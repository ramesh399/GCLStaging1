import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';
import { User } from '@app/models/master/user';

import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuditPlan } from '@app/models/audit/audit-plan';
import { AuditPlanService } from '@app/services/audit/audit-plan.service';
import { AuditExecutionService } from '@app/services/audit/audit-execution.service';
import { InspectionPlanService } from '@app/services/audit/inspection-plan.service';
import { AuthenticationService } from '@app/services';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {saveAs} from 'file-saver';
import { NgForm } from '@angular/forms';
import { TcInvocieListService } from '@app/services/transfer-certificate/tc-invoice/tc-invoice-list.service';
import { GenerateDetailService } from '@app/services/invoice/generate-detail.service';

@Component({
  selector: 'app-view-audit-plan',
  templateUrl: './view-audit-plan.component.html',
  styleUrls: ['./view-audit-plan.component.scss']
})
export class ViewAuditPlanComponent implements OnInit {
  reviewerhistroy: any = [];
  subTopics: any = [];
  subtopicna: any;
  lastIndex: number;
  stage_val: any;
  stage_arr: any = [];
  questionsStd: any =[];
  reviewernotes: any  =[];
  reviewer_note: any;
  showdetails: boolean=false;

  constructor(public errorSummary:ErrorSummaryService, private router:Router,
    private auditplanservice:AuditPlanService,private auditexecutionservice:AuditExecutionService,
    private inspectionplanservice:InspectionPlanService,private activatedRoute:ActivatedRoute,
    public AuditExecutionService:AuditExecutionService,
    public invoiceService: GenerateDetailService,
    private authservice:AuthenticationService, private modalService: NgbModal) { }
  id:number;
  auditPlanData:AuditPlan;
  error:any;
  success:any;
  loading=false;

  unit_id:any;
  userType:number;
  user_id_error='';
  userdetails:any;
  revieweruserList:User[];
  userdecoded:any;
  model: any = {assignsubtopicType:'', user_id:'',comments:'',subtopic:'',assign_subtopic:'',audit_plan_unit_id:'',technicalexpert_ids:[],technicalexpert_ids_changed:[]};
  panelOpenState=false;
  audit_plan_id:any;
  draftcertificatedata:any;
  tereviewer_error:any = '';

  followupdivcontentstatus:any=false;
  normaldivcontentstatus:any=false;

  ngOnInit() {
    
    this.id = this.activatedRoute.snapshot.queryParams.id;
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

    this.loading = true;

    this.AuditExecutionService.getReviewerHistroy(this.id).subscribe(async res => {
      if (res.status) {
       this.reviewernotes = res.notesdata;
        this.reviewerhistroy = await res.reviewerhistroy;
        this.reviewerhistroy.forEach(ele =>{
          ele.standard_label = '';
        });

        this.AuditExecutionService.getQuestionStandards().subscribe(async res =>{
          this.questionsStd = await res.auditExecutionChecklists;

          for(var i=0; i<this.reviewerhistroy.length; i++){
            for(var j=0; j<this.questionsStd.length; j++){
              if((this.reviewerhistroy[i].questions.localeCompare(this.questionsStd[j].name))==0){
                this.reviewerhistroy[i].standard_label = await this.questionsStd[j].standard_label;
              }
            }
          }
         });

        


        this.lastIndex = this.reviewerhistroy.length - 1;
        this.stage_val = this.reviewerhistroy[this.lastIndex].review_stage;
        //console.log(this.stage_val);
        for (var i = 0; i <= this.stage_val; i++) {
          this.stage_arr[i] = i;
        }
      }
    });
    
    this.getAuditDetails();

    /*
      this.auditPlanData.units.forEach((element,index) => {
        if(element.followup_status){
          this.changeAuditTab('followup',element.id);
        }else{
          this.changeAuditTab('normal',element.id);
        }
        
      })
      */

      /*
    this.auditplanservice.getAuditPlanDetails(this.id).pipe(first())
    .subscribe(res => {
      this.auditPlanData = res;
      this.audit_plan_id = res.id;
      this.loading = false;
      if(res['followup_status']){
        this.followupdivcontentstatus = true;
      }else{
        this.normaldivcontentstatus = true;
      }
     
    },
    error => {
        this.error = {summary:error};
        this.loading = false;
    });
    */


    this.auditplanservice.getAuditReviewer().pipe(first())
    .subscribe(resreviewers => {
      this.revieweruserList = resreviewers['data'];
    },
    error => {
        this.error = {summary:error};
    });

  }
  comments_error:any;
  status_error:any;

   calculatePercentage(val:any){
      
        let mandays = parseFloat(val); 
        let percentage = 20;
        let perc='0';
        if(isNaN(mandays) || isNaN(percentage)){
            perc='0'; 
        }else{
           	perc = ((mandays/100) * percentage).toFixed(2);
        }
        return perc;
    }
  checkUserComment(type)
  {
    if(type!=='forwarded_to_reviewer')
    {
      if(this.model.comments.trim() ==''){
        this.comments_error ='true';
      }else{
        this.comments_error ='';
        this.modals.close('Save');
      }
    }
    else
    {
      //this.status_error = false;
      this.comments_error = false;
      //if(this.model.status =='')
      //{
      //  this.status_error = true;
     // }
      //else if(this.model.comments.trim() =='')
      if(this.model.comments.trim() =='')
      {
        this.comments_error = true;
      }
      else
      {
        this.comments_error ='';
        this.modals.close('Save');
      }
    }
    
  }
  subtopic_error:any;
  checkSubTopic()
  {
    if(this.model.subtopic =='')
    {
      this.subtopic_error ='true';
    }
    else
    {
      this.subtopic_error ='';
      this.modals.close('Save');
    }
   }

   
  
   assign_subtopic_error:any;
   assignSubtopicsuccess:any;
   assignSubtopicerror:any;
   assignSubTopic()
   {
      this.assignSubtopicsuccess = '';
      this.assignSubtopicerror = '';
      /*if(this.model.assign_subtopic =='')
      {
        this.assign_subtopic_error ='true';
      }
      else
      {
        */
        this.assign_subtopic_error ='';
        this.loadingArr['assignSubtopic']=true;
        this.auditplanservice.saveSubtopics({subtopicType:this.model.assignsubtopicType,audit_plan_unit_id:this.model.audit_plan_unit_id,subtopic_id:this.model.assign_subtopic})
        .pipe(first()).subscribe(res => {
          if(res.status)
          { 
            this.assignSubtopicsuccess = {summary:res.message};
          }
          else
          {
            this.assignSubtopicerror = {summary:res.message};
          }
          setTimeout(() => {
            this.getAuditDetails();
            
            
            this.modalss.close();
            this.assignSubtopicsuccess = '';
            this.loadingArr['assignSubtopic']=false;
          }, this.errorSummary.redirectTime);
        });
        
     // }
   }
  

    showCertificatedraft(content,standard_id,type)
    {
      this.loading = true;
      if(type=='draft')
      {
        this.modalss = this.modalService.open(content, {size:'lg',ariaLabelledBy: 'modal-basic-title'});
        this.auditplanservice.getStdCertificateDetails({audit_plan_id:this.audit_plan_id,standard_id:standard_id,type:""}).pipe(first())
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
        this.auditplanservice.downloadCertificateFile({audit_plan_id:this.audit_plan_id,standard_id:standard_id,type:"pdf"}).pipe(first())
        .subscribe(res => {
          this.loading = false;
          this.modalss.close();
          saveAs(new Blob([res],{type:'application/pdf'}),'certificate_pdf'+standard_id+'.pdf');
          
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
    getSelectedTEValue(teid){
      let tedata = this.assignReviewerDetails.userListArr.find(x=>x.id ==teid);
      if(tedata !== undefined){
        return tedata.displayname;
      }else{
        return '';
      }
    }
    getChangeTEValue(teid){
      let tedata = this.changeReviewerDetails.userListArr.find(x=>x.id ==teid);
      if(tedata !== undefined){
        return tedata.displayname;
      }else{
        return '';
      }
    }
  modals:any;
  modaltitle:any;
  subTopicList:any = [];
  loadingData:any=[];
  confirmTitle:any;
  assignReviewerDetails:any={};
  changeReviewerDetails:any={};
  invalidSubTopicList = [];
  audit_report_valid:any=false;
  audit_report_message:any = '';
  audit_report_title:any = '';
  open(content,arg='',data:any='',unitindex:any=0) {
    //let status = this.auditPlanData.arrEnumStatus['review_in_process'];
    let user_type = '';
    let status;
    let unitstatus;
    let comments:any = '';
    this.model.status='';
    this.model.comments='';
    this.status_error='';
    this.comments_error='';
    //console.log(arg);
    if(arg=='sendaudittoauditor'){
      this.confirmTitle = 'Are you sure, do you want to submit the remediation to Auditor?';
    }else if(arg=='followup_sendaudittoreviewer'){
      this.confirmTitle = 'Are you sure, do you want to submit the remediation to Reviewer?';
    }else if(arg=='sendbackaudittocustomer'){
      this.confirmTitle = 'Are you sure, do you want to submit the remediation to customer?';
    }else if(arg=='sendaudittoreviewer'){
      this.confirmTitle = 'Are you sure, do you want to submit the remediation to Reviewer?';
    }else if(arg=='sendbackaudittoauditor'){
      this.confirmTitle = 'Are you sure, do you want to submit the remediation to Auditor?';
    }else if(arg=='sendbackfollowupaudittoleadauditor'){
      this.confirmTitle = 'Are you sure, do you want to send back to Auditor?';
    }else if(arg=='sendbackfollowupaudittoauditor'){
      this.confirmTitle = 'Are you sure, do you want to send back to Auditor?';
    }else if(arg=='submitforreview'){
      status = this.auditPlanData.arrEnumStatus['review_in_process'];
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['review_in_process'];
    }else if(arg=='inspectionplancomplete'){
      status = this.auditPlanData.arrEnumStatus['inspection_plan_completed'];
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['inspection_plan_completed'];
    }else if(arg=='sendtocustomer'){
      status = this.auditPlanData.arrEnumStatus['awaiting_for_customer_approval'];
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['awaiting_for_customer_approval'];
    }else if(arg == 'followup_sendtocustomer'){
      status = this.auditPlanData.arrEnumStatus['awaiting_followup_customer_approval'];
    }else if(arg == 'approveplanbyauditor'){
      status = this.auditPlanData.arrEnumStatus['approved'];
    }else if(arg=='approveinspection'){
      status = this.auditPlanData.arrEnumStatus['approved'];
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['approved'];
    }else if(arg=='rejectinspection'){
      status = this.auditPlanData.arrEnumStatus['rejected'];
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['rejected'];
    }else if(arg=='followup_approveinspection'){
      status = this.auditPlanData.arrEnumStatus['followup_booked'];
    }else if(arg=='followup_rejectinspection'){
      status = this.auditPlanData.arrEnumStatus['followup_rejected_by_customer'];
      //comments = this.model.comments;
    }else if(arg=='sendfollowupaudit'){
      this.confirmTitle = 'Are you sure, do you want to submit for Followup Audit?';
     // this.submitForAuditFollowup();
    }else if(arg=='executeAudit' || arg=='followup_executeAudit'){
      this.subTopicList = [];
      //console.log(this.model.subtopic);
      this.model.subtopic = [];
      this.model.audit_plan_unit_id = data['audit_plan_unit_id'];
      this.loadingData['subtopic'] = true;
      data['subtopictype'] = arg;
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
    }else if( arg == "assignreviewer"){
      
      let data:any = {app_id:this.auditPlanData.app_id,audit_id:this.id};
      this.loadingData['assignreviewer'] = true;
      this.assignReviewerDetails = {};
      this.auditplanservice.getAssignReviewerDetails(data)
      .pipe(first())
      .subscribe(res => {
            
          if(res.status==1){
             this.assignReviewerDetails = res['data'];
             this.assignReviewerDetails['status'] = 1;
             this.assignReviewerDetails['userbsectordetailslength'] = Object.keys(this.assignReviewerDetails.userbsectordetails).length;
             //console.log(Object.keys(this.assignReviewerDetails.userbsectordetails).length);
             //this.success = res.message;
          }else if(res.status == 0){
             this.error = res.message;
          }else{
             this.error = res;
          }
          this.loadingData['assignreviewer'] = false;
         
      },
      error => {
          this.error = error;
          this.loadingData['assignreviewer'] = false;
      });
    }else if(arg == "sendtoleadauditor"){
     // let data:any = {app_id:this.auditPlanData.app_id};
      //this.loadingData['assignreviewer'] = true;
      this.loadingArr['reportvalidation'] = true;
      this.audit_report_valid = false;
      this.audit_report_message = '';
      this.audit_report_title = '';
      
       this.auditplanservice.getUnitSubtopic(data)
      .pipe(first())
      .subscribe(res => {
          if(res.status==1){
             this.subTopicList = res.data;
             let subTopic = this.subTopicList.map(el => el.id).join(",")
 
               let getData = `audit_id=${data.audit_id}&audit_plan_id=${data.audit_plan_id}&unit_id=${data.unit_id}&sub_topic_id=${subTopic}`;
   // this.auditExecution.getReviewQuestionsByGet({audit_id:this.audit_id,audit_plan_id:this.audit_plan_id,unit_id:this.unit_id,sub_topic_id:this.sub_topic_id}).pipe(
 
           // If not all field are mandatory
            // for(let key in this.auditexecutionservice.validationResult) {
            //   if(!this.auditexecutionservice.validationResult[key] && this.subTopicList.filter(iel => key == iel.id)[0])
            //     this.invalidSubTopicList.push(this.subTopicList.filter(iel => key == iel.id)[0].name);
            // }
    
    
    
            
 
            this.auditexecutionservice.getReviewQuestionsByGet(getData).pipe(
              first()
            ).subscribe(res => {
                  res.questionList.forEach(el => {
                  if(el.answer != "3" && ( el.answer == "" 
                        || el.finding.trim() == ""
                        || (el.file_required == "1" && el.file == "" && el.answer == "1")
                        || (el.answer == 2 && el.severity == ""))) {
                          this.invalidSubTopicList.push(this.subTopicList.filter(iel => el.sub_topic_id == iel.id)[0].name);
                        }
                  })

          let uniqueSubTopic = [...new Set(  this.invalidSubTopicList)]

                    this.auditplanservice.checkAuditReport(data)
            .pipe(first())
            .subscribe(res => {
              this.loadingArr['reportvalidation'] = false;
              let message = ""
              if(this.invalidSubTopicList.length != 0) {
              if (!res['audit_report_valid']) {
               // message = ` <li class="text-danger m-t-10 m-l-15 m-r-15"> Field Missing under some Sub-Topic	( 	${ uniqueSubTopic.join(" ,")}	 ) 	</li> 		</div>`
               message = ` 	
                    <div class="text-danger m-t-10 m-l-15 m-r-15"> 		
                    <strong>This Audit Report details are empty/blank. You should enter data before submitting for Lead Auditor:</strong><br>
                              <ul>	 	 <li> Field Missing under some Sub-Topic	( 	${ uniqueSubTopic.join(" ,")}	 ) 	</li>	  	</ul> 		</div>`
              } else {

                message = ` 	
                    <div class="text-danger m-t-10 m-l-15 m-r-15"> 		
                    <strong>This Audit Report details are empty/blank. You should enter data before submitting for Lead Auditor:</strong><br>
                              <ul>	 	 <li> Field Missing under some Sub-Topic	( 	${ uniqueSubTopic.join(" ,")}	 ) 	</li>	  	</ul> 		</div>`
              }

              }

             
            
            
              this.audit_report_message = this.invalidSubTopicList.length == 0 ? 
                            res['audit_report_message'] :  res['audit_report_valid'] ? message :    res['audit_report_message'] + message; 
              this.audit_report_valid = this.invalidSubTopicList.length != 0 ? false : res['audit_report_valid'];
              this.audit_report_title = res['audit_report_title'];
            },
            error => {
              this.loadingArr['reportvalidation'] = false;
                this.error = error;
                
            });
 
                  
                  })
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
      
    }else if(arg == "submitforreviewer"){
     // let data:any = {app_id:this.auditPlanData.app_id};
      //this.loadingData['assignreviewer'] = true;
      this.loadingArr['reportvalidation'] = true;
      this.audit_report_valid = false;
      this.audit_report_message = '';
      this.audit_report_title = '';
      
      this.auditplanservice.checkAuditReport(data)
      .pipe(first())
      .subscribe(res => {
        this.loadingArr['reportvalidation'] = false;
        this.audit_report_message = res['audit_report_message'];
        this.audit_report_valid = res['audit_report_valid'];
        this.audit_report_title = res['audit_report_title'];
      },
      error => {
        this.loadingArr['reportvalidation'] = false;
          this.error = error;
          
      });
    }else if(arg=='followupsubmitforreview'){
      status = this.auditPlanData.arrEnumStatus['followup_review_in_process'];
    }else if(arg == "followup_sendtoleadauditor"){
      // let data:any = {app_id:this.auditPlanData.app_id};
       //this.loadingData['assignreviewer'] = true;
      this.confirmTitle = 'Are you sure, do you want to submit for lead auditor?';
     }

    //this.arg = arg;
    
    
    //console.log(arg);
    //, { centered: true }
    this.modals = this.modalService.open(content, {size:'lg',ariaLabelledBy: 'modal-basic-title',centered: true});
    
    this.modals.result.then((result) => {
       //console.log(result);
       //console.log(arg);
       //console.log(this.model.subtopic);
      //return false;
      if(arg=='sendaudittoauditor' || arg=='sendbackaudittocustomer' || arg=='sendaudittoreviewer' || arg=='sendbackaudittoauditor' || arg=='followup_sendaudittoreviewer' || arg=='sendbackfollowupaudittoleadauditor' || arg=='sendbackfollowupaudittoauditor'){
       this.sendAudit({audit_plan_id:data.audit_plan_id,actiontype:arg});
      }else if(arg=='executeAudit'){
        //this.model.subtopic
        data.subtopic = this.model.subtopic;
        let tempSubTopic = []
      
        this.subTopicList.forEach(element => {
          this.model.subtopic.forEach(subTopic => {
            if(element.id == subTopic) {
             tempSubTopic.push(element);
            }
          });
        });
        tempSubTopic.forEach(el => {
          el.name = el.name.replace("&", 'and')
        })
        this.router.navigateByUrl('/audit/audit-execution?app_id='+this.auditPlanData.app_id+'&audit_plan_unit_id='+data.audit_plan_unit_id+'&audit_plan_id='+data.audit_plan_id+'&audit_id='+data.audit_id+'&subtopic='+data.subtopic+'&unit_id='+data.unit_id+'&subtopicname='+ JSON.stringify(tempSubTopic));
      }else if(arg=='followup_executeAudit'){
        data.subtopic = this.model.subtopic;
        ///audit/audit-findings?app_id=295&audit_plan_id=118&audit_id=118&unit_id=482&audit_plan_unit_id=273
        //this.router.navigateByUrl('/audit/audit-execution?app_id='+this.auditPlanData.app_id+'&audit_plan_unit_id='+data.audit_plan_unit_id+'&audit_plan_id='+data.audit_plan_id+'&audit_id='+data.audit_id+'&subtopic='+data.subtopic+'&unit_id='+data.unit_id);
        this.router.navigateByUrl('/audit/audit-findings?app_id='+this.auditPlanData.app_id+'&audit_plan_unit_id='+data.audit_plan_unit_id+'&audit_plan_id='+data.audit_plan_id+'&audit_id='+data.audit_id+'&subtopic='+data.subtopic+'&unit_id='+data.unit_id);
      }else if( arg == "assignreviewer"){
       // this.assignReviewer({audit_plan_id:data.audit_plan_id,reviewer_id:this.userdetails.uid});
      }else if( arg == "submit_for_auditreview"){
        this.auditReview({audit_plan_id:data.audit_plan_id,reviewer_id:this.userdetails.uid});
      }else if( arg == "sendaudittocustomer"){
        this.sendToCustomer({audit_plan_id:data.audit_plan_id});
      }else if( arg == "sendtoleadauditor"){
        this.sendToLeadAuditor(data);
      }else if( arg == "followup_sendtoleadauditor"){
        this.followupsendToLeadAuditor(data);
      }else if( arg == "submitforreviewer"){
        this.sendToReviewer(data);
      }else if( arg == "generatecertificate"){
        this.generateCertificate(data);
      }else if( arg == "sendfollowupaudit"){
        this.submitForAuditFollowup();
      }else if(arg == "forwardtoreviewer"  || arg=='reviewNCoverdue'){
        data.argaction = arg;
        this.changeNCoverdueStatus(data);
      }else{
        this.changeStatus({argaction:arg,status,audit_id:this.id,audit_plan_id:this.audit_plan_id,comments:this.model.comments});
      }

    }, (reason) => {
      //this.comments_error ='';
      //this.arg = '';
      //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
    });
  }
  
  normalcontentstatus:any=[];
  followupcontentstatus:any=[];
  changeAuditUnitTab(arg,unitid:any='')
  {
    //console.log(arg+'=='+unitid);
    this.normalcontentstatus[unitid] = false;
    this.followupcontentstatus[unitid] = false;
	  if(arg=='normal'){
      //console.log(arg);
      this.normalcontentstatus[unitid] = true;
    }else if(arg=='followup'){
      //console.log(arg);
      this.followupcontentstatus[unitid] = true;
    }
  }
  changeAuditTab(arg,unitid:any='')
  {
    //console.log(arg+'=='+unitid);
    this.normaldivcontentstatus = false;
    this.followupdivcontentstatus = false;
	  if(arg=='normal'){
      //console.log(arg);
      this.normaldivcontentstatus = true;
    }else if(arg=='followup'){
      //console.log(arg);
      this.followupdivcontentstatus = true;
    }
  }

  submitForAuditFollowup(){
    let data:any = {audit_id:this.id,audit_plan_id:this.audit_plan_id};
    this.loading  = true;
    this.auditplanservice.submitForAuditFollowup(data)
     .pipe(first())
     .subscribe(res => {
        if(res.status==1){
          this.success = res.message;
          setTimeout(() => {
            this.success = '';
            this.getAuditDetails();
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

  modalss:any;
  openmodal(content,arg='',data:any='') {
    this.user_id_error ='';
    this.assign_subtopic_error ='';
    this.assignSubtopicsuccess ='';
    this.assignSubtopicerror ='';
    
    if(arg=='assignSubtopic' || arg=='followup_assignSubtopic')
    {
      this.subTopicList = [];
      this.model.subtopic = [];
      this.model.audit_plan_unit_id = data['audit_plan_unit_id'];
      this.loadingData['subtopic'] = true;
      data['subtopictype'] = arg;
      this.model.assignsubtopicType = arg;
      this.auditplanservice.getUnitAssignSubtopic(data)
      .pipe(first())
      .subscribe(res => {
            
          if(res.status==1){
            this.subTopicList = res.data;

             //this.model.assign_subtopic = [1,2,3];
             //this.success = res.message;
            let selecteFilter = res.data
            .filter(xx => xx.selected==1)
            .map(selfilt => selfilt.id);
            this.model.assign_subtopic = selecteFilter;

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
    }
    
    this.modalss = this.modalService.open(content, {size:'lg',ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  

  getReviewerDetails(user_id)
  {
    if(user_id)
    {
      let data:any = {app_id:this.auditPlanData.app_id,user_id:user_id,type:"chage_reviewer"};
      this.loadingData['changereviewer'] = true;
      this.changeReviewerDetails = {};
      this.auditplanservice.getAssignReviewerDetails(data)
      .pipe(first())
      .subscribe(res => {
            
          if(res.status==1){
             this.changeReviewerDetails = res['data'];
             this.changeReviewerDetails['status'] = 1;
             this.changeReviewerDetails['userbsectordetailslength'] = Object.keys(this.changeReviewerDetails.userbsectordetails).length;
          }else if(res.status == 0){
             this.error = res.message;
          }else{
             this.error = res;
          }
          this.loadingData['changereviewer'] = false;
         
      },
      error => {
          this.error = error;
          this.loadingData['changereviewer'] = false;
      });
    }
    
  }

  changedtechnicalselerror:any = '';
  checkUserSel(user_type='',action='')
  {
    if(this.model.user_id ==''){
      this.user_id_error ='true';
    }else{
      let technicalexpert_ids_changed:any;
      this.changedtechnicalselerror = '';
      if(Object.keys(this.changeReviewerDetails.userbsectordetails).length>0)
      {
        technicalexpert_ids_changed = this.model.technicalexpert_ids_changed;
        if(technicalexpert_ids_changed.length<=0){
          this.changedtechnicalselerror = 'Please select technical expert';
          return false;
        }
      }

      this.loading  = true;
      this.user_id_error ='';
      this.auditplanservice.changeReviewer({actiontype:action,audit_plan_id:this.id,user_id:this.model.user_id,technicalexpert_ids:technicalexpert_ids_changed,userbsectorcheckdetails:this.changeReviewerDetails.userbsectorcheckdetails})
     .pipe(first())
     .subscribe(res => {
           
         if(res.status){
          
            this.success = res.message;
            this.modalss.close();
            setTimeout(() => {
              this.getAuditDetails();
              this.success = ''; 
              this.model.user_id = '';
              this.model.technicalexpert_ids_changed = '';
              this.changeReviewerDetails = '';
            }, this.errorSummary.redirectTime);
            
          }else if(res.status == 0){
            this.error = res.message;
          }else{
            this.error = res;
          }
          this.loading = false;
        
     },
     error => {
         this.error = {summary:error};
         this.loading = false;
     });
    }
    
  }


  sendToReviewer(data){
    this.loading  = true;
    this.auditplanservice.sendToReviewer(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            this.auditPlanData.plan_status = res.data.plan_status;
            let plan_unit_status = res.data.plan_unit_status;
            this.auditPlanData.units.forEach((element,index) => {
              this.auditPlanData.units[index].status = plan_unit_status;
            });

            if(this.auditPlanData.reviewer_id){
              this.auditPlanData.plan_status = this.auditPlanData.arrEnumPlanStatus['review_in_progress'];
            }
            
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

  downloadInspection(audit_id,inspectionplan_id)
  {
    this.loading  = true;
    this.inspectionplanservice.downloadInspectionPlan({audit_id,inspectionplan_id})
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

  downloadAuditreport(audit_id,audit_plan_id,standard_id,unit_id='')
  {
    this.loading  = true;
    this.auditexecutionservice.downloadAuditreport({audit_id,audit_plan_id,standard_id,unit_id})
     .pipe(first())
     .subscribe(res => {
      this.loading = false;
      this.modalss.close();
      saveAs(new Blob([res],{type:'application/pdf'}),'auditreport_'+audit_id+'.pdf');
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


  followupsendToLeadAuditor(data){
    this.loading  = true;
    this.auditplanservice.followupsendToLeadAuditor(data)
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
            this.auditPlanData.plan_status = this.auditPlanData.arrEnumPlanStatus['generate_certificate'];
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

  
  sendAudit(data){
  this.loading  = true;
  this.auditplanservice.sendAudit(data)
   .pipe(first())
   .subscribe(res => {
         
       if(res.status==1){
          this.auditPlanData.plan_status = data.status;
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
              this.getAuditDetails();
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

  changeNCoverdueStatus(data){
  
    this.loading  = true;

    //data.answer = this.model.status;
    data.comments = this.model.comments;

    this.auditplanservice.changeNCoverdueStatus(data)
    .pipe(first())
    .subscribe(res => {
          
        if(res.status==1){
            this.auditPlanData.status = data.status;
            this.success = res.message;
            setTimeout(() => {
              this.getAuditDetails();
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
  this.auditplanservice.getAuditPlanDetails(this.id).pipe(first())
  .subscribe(res => {
    this.auditPlanData = res;
    this.auditPlanData.units.forEach(el => {
      let data = {}
      data[el.unit_id] = el.id
       localStorage.setItem("unitid", JSON.stringify(data))
    })
    localStorage.setItem("appid", res.app_id)
    this.audit_plan_id = res.id;
    if(res['show_followup_status'] && res['followup_status'] && this.auditPlanData.status!=this.auditPlanData.arrEnumStatus['followup_open']){
      this.followupdivcontentstatus = true;
    }else{
      this.normaldivcontentstatus = true;
    }

    for(var i=0; i<this.auditPlanData.units.length; i++){
      if(this.auditPlanData.units[i].unit_brand_consent==1){
        this.showdetails =true;
      }
    }
    this.loading = false;
  },
  error => {
      this.error = {summary:error};
      this.loading = false;
  });
 }
 
 reviewererror:any;
 reviewersuccess:any;
 loadingArr:any = [];
 technicalselerror:any = '';
 addReviewerwithte(){
  //this.auditPlanData=undefined;
  this.reviewersuccess = '';
  let technicalexpert_ids:any;
  this.technicalselerror = '';
  if(Object.keys(this.assignReviewerDetails.userbsectordetails).length>0)
  {
    technicalexpert_ids = this.model.technicalexpert_ids;
    if(technicalexpert_ids.length<=0){
      this.technicalselerror = 'Please select technical expert';
      return false;
    }
  }
  this.loadingArr['addAssignReviewer'] = true;
  this.auditplanservice.getAddAssignReviewerDetails({audit_plan_id:this.audit_plan_id,technicalexpert_ids:technicalexpert_ids,userbsectorcheckdetails:this.assignReviewerDetails.userbsectorcheckdetails}).pipe(first())
  .subscribe(res => {
    if(res.teerror){
      this.reviewererror = {summary:res['reviewererror']};
      this.loadingArr['addAssignReviewer'] = false;
    }else{
      if(res['reviewersuccess']!=''){
        this.reviewersuccess = {summary:res['reviewersuccess']};
      }
      if(res['reviewererror']!=''){
        this.reviewererror = {summary:res['reviewererror']};
      }
      this.getAuditDetails();
      setTimeout(() => {
        this.loadingArr['addAssignReviewer'] = false;
        this.reviewersuccess = '';
        this.reviewererror = '';
        this.modals.close();
        //this.reviewererror = '';
        //this.reviewersuccess = '';
        
        //this.auditPlanData.reviewer_id = res.reviewer_id;
        //this.auditPlanData.plan_status = this.auditPlanData.arrEnumPlanStatus['review_in_progress'];
        
      }, this.errorSummary.redirectTime);
    }
    
    

    

   
  },
  error => {
      this.error = {summary:error};
      this.loadingArr['addAssignReviewer'] = false;
  });
 }
 
 editProduct(content) {	
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title',centered: true});
 }
}
