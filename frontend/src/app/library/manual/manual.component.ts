import { Component, OnInit,EventEmitter,QueryList, ViewChildren, HostListener  } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';

import { ManualListService } from '@app/services/library/manual-list.service';
import { CommonService } from '@app/services/library/common.service';

import { UserRoleService } from '@app/services/master/userrole/userrole.service';
import { UserRole } from '@app/models/master/userrole';

import {Manual} from '@app/models/library/manual';
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
  selector: 'app-manual',
  templateUrl: './manual.component.html',
  styleUrls: ['./manual.component.scss'],
  providers: [ManualListService]
})
export class ManualComponent implements OnInit {

  title = '';
  Manual$: Observable<Manual[]>;
  total$: Observable<number>;
  source_file_status$: Observable<number>;
  view_file_status$: Observable<number>;

  auditplanStatus$: Observable<any>;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;


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
  roleList:UserRole[]=[];
  
  downloadTypeArray = {'handbooks':'Handbooks','training_mat':'Training Mat','artwork':'Artwork','client_logos':'Client Logos','manual':'Manual','procedures':'Procedures','competence_criteria':'Competence Criteria','instructions':'Instructions','templates':'Templates','application_forms':'Application Forms','polices':'Policies','standards':'Standards','webinars':'Webinar/Training'};
  downloadTypeActionArray = {'handbooks':'handbook','training_mat':'training_mat','artwork':'artwork','client_logos':'client_logo','manual':'manual','procedures':'procedure','competence_criteria':'competence_criteria','instructions':'instruction','templates':'template','application_forms':'application_form','polices':'policy','standards':'standards','webinars':'webinar'};
    
  constructor(private userRoleService:UserRoleService,private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, public userService:UserService,public service: ManualListService,public commonservice: CommonService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { 
  
    this.Manual$ = service.manual$;
    this.total$ = service.total$;		
    this.source_file_status$ = service.source_file_status$;   
    this.view_file_status$ = service.view_file_status$;   
	
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
  	//this.type = this.activatedRoute.snapshot.queryParams.type;
	this.type = this.activatedRoute.snapshot.data['pageType'];
		

	this.title = this.downloadTypeArray[this.type];	
	//this.title = this.activatedRoute.snapshot.data['pageType'];
	
	//this.type = this.title;
	if(this.type=='webinars')
	{
		this.form = this.fb.group({
			document_date:['',[Validators.required]],
			reviewer:['',[Validators.required]],	 	  
			access:['',[Validators.required]],	
			description:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
			status:['',[Validators.required]],
			file:[''],
			viewfile:['']
		});		
		
	}else if(this.type=='standards'){
		
		this.form = this.fb.group({
			title:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
			standards:['',[Validators.required]],
			document_date:['',[Validators.required]],
			reviewer:['',[Validators.required]],	 	  
			access:['',[Validators.required]],	
			description:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
			status:['',[Validators.required]],
			file:[''],
			viewfile:['']
		});		
		
	}else{
	
		this.form = this.fb.group({
			title:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
			version:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(10)]],
			document_date:['',[Validators.required]],
			reviewer:['',[Validators.required]],	 	  
			access:['',[Validators.required]],	
			description:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
			status:['',[Validators.required]],
			file:[''],
			viewfile:['']
		});		
	}    
    
    this.service.getData().pipe(first())
    .subscribe(res => {
      this.reviewerList  = res.reviewerList;
      this.statuslist  = res.status;
      this.accessList = res.useraccess;
	    this.standardList = res.standard;
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });
	
	this.userRoleService.getAllRoles().subscribe(res => {
      this.roleList = res['userroles'];
    });
    
    this.authservice.currentUser.subscribe(x => {
		if(x){
			let user = this.authservice.getDecodeToken();
			this.userType= user.decodedToken.user_type;
			this.userdetails= user.decodedToken;
			if(this.userType==1 && this.userdetails.resource_access==1){
			  this.canAddData = true;
			  this.canEditData = true;
			  this.canDeleteData = true;
			}else if(this.userType==1){

			  //if(this.type=='handbooks'){
				 
				if(this.userdetails.rules.includes('add_'+this.downloadTypeActionArray[this.type]))
				{
				  this.canAddData = true;
				}
				
				if(this.userdetails.rules.includes('edit_'+this.downloadTypeActionArray[this.type]))
				{
				  this.canEditData = true;
				}
				
				if(this.userdetails.rules.includes('view_'+this.downloadTypeActionArray[this.type]))
				{
				  this.canViewData = true;
				}			
				
				if(this.userdetails.rules.includes('delete_'+this.downloadTypeActionArray[this.type]))
				{
				  this.canDeleteData = true;
				}
				
			  //}	
			  
				/*		  
			  }else if(this.type=='training_mat'){
				
				if(this.userdetails.rules.includes('add_training_mat')){
				  this.canAddData = true;
				}
				if(this.userdetails.rules.includes('edit_training_mat')){
				  this.canEditData = true;
				}
				if(this.userdetails.rules.includes('delete_training_mat')){
				  this.canDeleteData = true;
				}
				 
			  }
			  */
			}
		}else{
			this.userdecoded=null;
		}
	});

  }

  get f() { return this.form.controls; } 
  get sf() { return this.logForm.controls; }  

  

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
 	this.fileErr = '';
	this.viewErr = '';
	if(this.type!='webinars')
	{
		this.f.title.markAsTouched();
	}
	
	if(this.type=='standards')
	{
		this.f.standards.markAsTouched();
	}else if(this.type!='webinars'){
		this.f.version.markAsTouched();
	}
    this.f.document_date.markAsTouched();
    this.f.reviewer.markAsTouched();
    this.f.access.markAsTouched();
    this.f.description.markAsTouched();
    this.f.status.markAsTouched();
	let fileValidationStatus=false;
    let addfiles = this.files.find(x=>x.deleted==0)
    if(!addfiles || addfiles.length<=0){
    	this.fileErr = 'Please upload source file';
    	fileValidationStatus=true;
    }
	
	let addviewfiles = this.viewfiles.find(x=>x.deleted==0)	
	if(!addviewfiles || addviewfiles.length<=0){
		this.viewErr = 'Please upload view file';
    	fileValidationStatus=true;
    }
	
	if(fileValidationStatus==true)
	{
		return false;
	}
    
    if(this.form.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true;

      let title = '';
	  if(this.type!='webinars')
	  {
		title = this.form.get('title').value;  
	  }
	  
      //let version = this.form.get('version').value;
	  let version = '';
	  let standards = '';
	  if(this.type=='standards')
	  {
		standards = this.form.get('standards').value;
	  }else if(this.type!='webinars'){
		version = this.form.get('version').value;
	  }
      let document_date = this.errorSummary.displayDateFormat(this.form.get('document_date').value);
      let reviewer = this.form.get('reviewer').value;
      let access = this.form.get('access').value;
      let description = this.form.get('description').value;
      let status = this.form.get('status').value;

      let expobject:any={};

      expobject = {title:title,version:version,standards:standards,document_date:document_date,reviewer:reviewer,manual_access:access,description:description,status:status,documents:this.files,viewdocuments:this.viewfiles,type:this.type,newVersionStatus:this.newVersionStatus};
       
      
      
      
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
	          
	          /*
	          setTimeout(() => {
	            
	          },this.errorSummary.redirectTime);
	          */
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
	
	if(this.type=='webinars'){
		
		this.form.patchValue({			
			reviewer:'',	 	  
			access:'',				
			status:''
		});
		
	}else if(this.type=='standards'){
		
		this.form.patchValue({			
			standards:'',
			reviewer:'',	 	  
			access:'',				
			status:''
		});
		
	}else{
		this.form.patchValue({
			reviewer:'',	 	  
			access:'',				
			status:''
		});
	}
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
  downloadFile(fileid,filename,downloadtype){
    this.service.downloadFile({id:fileid,type:this.type,downloadtype:downloadtype})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
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
	this.fileErr ='';
	this.viewErr ='';  
    this.formData = new FormData(); 
    this.curData = data;
	this.editStatus = 1;
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
	
	if(this.type=='webinars'){
		
		this.form.patchValue({
			document_date:this.errorSummary.editDateFormat(data.document_date),
			reviewer:data.reviewer,	 	  
			access:data.access,	
			description:data.description,
			status:data.status
		});
		
	}else if(this.type=='standards'){
		
		this.form.patchValue({
			title:data.title,
			standards:data.standard,
			document_date:this.errorSummary.editDateFormat(data.document_date),
			reviewer:data.reviewer,	 	  
			access:data.access,	
			description:data.description,
			status:data.status
		});
		
	}else{
		this.form.patchValue({
			title:data.title,
			version:data.version,
			document_date:this.errorSummary.editDateFormat(data.document_date),
			reviewer:data.reviewer,	 	  
			access:data.access,	
			description:data.description,
			status:data.status
		});
	}
	this.scrollToBottom();	
  }
  
   getSelectedRoleValue(val)
   {
		return this.roleList.find(x=> x.id==val).role_name;    
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
