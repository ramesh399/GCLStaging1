import { Component, OnInit,EventEmitter,QueryList, ViewChildren, HostListener  } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';

import { StandardLabelGradeListService } from '@app/services/transfer-certificate/standard-label-grade/standard-label-grade-list.service';
import { StandardListService } from '@app/services/transfer-certificate/standard/standard-list.service';

import {StandardLabelGrade} from '@app/models/transfer-certificate/standard-label-grade';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';
import {saveAs} from 'file-saver';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import {Observable} from 'rxjs';


@Component({
 selector: 'app-raw-material-standard-label-grade',
  templateUrl: './raw-material-standard-label-grade.component.html',
  styleUrls: ['./raw-material-standard-label-grade.component.scss'],
  providers: [StandardLabelGradeListService]
})
export class RawMaterialStandardLabelGradeComponent implements OnInit {

  title = '';
  StandardLabelGrade$: Observable<StandardLabelGrade[]>;
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
  standardList:Standard[];
  
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
    
  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, public userService:UserService,public service: StandardLabelGradeListService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService,public standardListService:StandardListService) { 
  
    this.StandardLabelGrade$ = service.standardLabelGrade$;
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
	this.title = 'Standard Label Grade';

	this.standardListService.getStandardList().subscribe(res => {
		this.standardList = res['standards'];
    });	
			
	this.form = this.fb.group({	
        standard_id:['',[Validators.required]],	
		name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255), Validators.pattern("^[a-zA-Z0-9 \'\-().,]+$")]]
	});	   
    
    this.authservice.currentUser.subscribe(x => {
		if(x){
			let user = this.authservice.getDecodeToken();
			this.userType= user.decodedToken.user_type;
      this.userdetails= user.decodedToken;
      
      if(this.userdetails.resource_access == 1){
        this.canAddData = true;
        this.canEditData = true;
        this.canDeleteData = true;
      }else{
        
        if(this.userdetails.rules.includes('edit_tc_standard_label_grade')  ){
          this.canEditData = true;
        }
        if(this.userdetails.rules.includes('delete_tc_standard_label_grade') ){
          this.canDeleteData = true;
        }
        if(this.userdetails.rules.includes('add_tc_standard_label_grade')  ){
          this.canAddData = true;
        }
        
      }
      /*
			this.canAddData = true;
			this.canEditData = true;
      this.canDeleteData = true;
      */
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
  
  getSelectedValue(val)
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
	  
	this.f.standard_id.markAsTouched(); 
	this.f.name.markAsTouched();
       
    if(this.form.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true;     
     
	  let standard_id = this.form.get('standard_id').value;
      let name = this.form.get('name').value;
      
      let expobject:any={};

      expobject = {standard_id:standard_id,name:name};
       
            
      
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
		
	this.form.patchValue({
		name:data.name,
		standard_id:data.standard_id
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



