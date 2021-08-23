import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';

import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuditPlan } from '@app/models/audit/audit-plan';
import { AuditPlanService } from '@app/services/certification/audit-plan.service';
import { AuditExecutionService } from '@app/services/audit/audit-execution.service';
import { InspectionPlanService } from '@app/services/audit/inspection-plan.service';
import { AuthenticationService } from '@app/services';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {saveAs} from 'file-saver';
import { environment } from '@environments/environment';
@Component({
  selector: 'app-view-audit-plan',
  templateUrl: './view-audit-plan.component.html',
  styleUrls: ['./view-audit-plan.component.scss']
})
export class ViewAuditPlanComponent implements OnInit {

  constructor(public errorSummary:ErrorSummaryService, private router:Router,private auditplanservice:AuditPlanService,private auditexecutionservice:AuditExecutionService,private inspectionplanservice:InspectionPlanService,private activatedRoute:ActivatedRoute,private authservice:AuthenticationService, private modalService: NgbModal) { }
  id:number;
  certificate_id:number;
  product_addition_id:number
  auditPlanData:AuditPlan;
  error:any;
  success:any;
  loading=false;

  userType:number;
  userdetails:any;
  userdecoded:any;
  model: any = {comments:'',subtopic:''};
  panelOpenState=false;
  audit_plan_id:any;
  draftcertificatedata:any;
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
	
	this.loadAuditPlanData();  
  }
  comments_error:any;
  checkUserComment(){
    if(this.model.comments.trim() ==''){
      this.comments_error ='true';
    }else{
      this.comments_error ='';
      this.modals.close('Save');
    }
   }
   subtopic_error:any;
  checkSubTopic(){
    if(this.model.subtopic ==''){
      this.subtopic_error ='true';
    }else{
      this.subtopic_error ='';
      this.modals.close('Save');
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
	  
    showCertificatedraftBk(content,standard_id,type)
    {
      this.loading = true;
      if(type=='draft')
      {
        this.modalss = this.modalService.open(content, {size:'lg',ariaLabelledBy: 'modal-basic-title'});
        this.auditplanservice.getStdCertificateDetails({certificate_id:this.certificate_id,audit_id:this.id,audit_plan_id:this.audit_plan_id,standard_id:standard_id,type:""}).pipe(first())
        .subscribe(res => {
          this.loading = false;
         
            this.draftcertificatedata=res.draftData;
            
              // saveAs(new Blob([res],{type:'application/pdf'}),'certificate_'+type+'.pdf');
          
        },
        error => {
            this.error = {summary:error};
            this.loading = false;
        });
      }
      else
      {
        this.auditplanservice.downloadCertificateFile({audit_id:this.id,audit_plan_id:this.audit_plan_id,standard_id:standard_id,type:"pdf"}).pipe(first())
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

     
    }

   getSelectedValue(subtopicid){
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
       // window.location.href = environment.apiUrl+'/audit/audit-execution?audit_plan_unit_id='+data.audit_plan_unit_id+'&audit_plan_id='+data.audit_plan_id+'&audit_id='+data.audit_id+'&subtopic='+data.subtopic+'&unit_id='+data.unit_id;
        //location.reload();
        this.router.navigateByUrl('/audit/audit-execution?audit_plan_unit_id='+data.audit_plan_unit_id+'&audit_plan_id='+data.audit_plan_id+'&audit_id='+data.audit_id+'&subtopic='+data.subtopic+'&unit_id='+data.unit_id);
      }else if( arg == "assignreviewer"){
        this.assignReviewer({audit_plan_id:data.audit_plan_id,reviewer_id:this.userdetails.uid});
      }else if( arg == "submit_for_auditreview"){
        this.auditReview({audit_plan_id:data.audit_plan_id,reviewer_id:this.userdetails.uid});
      }else if( arg == "sendaudittocustomer"){
        this.sendToCustomer({audit_plan_id:data.audit_plan_id});
      }else if( arg == "sendtoleadauditor"){
        this.sendToLeadAuditor(data);
      }else if( arg == "submitforreviewer"){
        this.sendToReviewer(data);
      }else if( arg == "generatecertificate"){
        this.generateCertificate(data);
      }else{
       this.changeStatus({status,audit_id:this.id,audit_plan_id:this.audit_plan_id});
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


  sendToReviewer(data){
    this.loading  = true;
    this.auditplanservice.sendToReviewer(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status){
           /*
            this.auditPlanData.plan_status = res.data.plan_status;
            let plan_unit_status = res.data.plan_unit_status;
            this.auditPlanData.units.forEach((element,index) => {
              this.auditPlanData.units[index].status = plan_unit_status;
            });

            if(this.auditPlanData.reviewer_id){
              this.auditPlanData.plan_status = this.auditPlanData.arrEnumPlanStatus['review_in_progress'];
            }
            */
            this.success = res.message;
            setTimeout(() => {
              this.success = '';
              this.loadAuditPlanData();  

              //this.router.navigateByUrl('/audit/list-audit-plan'); 
            }, this.errorSummary.redirectTime);
          }else if(res.status == '0'){
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

  downloadcbFile(fileid,filename,type){
    this.auditplanservice.downloadcbFile({id:fileid,type})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    
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
  successUnitIndex:any;
  unitsuccess:any;
  uniterror:any;
  sendToLeadAuditor(data){
    this.loading  = true;
    this.auditplanservice.sendToLeadAuditor(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            let unitIndex = this.auditPlanData.units.findIndex(x=>x.id ==data.audit_plan_unit_id);
            this.auditPlanData.units[unitIndex].status = res.data.status;

            this.auditPlanData.plan_status = res.data.plan_status;
            this.successUnitIndex = data.audit_plan_unit_id;
            this.unitsuccess = {summary:res.message};
            setTimeout(() => {
              this.getAuditDetails();
              this.successUnitIndex = '';
              //this.router.navigateByUrl('/audit/list-audit-plan'); 
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.uniterror = {summary:res.message};
          }else{
            this.uniterror = {summary:res};
          }
          this.loading = false;
        
     },
     error => {
        this.uniterror = {summary:error};
        this.loading = false;
    });
  }
  
  auditReview(data){
    //console.log(data);
    //return false;
    this.loading  = true;
    this.auditplanservice.changetoAuditReview(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            this.auditPlanData.plan_status = this.auditPlanData.arrEnumPlanStatus['audit_checklist_inprocess'];
            this.success = res.message;
            setTimeout(() => {
              this.success = '';
              this.getAuditDetails();
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


  generateCertificate(data){
    //console.log(data);
    //return false;
    this.loading  = true;
    this.auditplanservice.generateCertificate(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //this.auditPlanData.plan_status = this.auditPlanData.arrEnumPlanStatus['generate_certificate'];
            this.success = res.message;
            setTimeout(() => {
              this.loading = true;
              //this.auditPlanData = undefined;
              //this.loadAuditPlanData();
              this.success = '';
              this.router.navigateByUrl('/certification/view-certificate?id='+this.id+'&certificate_id='+this.certificate_id);
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = res.message;
             this.loading = false;
          }else{
            this.error = res;
             this.loading = false;
          }
         
        
     },
     error => {
         this.error = error;
         this.loading = false;
     });
 }

  assignReviewer(data){
    //console.log(data);
    //return false;
    this.loading  = true;
    this.auditplanservice.assignReviewer(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //this.enquirydata.status = res.enquirystatus;
            //this.enquirydata.status_updated_date = res.status_updated_date;
            //this.enquirydata.status_updated_by = res.status_update_by;
           // this.auditPlanData.status = data.status;
            this.auditPlanData.reviewer_id = res.reviewer_id;
            this.auditPlanData.plan_status = this.auditPlanData.arrEnumPlanStatus['review_in_progress'];
            this.success = res.message;
            setTimeout(() => {
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

 
 sendToCustomer(data){
  this.loading  = true;
  this.auditplanservice.sendToCustomer(data)
   .pipe(first())
   .subscribe(res => {
         
       if(res.status==1){
          this.auditPlanData.plan_status = data.status;
          this.success = res.message;
          setTimeout(() => {
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

  changeStatus(data){
    //console.log(data);
    //return false;
    this.loading  = true;
    this.auditplanservice.changeStatus(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //this.enquirydata.status = res.enquirystatus;
            //this.enquirydata.status_updated_date = res.status_updated_date;
            //this.enquirydata.status_updated_by = res.status_update_by;
            this.auditPlanData.status = data.status;
            this.success = res.message;
            setTimeout(() => {
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
		this.loadAuditPlanData();
	}
 
	assignCertificationReviewer()
    {
		this.modalss.close('');
		this.loading = true;
		this.auditplanservice.assignCertificationReviewer({certificate_id:this.certificate_id,product_addition_id:this.auditPlanData.product_addition_id}).pipe(first())
		.subscribe(res => {
			if(res.status)
			{
				this.success =  res.message;						  
				setTimeout(() => {
					this.loading = false;
					this.success = '';
					this.loadAuditPlanData();
				}, this.errorSummary.redirectTime);          
			}
		},
		error => {
			this.error = error;
			this.loading = false;
		});
    }
  
  cur_standard_id = [];
	loadAuditPlanData()
	{
		this.loading = true;
		this.auditplanservice.getAuditPlanDetails({id:this.id,certificate_id:this.certificate_id}).pipe(first())
		.subscribe(res => {
      this.auditPlanData = res;
      this.cur_standard_id = [this.auditPlanData.standard_id];
			this.audit_plan_id = res.id;
			this.product_addition_id = res.product_addition_id;		  
			this.loading = false;
		},
		error => {
			this.error = {summary:error};
			this.loading = false;
		});
	}
	
	selectProduct(content) {	
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title',centered: true});
	}
    
}
