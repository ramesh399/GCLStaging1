import { Component, OnInit,EventEmitter,QueryList, ViewChildren, HostListener  } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';

import { InspectionBodyListService } from '@app/services/transfer-certificate/inspection-body/inspection-body-list.service';

import {InspectionBody} from '@app/models/transfer-certificate/inspection-body';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import {saveAs} from 'file-saver';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import {Observable} from 'rxjs';


@Component({
  selector: 'app-inspection-body',
  templateUrl: './inspection-body.component.html',
  styleUrls: ['./inspection-body.component.scss'],
  providers: [InspectionBodyListService]
})
export class InspectionBodyComponent implements OnInit {

  title = '';
  InspectionBody$: Observable<InspectionBody[]>;
  total$: Observable<number>;
  //source_file_status$: Observable<number>;
  //view_file_status$: Observable<number>;

  auditplanStatus$: Observable<any>;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  nameErrors:any = '';
  form : FormGroup;
  logForm: FormGroup;
  
  buttonDisable = false;
  error:any;
  id:number;
  typelist:any=[];
  statuslist:any=[];
  
  model: any = {id:null,action:null,type:'',description:'',date:''};
  success:any;
  modalss:any;

  formData:FormData = new FormData();
  userType:number;
  userdetails:any;
  userdecoded:any;

  gislogEntries:any=[];

  fileErr = '';
  viewErr = '';
  type:any;
  reviewerList:any;
  accessList:any;
  standardList:any;  
  
  downloadTypeArray = {'certification':'Certification Body','inspection':'Inspection Body'};
  downloadTypeActionArray = {'certification':'certification','inspection':'inspection'};
     
  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, public userService:UserService,public service: InspectionBodyListService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { 
  
    this.InspectionBody$ = service.inspectionbody$;
    this.total$ = service.total$;		
    //this.source_file_status$ = service.source_file_status$;   
    //this.view_file_status$ = service.view_file_status$;   
	
	/*
    router.events
      .filter(e => e instanceof NavigationEnd)
      .forEach(e => {
        this.title = activatedRoute.root.firstChild.snapshot.data['usertype'];
    });
	*/
	
	window.scroll({ 
      top: 0, 
      left: 0, 
      behavior: 'smooth' 
    });
  }
  canAddData = false;
  canEditData = false;
  canDeleteData = false;
  canViewData = false;
  ngOnInit() {
  			
    this.type = this.activatedRoute.snapshot.data['pageType'];	
      
    this.title = this.downloadTypeArray[this.type];	
          
    this.form = this.fb.group({		
      name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],		
      description:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
    });	   
    
    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;

        if(this.userdetails.resource_access != 1){
          if(this.type == 'certification'){
            if(this.userdetails.rules.includes('edit_certification_body')  ){
              this.canEditData = true;
            }
            if(this.userdetails.rules.includes('delete_certification_body') ){
              this.canDeleteData = true;
            }
            if(this.userdetails.rules.includes('add_certification_body')  ){
              this.canAddData = true;
            }
          }else if(this.type == 'inspection'){
            if(this.userdetails.rules.includes('edit_inspection_body') ){
              this.canEditData = true;
            }
            if(this.userdetails.rules.includes('delete_inspection_body') ){
              this.canDeleteData = true;
            }
            if(this.userdetails.rules.includes('add_inspection_body') ){
              this.canAddData = true;
            }
          }
          
        }
        if(this.userdetails.resource_access == 1){
          this.canAddData = true;
          this.canEditData = true;
          this.canDeleteData = true;	
        }
        
        		
      }else{
        this.userdecoded=null;
      }
    });

  }

  get f() { return this.form.controls; } 
  get sf() { return this.logForm.controls; }  

  onSort({column, direction}: SortEvent) 
  {
    this.headers.forEach(header => {
      if (header.sortable !== column) {
        header.direction = '';
      }
    });

    this.service.sortColumn = column;
    this.service.sortDirection = direction;
  }

  files:any=[];
  fileChange(element) {
    let files = element.target.files;
    this.fileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      let fileindex = this.files.length;
      this.formData.append("document["+fileindex+"]", files[0], files[0].name);
      this.files.push({id:'',deleted:0,added:1,name:files[0].name});
      
    }else{
      this.fileErr ='Please upload valid source file';
    }
    element.target.value = '';
   
  }
  
  viewfiles:any=[];
  viewFileChange(element) {
    let viewfiles = element.target.files;
    this.viewErr ='';
    let fileextension = viewfiles[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      let fileindex = this.viewfiles.length;
      this.formData.append("viewdocument["+fileindex+"]", viewfiles[0], viewfiles[0].name);
      this.viewfiles.push({id:'',deleted:0,added:1,name:viewfiles[0].name});
      
    }else{
      this.viewErr ='Please upload valid view file';
    }
    element.target.value = '';
   
  }
  
  get filterFile(){
    return this.files.filter(x=>x.deleted==0);
  }
  removeFile(fileindex)
  {
  	this.files[fileindex].deleted =1;
  }
  
  removeViewFile(fileindex)
  {
  	this.viewfiles[fileindex].deleted =1;
  }
  
  getSelectedValue(val)
  {
    
    return this.accessList.find(x=> x.id==val).name;
    
  }
  
  getSelectedStandardValue(val)
  {
    
    return this.standardList.find(x=> x.id==val).name;
    
  }
  
  newVersionStatus=0;
  addNewVersion()
  {
	  this.newVersionStatus=1;
	  this.addData();
  }
  
  
  
  gisListEntries = [];
  gisIndex:number=null;
  loading:any=[];
  addData()
  {
	this.f.name.markAsTouched();
    this.f.description.markAsTouched();
    	
    if(this.form.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true;     
     
      let name = this.form.get('name').value;
      let description = this.form.get('description').value;
      
      let expobject:any={};

      expobject = {name:name,description:description,type:this.type};
       
            
      
	    if(this.curData){
	      expobject.id = this.curData.id;
	      //expobject['gis_file'] = this.curData.gis_file;
	    }
	    
	    this.formData.append('formvalues',JSON.stringify(expobject));

	    this.service.addData(this.formData)
	    .pipe(first())
	    .subscribe(res => {

	    
	        if(res.status){
				this.formData = new FormData(); 
				this.service.customSearch();
				this.formReset();
				this.success = {summary:res.message};
				this.buttonDisable = false;          
	        }else if(res.status == 0){	
          
				  this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
	        }else{			      
				this.error = {summary:res};
			}
	        this.loading['button'] = false;
	        this.buttonDisable = false;
	    },
	    error => {
	        this.error = {summary:error};
	        this.loading['button'] = false;
	    });
        
     




         
    }
  }
  

  formReset()
  {
	this.fileErr ='';
	this.viewErr ='';
  	this.formData = new FormData(); 
    this.curData = '';
    this.files = [];
	  this.viewfiles = [];
	this.editStatus=0;
	this.newVersionStatus=0;
    this.form.reset();
  }

  downloadData:any;
  showDetails(content,data)
  {
    this.downloadData = data;
    //console.log(data);
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }
  
  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  
  removeData(content,index:number,data) {

      this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

      this.modalss.result.then((result) => {

          
          this.formReset();
          
          this.service.deleteData({id:data.id})
          .pipe(first())
          .subscribe(res => {

              if(res.status){
                this.service.customSearch();
                this.success = {summary:res.message};
                this.buttonDisable = true;
              }else if(res.status == 0){
                this.error = {summary:res};
              }
              this.loading['button'] = false;
              this.buttonDisable = false;
          },
          error => {
              this.error = {summary:error};
              this.loading['button'] = false;
          });
      }, (reason) => {
      })
      
    
  }

  editStatus=0;
  curData:any;
  editData(index:number,data) {
    this.formData = new FormData(); 
    this.curData = data;
	this.editStatus = 1;
	/*
    this.files = [];
	this.viewfiles = [];
	
    if(data.documents && data.documents.length>0){
    	data.documents.forEach((val)=>{
    		this.files.push({id:val.id,deleted:0,added:0,name:val.name});
    	})
    }
	
	if(data.viewdocuments && data.viewdocuments.length>0){
    	data.viewdocuments.forEach((val)=>{
    		this.viewfiles.push({id:val.id,deleted:0,added:0,name:val.name});
    	})
    }
	*/
	
	this.form.patchValue({
		name:data.name,		
		description:data.description		
	});
	
	this.scrollToBottom();	
  }
  
  scrollToBottom()
  {
	window.scroll({ 
      top: window.innerHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }


}


