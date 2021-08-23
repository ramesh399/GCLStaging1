import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import {AuditInterviewSamplingPlanService} from '@app/services/master/audit-interview-sampling-plan/audit-interview-sampling-plan.service';
import { User } from '@app/models/master/user';
import { tap,first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import { AuditInterviewSamplingPlan } from '@app/models/master/audit-interview-sampling-plan';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';

@Component({
  selector: 'app-audit-interview-social-criteria',
  templateUrl: './audit-interview-social-criteria.component.html',
  styleUrls: ['./audit-interview-social-criteria.component.scss'],
  providers: [AuditInterviewSamplingPlanService]
})
export class AuditInterviewSocialCriteriaComponent implements OnInit {

  title = 'Audit Interview Sampling Plan'; 
  form : FormGroup; 
  plans$: Observable<AuditInterviewSamplingPlan[]>;
  total$: Observable<number>;
  id:number;
  planData:any;
  FaqData:any;
  error:any;
  success:any;
  buttonDisable = false;
  audit_man_daysErrors = '';
  model: any = {user_access_id:null};
  accessList:any=[];
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  planEntries:any=[];
  modalss:any;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  canAddData = false;
  canEditData = false;
  canDeleteData = false; 
  canViewData = false;

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private userservice: UserService, private router: Router,private fb:FormBuilder, public userService:UserService,public service: AuditInterviewSamplingPlanService, private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) {
    this.plans$ = service.plans$;
    this.total$ = service.total$;
  }

  ngOnInit() {
    this.form = this.fb.group({	
      // audit_man_days:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],  
      no_of_employess_from:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
      no_of_employess_to:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
 
      total_employees_interviewed:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(11)]],  
      records_checked_per_month:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(11)]],
      time_spent_on_interviews:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]]
    });

    
    
    this.authservice.currentUser.subscribe(x => {
      if(x)
      {
				let user = this.authservice.getDecodeToken();
				this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        
        if(this.userdetails.resource_access != 1)
        {
          if(this.userdetails.rules.includes('edit_audit_interview_sampling_plan')  ){
            this.canEditData = true;
          }
          if(this.userdetails.rules.includes('delete_audit_interview_sampling_plan') ){
            this.canDeleteData = true;
          }
          if(this.userdetails.rules.includes('add_audit_interview_sampling_plan')  ){
            this.canAddData = true;
          }
        }
        if(this.userdetails.resource_access == 1)
        {
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

  planListEntries = [];
  planIndex:number=null;
  loading:any=[];
  addplan()
  {
    // this.f.audit_man_days.markAsTouched();
    this.f.no_of_employess_from.markAsTouched();
    this.f.no_of_employess_to.markAsTouched();
    this.f.total_employees_interviewed.markAsTouched();
    this.f.records_checked_per_month.markAsTouched();
    this.f.time_spent_on_interviews.markAsTouched();

    if(this.form.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] =true;
      // let audit_man_days = this.form.get('audit_man_days').value;
      console.log(this.form.value)
      let no_of_employees_from = this.form.get('no_of_employess_from').value;
      let no_of_employees_to = this.form.get('no_of_employess_to').value;
      let total_employees_interviewed = this.form.get('total_employees_interviewed').value;
      let records_checked_per_month = this.form.get('records_checked_per_month').value;
      let time_spent_on_interviews = this.form.get('time_spent_on_interviews').value;

      // let expobject:any={audit_man_days:audit_man_days,total_employees_interviewed:total_employees_interviewed,records_checked_per_month:records_checked_per_month,time_spent_on_interviews:time_spent_on_interviews};
      let expobject:any={no_of_employees_from:no_of_employees_from,no_of_employees_to:no_of_employees_to, total_employees_interviewed:total_employees_interviewed,records_checked_per_month:records_checked_per_month,time_spent_on_interviews:time_spent_on_interviews};

      
      if(1)
      {

        if(this.planData){
          expobject.id = this.planData.id;
        }
        
       // this.formData.append('formvalues',JSON.stringify(expobject));
        this.service.addData(expobject)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              this.planData = '';
              //this.formData = new FormData(); 
              this.service.customSearch();
              this.planFormreset();
              this.success = {summary:res.message};
              this.buttonDisable = false;
              
              /*
              setTimeout(() => {
                
              },this.errorSummary.redirectTime);
              */
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
            this.buttonDisable = false;
        });
        
      } 
    }
    else {
        
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form); 
      
    }   
  }

  editStatus=0;
  editPlan(index:number,plandata) {
   // this.formData = new FormData(); 
    this.editStatus=1;
    this.planData = plandata;
    
    this.form.patchValue({
      // audit_man_days:plandata.audit_man_days,
      no_of_employess_from:plandata.no_of_employees_from,
      no_of_employess_to:plandata.no_of_employees_to,
      total_employees_interviewed:plandata.total_employees_interviewed,     
      records_checked_per_month:plandata.records_checked_per_month,
      time_spent_on_interviews:plandata.time_spent_on_interviews
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

 

  removePlan(content,index:number,plandata) {

    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.planFormreset();
        this.service.deleteData({id:plandata.id})
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

  planFormreset()
  {
	this.editStatus=0;  
    this.form.reset();
  }

  onSubmit(){ }

}
