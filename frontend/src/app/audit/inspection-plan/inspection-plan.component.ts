import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { InspectionPlanService } from '@app/services/audit/inspection-plan.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
//import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { AuditPlanService } from '@app/services/audit/audit-plan.service';
import {NgbTimepickerConfig} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-inspection-plan',
  templateUrl: './inspection-plan.component.html',
  styleUrls: ['./inspection-plan.component.scss'],
  providers: [NgbTimepickerConfig]
})
export class InspectionPlanComponent implements OnInit {

  //time = {hour: 13, minute: 30};
  form : FormGroup;
  loading = [];
  error:any;
  id:number;
  success:any;
  modalss:any;
  //audittype:Audittype;
  
  inspectionplanEntries:any=[];
  activityErrors='';
  inspectorErrors=''; 
  dateErrors='';
  start_timeErrors='';
  end_timeErrors='';
  persons_needErrors='';
  application_unit_idErrors='';
  
  formData:FormData = new FormData();
  startDate:any;
  inspdata:any;
  panelOpenState = false;
  constructor(config: NgbTimepickerConfig, private modalService: NgbModal,private activatedRoute:ActivatedRoute,private auditplanservice:AuditPlanService,private router: Router,private fb:FormBuilder,private InspectionPlanService: InspectionPlanService,private errorSummary: ErrorSummaryService) {
    //config.spinners = false;
   }
  audit_id:number;
  audit_plan_id:number;
  audit_unit_days:any;
  application_units:any=[];
  audit_type:any = '';

  //, Validators.pattern("^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$")
  ngOnInit() 
  {
    this.id= this.activatedRoute.snapshot.queryParams.auditplan_unit_id; 
    this.audit_id= this.activatedRoute.snapshot.queryParams.audit_id; 
    this.audit_plan_id=this.activatedRoute.snapshot.queryParams.audit_plan_id; 
    this.audit_unit_days = [];
    this.form = this.fb.group({
      activity:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]],
      inspector:['',[Validators.required]],
      date:['',[Validators.required]],
      start_time:['',[Validators.required]],
      end_time:['',[Validators.required]],
      person_need_to_be_present:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]],
      application_unit_id:['',[Validators.required]],
	    //time:[{hour: 11, minute: 30},[Validators.required]]
    });
    /*
    (control: FormControl) => {
        const value = control.value;
    
        if (!value) {
          return null;
        }
        
    
        return null;
      }*/
    this.loadDetails();

    this.auditplanservice.getApplicationUnit({audit_id:this.audit_id,audit_plan_id:this.audit_plan_id}).pipe(first())
      .subscribe(res => {
          this.application_units = res.app_units;  
          this.audit_type = res.audit_type;  
      },
      error => {
        this.error = error;
      });
	
  }
  showsendtocustomer:number = 0;
  showInspectionApproval:number = 0;
  loadDetails()
  {
    this.InspectionPlanService.getInspectionPlan({audit_id:this.audit_id}).pipe(first())
    .subscribe(res => {
      if(res){
        this.inspectionplanEntries = res.inspectionplans;      
        this.audit_unit_days = res.unitdates;
        this.startDate = this.audit_unit_days[0];
        this.showsendtocustomer = res.showsendtocustomer;
        this.showInspectionApproval = res.showInspectionApproval;
      }
    },
    error => {
        this.error = error;
    });
  }
  
  myFilter = (d: Date | null): boolean => {

    const date =
		d.getFullYear() +
		"-" +
		("00" + (d.getMonth() + 1)).slice(-2) +
		"-" +
    ("00" + d.getDate()).slice(-2);
    
    const day = (d || new Date()).getDay();
    if(this.audit_unit_days.findIndex(x=>x==date) >=0){
      return true;
    }
    return false;
    // Prevent Saturday and Sunday from being selected.
    //return day !== 0 && day !== 6;
  }

  get f() { return this.form.controls; }  
  
  inspectorList:any=[];
  getInspectors(unit_id)
  {
    if(unit_id)
    {
      this.loading['inspector']  = true;
      this.auditplanservice.getInspectors({audit_plan_id:this.audit_plan_id,unit_id:unit_id}).pipe(first())
      .subscribe(res => {
        this.inspectorList = res.data;
        this.loading['inspector']  = false;
      });
    }else{
      this.inspectorList = [];
    }
  }

  getSelectedValue(val)
  {
    if(this.inspectorList && this.inspectorList.length>0){
      let inspfind = this.inspectorList.find(x=> x.id==val);
      if(inspfind){
        return inspfind.name; 
      }
    }
    return '';
  }
  
  resetInspectionPlan()
  {
	 this.form.patchValue({
		activity : '',
		inspector: [],
		date: '',
		start_time: '',
		end_time: '',
		person_need_to_be_present: '',
		application_unit_id: '',
		application_unit_name: ''
    });
  
    this.inspectorList = [];
    this.inspdata = '';

    this.activityErrors = '';
    this.inspectorErrors = '';
    this.dateErrors = '';
    this.start_timeErrors = '';
    this.end_timeErrors = '';
    this.persons_needErrors = '';
    this.application_unit_idErrors='';

    this.form.reset();
  }
  
  
  addInspectionPlan()
  { 
    this.f.activity.markAsTouched();
    this.f.inspector.markAsTouched();
    this.f.date.markAsTouched();
    this.f.start_time.markAsTouched();
    this.f.end_time.markAsTouched();
    this.f.person_need_to_be_present.markAsTouched();
    this.f.application_unit_id.markAsTouched();

    let activity = this.form.get('activity').value;
    let inspector = this.form.get('inspector').value;
    
    let start_time = this.form.get('start_time').value;
    let end_time = this.form.get('end_time').value;
    let person_need_to_be_present = this.form.get('person_need_to_be_present').value;
    let application_unit_id = this.form.get('application_unit_id').value;
    
    
    if(this.form.valid)
    {
      let date = this.errorSummary.displayDateFormat(this.form.get('date').value);

      this.loading['button'] = true;
      let application_unit_name = this.application_units.find(s => s.id ==  application_unit_id);
       
      let expobject:any={audit_plan_unit_id:this.id,audit_id:this.audit_id,activity:activity,inspector:inspector,date:date,start_time:start_time,end_time:end_time,person_need_to_be_present:person_need_to_be_present,application_unit_id:application_unit_id,application_unit_name:application_unit_name.name}
              
      if(1)
      {
        if(this.inspdata)
        {
          expobject.id = this.inspdata.id;
        }

        this.InspectionPlanService.addData(expobject).pipe(first()).subscribe(res => {

            if(res.status)
            {
              this.success = {summary:res.message};
              this.resetInspectionPlan();
              this.loading['button'] = false;
              this.loadDetails();
              //setTimeout(()=>this.router.navigateByUrl('/audit/view-audit-plan?id='+this.audit_id),this.errorSummary.redirectTime);
            }
            else if(res.status == 0)
            {
              this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
            }else{
                  this.error = {summary:res};
            }
            this.loading['button'] = false;
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        }); 
      }
      else 
      { 
        this.error = {summary:this.errorSummary.errorSummaryText};	
        this.errorSummary.validateAllFormFields(this.form);       
      }
    }

  }
  
  editInspectionPlan(index:number,data)
  {
    this.activityErrors = '';
    this.inspectorErrors = '';
    this.dateErrors = '';
    this.start_timeErrors = '';
    this.end_timeErrors = '';  
    this.persons_needErrors = '';
    this.application_unit_idErrors = '';
    
    this.inspdata = data;
	  let qual = this.inspectionplanEntries[index];
    this.getInspectors(qual.application_unit_id);
    

    //console.log(qual.start_time);
    let start_timearr = qual.start_time.split(":");
    let start_time = {'hour':parseInt(start_timearr[0]),'minute':parseInt(start_timearr[1])};

    let end_timearr = qual.end_time.split(":");
    let end_time = {'hour':parseInt(end_timearr[0]),'minute':parseInt(end_timearr[1])};

    this.form.patchValue({
		activity: qual.activity,
	  inspector: qual.inspector.map(x=>parseInt(x)),
		date: this.errorSummary.editDateFormat(qual.date),
		start_time: start_time,
    end_time: end_time,
    person_need_to_be_present: qual.person_need_to_be_present,
		application_unit_id: qual.application_unit_id,
		application_unit_name: qual.application_unit_name			
    });
  }
  
  removeInspectionPlan(content,index:number,inspdata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    this.modalss.result.then((result) => {
      this.resetInspectionPlan();
      this.InspectionPlanService.deleteData({id:inspdata.id})
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.loadDetails();
            this.success = {summary:res.message};
            this.loading['button'] = false;
          }else if(res.status == 0){
            this.error = {summary:res};
          }
          this.loading['button'] = false;
      },
      error => {
          this.error = {summary:error};
      });
    }, (reason) => {
    })
  }
    

  changeStatus(data){
    //console.log(data);
    //return false;
    this.loading['button']  = true;
    this.auditplanservice.changeStatus(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //this.enquirydata.status = res.enquirystatus;
            //this.enquirydata.status_updated_date = res.status_updated_date;
            //this.enquirydata.status_updated_by = res.status_update_by;
            //this.auditPlanData.status = data.status;
            this.success = {summary:res.message};
            setTimeout(() => {
              //this.getAuditDetails();
              this.loading['button'] = false;
              this.success = '';
              this.router.navigateByUrl('/audit/view-audit-plan?id='+this.audit_id); 
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.loading['button'] = false;
            this.error = {summary:res};
          }else{
            this.loading['button'] = false;
            this.error = {summary:res};
          }
          
        
     },
     error => {
         this.error = {summary:error};
         this.loading['button'] = false;
     });
 }

 modals:any;
 open(content,arg='',data:any='',unitindex:any=0) 
 {
    //let status = this.auditPlanData.arrEnumStatus['review_in_process'];
    let user_type = '';
    let status;
    let unitstatus;
    //console.log(arg);
    if(arg=='sendtocustomer'){
      //status = this.auditPlanData.arrEnumStatus['awaiting_for_customer_approval'];
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['awaiting_for_customer_approval'];
    }
    //this.arg = arg;
    
    
    //console.log(arg);
    //, { centered: true }
    this.modals = this.modalService.open(content, {size:'lg',ariaLabelledBy: 'modal-basic-title',centered: true});
    
    this.modals.result.then((result) => {
        let inspectiontype:any = 'sendtocustomer';
        if(arg=='approveplanbyauditor'){
          inspectiontype = 'approveplanbyauditor';
        }else if(this.audit_type==2) {
          inspectiontype = 'followup_sendtocustomer';
        }
      this.changeStatus({typearg:arg,inspectiontype:inspectiontype,status,audit_id:this.audit_id,audit_plan_id:this.audit_plan_id});
      

    }, (reason) => {
      //this.comments_error ='';
      //this.arg = '';
      //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
    });
  }
}
