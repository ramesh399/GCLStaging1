import { Component, OnInit,EventEmitter,QueryList, ViewChildren, HostListener  } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';

import { AuditCategoryListService } from '@app/services/master/audit-category/audit-category.service';

import {AuditCategory} from '@app/models/master/audit-category.ts';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import {saveAs} from 'file-saver';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {Observable} from 'rxjs';


@Component({
  selector: 'app-audit-report-category',
  templateUrl: './audit-report-category.component.html',
  styleUrls: ['./audit-report-category.component.scss'],
  providers: [AuditCategoryListService]
})
export class AuditReportCategoryComponent implements OnInit {

  title = '';
  categorys$: Observable<AuditCategory[]>;
  total$: Observable<number>;
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
  alertInfoMessage:any;
  closeResult: string;
  modalOptions:NgbModalOptions;
  alertSuccessMessage:any;
  alertErrorMessage:any;
  cancelBtn=true;
  okBtn=true;

  formData:FormData = new FormData();
  userType:number;
  userdetails:any;
  userdecoded:any;
  type:any;

  TypeArray = {'interview_requirement':'Audit Report Interview Requirement','client_information':'Audit Report Client Information','living_requirement':'Audit Report Living wage Requirement','living_category':'Audit Report Living wage Category'};
  TypeActionArray = {'interview_requirement':'interview_requirement','client_information':'client_information','living_requirement':'living_requirement','living_category':'living_category'};

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, public userService:UserService,public service: AuditCategoryListService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { 
  
    this.categorys$ = service.categorys$;
    this.total$ = service.total$;		
   
	
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
  canActivateData = false;
  canDeactivateData = false;

  ngOnInit() {

    this.type = this.activatedRoute.snapshot.data['pageType'];	
		
    this.title = this.TypeArray[this.type];	
    
    this.form = this.fb.group({		
      name:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
    });	   

    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;

        if(this.userdetails.resource_access != 1){
          if(this.type == 'living_requirement'){
            if(this.userdetails.rules.includes('edit_audit_report_living_wage_requirement')  ){
              this.canEditData = true;
            }
            if(this.userdetails.rules.includes('delete_audit_report_living_wage_requirement') ){
              this.canDeleteData = true;
            }
            if(this.userdetails.rules.includes('add_audit_report_living_wage_requirement')  ){
              this.canAddData = true;
            }
            if(this.userdetails.rules.includes('activate_audit_report_living_wage_requirement')  ){
              this.canActivateData = true;
            }
            if(this.userdetails.rules.includes('deactivate_audit_report_living_wage_requirement')  ){
              this.canDeactivateData = true;
            }
          }else if(this.type == 'living_category'){
            if(this.userdetails.rules.includes('edit_audit_report_living_wage_category') ){
              this.canEditData = true;
            }
            if(this.userdetails.rules.includes('delete_audit_report_living_wage_category') ){
              this.canDeleteData = true;
            }
            if(this.userdetails.rules.includes('add_audit_report_living_wage_category') ){
              this.canAddData = true;
            }
            if(this.userdetails.rules.includes('activate_audit_report_living_wage_category')  ){
              this.canActivateData = true;
            }
            if(this.userdetails.rules.includes('deactivate_audit_report_living_wage_category')  ){
              this.canDeactivateData = true;
            }
          }else if(this.type == 'interview_requirement'){
            if(this.userdetails.rules.includes('edit_audit_report_interview_requirement') ){
              this.canEditData = true;
            }
            if(this.userdetails.rules.includes('delete_audit_report_interview_requirement') ){
              this.canDeleteData = true;
            }
            if(this.userdetails.rules.includes('add_audit_report_interview_requirement') ){
              this.canAddData = true;
            }
            if(this.userdetails.rules.includes('activate_audit_report_interview_requirement')  ){
              this.canActivateData = true;
            }
            if(this.userdetails.rules.includes('deactivate_audit_report_interview_requirement')  ){
              this.canDeactivateData = true;
            }
          }else if(this.type == 'client_information'){
            if(this.userdetails.rules.includes('edit_audit_report_client_information') ){
              this.canEditData = true;
            }
            if(this.userdetails.rules.includes('delete_audit_report_client_information') ){
              this.canDeleteData = true;
            }
            if(this.userdetails.rules.includes('add_audit_report_client_information') ){
              this.canAddData = true;
            }
            if(this.userdetails.rules.includes('activate_audit_report_client_information')  ){
              this.canActivateData = true;
            }
            if(this.userdetails.rules.includes('deactivate_audit_report_client_information')  ){
              this.canDeactivateData = true;
            }
          }
          
        }
        if(this.userdetails.resource_access == 1){
          this.canAddData = true;
          this.canEditData = true;
          this.canDeleteData = true;	
          this.canActivateData = true;
          this.canDeactivateData = true;
        }
        	
      }else{
        this.userdecoded=null;
      }
    });
  }

  get f() { return this.form.controls; }
  loading:any=[];
  addData()
  {
    this.f.name.markAsTouched();

    if(this.form.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true;     
     
      let name = this.form.get('name').value;

      let expobject:any={};

      expobject = {name:name,type:this.type};

      if(this.curData){
	      expobject.id = this.curData.id;
      }
      
      this.service.addData(expobject)
	    .pipe(first())
	    .subscribe(res => {

	    
	      if(res.status){
				
				this.service.customSearch();				
				this.success = {summary:res.message};
				
				setTimeout(() => {					
					this.success = {summary:''};
					this.formReset();
					this.buttonDisable = false;	          					
				}, this.errorSummary.redirectTime);				
				
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

  this.curData = data;
  this.editStatus = 1;


this.form.patchValue({
  name:data.name
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

  formReset()
  {
    this.form.patchValue({
      name:''		
    });
    this.form.reset();
  }


  open(content,action,id) {
    this.model.id = id;	
    this.model.action = action;	
    this.resetBtn();
    if(action=='activate'){		
       this.alertInfoMessage='Are you sure, do you want to activate?';
    }else if(action=='deactivate'){		
       this.alertInfoMessage='Are you sure, do you want to deactivate?';
    }else if(action=='delete'){		
       this.alertInfoMessage='Are you sure, do you want to delete?';	
    }
    
    this.modalss = this.modalService.open(content, this.modalOptions);
      //this.modalService.open(content, this.modalOptions).result.then((result) => {
    
    this.modalss.result.then((result) => {	
        this.closeResult = `Closed with: ${result}`;	 
      }, (reason) => {
      this.model.id = null;  
      this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;	  
      });
    }
    
    private getDismissReason(reason: any): string {
      if (reason === ModalDismissReasons.ESC) {
        return 'by pressing ESC';
      } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
        return 'by clicking on a backdrop';
      } else {
        return  `with: ${reason}`;
      }
    }
    
    commonModalAction(){
    let reason = this.model.action;	
    
    let actionStatus=0;
    if(reason=='activate'){		
      actionStatus=0;
    }else if(reason=='deactivate'){		
      actionStatus=1;
    }else if(reason=='delete'){		
      actionStatus=2;	
    }else{
      this.modalss.close('deactivate')
    }	
    this.commonUpdateData(actionStatus);
    }  
   
    commonUpdateData(actionStatus) {
      
    this.alertInfoMessage='Please wait. Your request is processing';
    this.service.commonActionData({id:this.model.id,status:actionStatus,type:this.type}).pipe(first())
      .subscribe(res => {
      this.model.id = null;
      this.model.action = null;
      this.cancelBtn=false;
      this.okBtn=false;
      
      if(res.status){
        this.alertInfoMessage='';
              this.alertSuccessMessage = res.message;
        setTimeout(()=>this.modalss.close('deactivate'),this.errorSummary.redirectTime);
        this.service.searchTerm=this.service.searchTerm;
          }else if(res.status == 0){			
        this.alertInfoMessage='';
              this.alertErrorMessage = res.message;	
        }else{
        this.alertInfoMessage='';
              this.alertErrorMessage = res.message;
      }				
      },
      error => {
      this.alertInfoMessage='';
          this.alertErrorMessage = error;
         
      });
    }  
    
    resetBtn()
    {
    this.alertInfoMessage='';
    this.alertSuccessMessage='';
    this.alertErrorMessage='';
    this.cancelBtn=true;
    this.okBtn=true;
    }

}
