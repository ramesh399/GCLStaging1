import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl, Form } from '@angular/forms';
import { first, debounceTime, distinctUntilChanged, map,tap } from 'rxjs/operators';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuditPlan } from '@app/models/audit/audit-plan';
import { AuditPlanService } from '@app/services/certification/audit-plan.service';
import { AuditExecutionService } from '@app/services/audit/audit-execution.service';
import { InspectionPlanService } from '@app/services/audit/inspection-plan.service';
import { AuthenticationService } from '@app/services';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {saveAs} from 'file-saver';

@Component({
  selector: 'app-view-certificate',
  templateUrl: './view-certificate.component.html',
  styleUrls: ['./view-certificate.component.scss']
})
export class ViewCertificateComponent implements OnInit {

  constructor(public errorSummary:ErrorSummaryService, private fb:FormBuilder, private router:Router,private auditplanservice:AuditPlanService,private auditexecutionservice:AuditExecutionService,private inspectionplanservice:InspectionPlanService,private activatedRoute:ActivatedRoute,private authservice:AuthenticationService, private modalService: NgbModal) { }
  id:number;
  certificate_id:number;
  product_addition_id:number;
  certificate_status:number;
  auditPlanData:AuditPlan;
  error:any;
  success:any;
  loading=false;
  unitsuccess:any;
  uniterror:any;

  userType:number;
  userdetails:any;
  userdecoded:any;
  model: any = {comments:'',subtopic:''};
  panelOpenState=false;
  audit_plan_id:any;
  draftcertificatedata:any;
  certificatestatus:any=[];
  successUnitIndex:any;
  form : FormGroup;
  minDate = new Date();
  
  ngOnInit() {
    
    this.id = this.activatedRoute.snapshot.queryParams.id;
	  this.certificate_id = this.activatedRoute.snapshot.queryParams.certificate_id;
    this.authservice.currentUser.subscribe(x => {
      if(x){
        
         
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        //console.log(user.decodedToken);
        
      }else{
        this.userdecoded=null;
      }
    });
	
	  this.form = this.fb.group({
      id:[''],
      status:['',[Validators.required]],
      comment:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
      extension_date:[''],      
    });

    this.loading = true;
    this.auditplanservice.getAuditPlanDetails({id:this.id,certificate_id:this.certificate_id}).
    pipe(
    tap(res=>{
        this.certificate_status = res.certificate_status;
        this.auditplanservice.getStatusList({status:this.certificate_status})
        .pipe(first())
        .subscribe(res => {
          this.certificatestatus = res.data;
        },
        error => {
            this.error = {summary:error};
            this.loading['reviewstatus'] = false;
        });
      },
      first())
    ).subscribe(res => {
      this.auditPlanData = res;
      this.audit_plan_id = res.id;
	    this.product_addition_id = res.product_addition_id;
      this.loading = false;
    },
    error => {
        this.error = {summary:error};
        this.loading = false;
    });

  }
  
  status_error = false;
  comment_error = false;
  date_error = false;
  checkUserSel()
  {
	this.f.status.markAsTouched();
	this.f.comment.markAsTouched();
	
	//let status = this.f.get('status').value;
	//let comment = this.f.get('comment').value;
	//let extension_date = this.f.get('extension_date').value;
	
	//console.log(status+'--'+comment);
    
	/*
    if(this.model.status !=='' && this.model.status !== undefined){
      this.status_error =false;
    }else{
      this.status_error =true;
      
    }

    if(this.model.comment !=='' && this.model.comment !== undefined){
      this.comment_error = false;
    }else{
      this.comment_error = true;
    }
	*/

    if(this.form.get('status').value== 3)
    {
		this.f.extension_date.setValidators([Validators.required]);
		this.f.extension_date.updateValueAndValidity();
		this.f.extension_date.markAsTouched();
		
		/*
		if(this.model.extension_date !== '' && this.model.extension_date !== undefined)
		{	
			this.date_error = false;
		}
		else
		{
			this.date_error = true;
		}
		*/
		
    }else{
		this.f.extension_date.setValidators([]);
		this.f.extension_date.updateValueAndValidity();
		this.f.extension_date.markAsTouched();
	}

    
	if (this.form.valid && this.status_error === false && this.comment_error ===  false && this.date_error ===  false) 
	{
		let status = this.form.get('status').value;
		let extension_date = '';
		let comment = this.form.get('comment').value;
		if(this.form.get('status').value== 3)
		{
			extension_date = this.errorSummary.displayDateFormat(this.form.get('extension_date').value);
		}
		
		this.loading  = true;
		this.auditplanservice.approveCertificate({app_id:this.auditPlanData.app_id,certificate_id:this.certificate_id,extension_date:extension_date,comment:comment,status:status})
       .pipe(first())
       .subscribe(res => {
           if(res.status==1){
              this.success = {summary:res.message};
              setTimeout(() => {
                this.router.navigateByUrl('/certification/certificate-list'); 
              }, this.errorSummary.redirectTime); 
            }else if(res.status == 0){
              this.error = {summary:res.message};
            }else{
              this.error = {summary:res};
            }
            this.loading = false;
            this.modalss.close();
		},
		error => {
           this.error = {summary:error};
           this.loading = false;
		});
    }
   
  }

  showCertificatedraft(standard_id,type)
  {
    this.loading = true;
  
    this.auditplanservice.downloadCertificateFile({certificate_id:this.certificate_id,audit_id:this.id,audit_plan_id:this.audit_plan_id,standard_id:standard_id,type:"pdf"}).pipe(first())
    .subscribe(res => {
      this.loading = false;
      this.modalss.close();
      let certfile = this.auditPlanData.certFileList[standard_id]?this.auditPlanData.certFileList[standard_id]:'certificate_pdf'+standard_id+'.pdf';
      saveAs(new Blob([res],{type:'application/pdf'}),certfile);
      
    },
    error => {
        this.error = {summary:error};
        this.loading = false;
    });    
  }

  getSelectedValue(subtopicid)
  {
    //return 'test';
    let subtopic = this.subTopicList.find(x=>x.id ==subtopicid);
    if(subtopic !== undefined){
      return subtopic.name;
    }else{
      return '';
    }
  }
  modals:any;
  modaltitle:any;
  subTopicList:any = [];
  loadingData:any=[];
  open(content,arg='',data:any='',unitindex:any=0) {
    //let status = this.auditPlanData.arrEnumStatus['review_in_process'];
    let user_type = '';
    let status;
    let unitstatus;
    //console.log(arg);
    if(arg=='submitforreview'){
      status = this.auditPlanData.arrEnumStatus['review_in_process'];
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['review_in_process'];
    }else if(arg=='inspectionplancomplete'){
      status = this.auditPlanData.arrEnumStatus['inspection_plan_completed'];
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['inspection_plan_completed'];
    }else if(arg=='sendtocustomer'){
      status = this.auditPlanData.arrEnumStatus['awaiting_for_customer_approval'];
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['awaiting_for_customer_approval'];
    }else if(arg=='approveinspection'){
      status = this.auditPlanData.arrEnumStatus['approved'];
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['approved'];
    }else if(arg=='rejectinspection'){
      status = this.auditPlanData.arrEnumStatus['rejected'];
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['rejected'];
    }else if(arg=='executeAudit'){
      this.loadingData['subtopic'] = true;
      this.auditplanservice.getUnitSubtopic(data)
      .pipe(first())
      .subscribe(res => {
            
          if(res.status==1){
             this.subTopicList = res.data;
             //this.success = res.message;
          }else if(res.status == 0){
             this.error = res.message;
          }else{
             this.error = res;
          }
          this.loadingData['subtopic'] = false;
         
      },
      error => {
          this.error = error;
          this.loadingData['subtopic'] = false;
      });
      //this.subTopicList = this.auditPlanData['units'][unitindex].subtopics;
      // status = this.auditPlanData.arrEnumStatus['rejected'];
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['rejected'];
    }
    //this.arg = arg;
    
    
    //console.log(arg);
    //, { centered: true }
    this.modals = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    
    this.modals.result.then((result) => {
       //console.log(result);
       //console.log(arg);
       //console.log(this.model.subtopic);
      //return false;
      if(arg=='executeAudit'){
        //this.model.subtopic
        data.subtopic = this.model.subtopic;
        this.router.navigateByUrl('/audit/audit-execution?audit_plan_unit_id='+data.audit_plan_unit_id+'&audit_plan_id='+data.audit_plan_id+'&audit_id='+data.audit_id+'&subtopic='+data.subtopic+'&unit_id='+data.unit_id);
      }else if( arg == "generatecertificate"){
        this.generateCertificate(data);
      }

    }, (reason) => {
      //this.comments_error ='';
      //this.arg = '';
      //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
    });
  }
  
  modalss:any;
  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
 

  downloadInspection(audit_id)
  {
    this.loading  = true;
    this.inspectionplanservice.downloadInspectionPlan({audit_id})
     .pipe(first())
     .subscribe(res => {
      this.loading = false;
      this.modalss.close();
      saveAs(new Blob([res],{type:'application/pdf'}),'inspectionplan_'+audit_id+'.pdf');
    },
    error => {
        this.error = error;
        this.loading = false;
        this.modalss.close();
    });

  }

  downloadNCreport(audit_id,audit_plan_id)
  {
    this.loading  = true;
    this.auditexecutionservice.downloadNCreport({audit_id,audit_plan_id})
     .pipe(first())
     .subscribe(res => {
      this.loading = false;
      this.modalss.close();
      saveAs(new Blob([res],{type:'application/pdf'}),'findings_'+audit_id+'.pdf');
    },
    error => {
        this.error = error;
        this.loading = false;
        this.modalss.close();
    });
  }

  downloadunitNCreport(audit_id,audit_plan_id,audit_plan_unit_id)
  {
    this.loading  = true;
    this.auditexecutionservice.downloadunitNCreport({audit_id,audit_plan_id,audit_plan_unit_id})
     .pipe(first())
     .subscribe(res => {
      this.loading = false;
      this.modalss.close();
      saveAs(new Blob([res],{type:'application/pdf'}),'findings_'+audit_id+'.pdf');
    },
    error => {
        this.error = error;
        this.loading = false;
        this.modalss.close();
    });
  }    
  
  generateCertificate(data){
    //console.log(data);
    //return false;
    this.loading  = true;
    this.auditplanservice.generateCertificate(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            this.auditPlanData.plan_status = this.auditPlanData.arrEnumPlanStatus['generate_certificate'];
            this.success = res.message;
            setTimeout(() => {
              this.loading = true;
              this.auditPlanData = undefined;
              this.auditplanservice.getAuditPlanDetails({id:this.id,certificate_id:this.certificate_id}).pipe(first())
              .subscribe(res => {
                this.auditPlanData = res;
                this.audit_plan_id = res.id;
				this.product_addition_id = res.product_addition_id;
                this.loading = false;
              },
              error => {
                  this.error = {summary:error};
                  this.loading = false;
              });
              this.success = '';
              //this.router.navigateByUrl('/audit/list-audit-plan'); 
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = res.message;
          }else{
            this.error = res;
          }
          this.loading = false;
        
     },
     error => {
         this.error = error;
         this.loading = false;
     });
 }
 
 getAuditDetails(){
  this.auditPlanData=undefined;
  this.loading = true;
  this.auditplanservice.getAuditPlanDetails({id:this.id,certificate_id:this.certificate_id}).pipe(first())
  .subscribe(res => {
    this.auditPlanData = res;
    this.audit_plan_id = res.id;
	this.product_addition_id = res.product_addition_id;
    this.loading = false;
  },
  error => {
      this.error = {summary:error};
      this.loading = false;
  });
 }
 
 get f() { return this.form.controls; } 
}