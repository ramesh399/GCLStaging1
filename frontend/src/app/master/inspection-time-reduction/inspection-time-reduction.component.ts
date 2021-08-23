import { Component, OnInit,EventEmitter,QueryList, ViewChildren, HostListener  } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';

import { InspectiontimereductionService } from '@app/services/master/inspectiontime/inspectiontimereduction.service';

import {Inspectiontimereduction} from '@app/models/master/inspectiontimereduction';

import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import {saveAs} from 'file-saver';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import {Observable} from 'rxjs';

import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';


@Component({
  selector: 'app-inspection-time-reduction',
  templateUrl: './inspection-time-reduction.component.html',
  styleUrls: ['./inspection-time-reduction.component.scss'],
  providers: [InspectiontimereductionService]
})
export class InspectionTimeReductionComponent implements OnInit {

  title = '';
  Inspectiontimereduction$: Observable<Inspectiontimereduction[]>;
  total$: Observable<number>;
  //source_file_status$: Observable<number>;
  //view_file_status$: Observable<number>;

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
  
  model: any = {id:null,action:null,type:'',standards:'',date:''};
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
     
  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, public userService:UserService,public service: InspectiontimereductionService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService,private standardservice: StandardService) { 
  
    this.Inspectiontimereduction$ = service.inspectiontimereduction$;
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
	
	//console.log(this.Inspectiontimereduction.length);
	
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
  			
	this.title = 'Inspection Time Reduction Percentage';	
	
	this.standardservice.getStandard().subscribe(res => {
		  this.standardList = res['standards'];   
    });
				
	this.form = this.fb.group({
		reduction_percentage:['',[Validators.required]],		
		standards:['',[Validators.required]]
	});	   
    
    this.authservice.currentUser.subscribe(x => {
		if(x){
			let user = this.authservice.getDecodeToken();
			this.userType= user.decodedToken.user_type;
			this.userdetails= user.decodedToken;
			this.canAddData = true;
			this.canEditData = true;
			this.canDeleteData = true;			
		}else{
			this.userdecoded=null;
		}
	});

  }

  get f() { return this.form.controls; } 
  get sf() { return this.logForm.controls; }    
  
  getSelectedValue(val)
  {
    
    return this.accessList.find(x=> x.id==val).name;
    
  }
  
  getSelectedStandardValue(val)
  {
    
    return this.standardList.find(x=> x.id==val).code;
    
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
	this.f.reduction_percentage.markAsTouched();
    this.f.standards.markAsTouched();
    	
    if(this.form.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true;     
     
      let reduction_percentage = this.form.get('reduction_percentage').value;
      let standards = this.form.get('standards').value;
      
      let expobject:any={};

      expobject = {reduction_percentage:reduction_percentage,standards:standards};
       
            
      
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
    		this.files.push({id:val.id,deleted:0,added:0,reduction_percentage:val.reduction_percentage});
    	})
    }
	
	if(data.viewdocuments && data.viewdocuments.length>0){
    	data.viewdocuments.forEach((val)=>{
    		this.viewfiles.push({id:val.id,deleted:0,added:0,reduction_percentage:val.reduction_percentage});
    	})
    }
	*/
	
	this.form.patchValue({
		reduction_percentage:data.reduction_percentage,		
		standards:data.standards		
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



