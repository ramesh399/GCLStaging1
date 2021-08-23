import { Component, OnInit } from '@angular/core';
import { first,tap,map } from 'rxjs/operators';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl, Form } from '@angular/forms';
import { FindingsCorrectiveActionService } from '@app/services/audit/findings-corrective-action.service';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditExecutionService } from '@app/services/audit/audit-execution.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import {saveAs} from 'file-saver';
import { SubTopicService } from '@app/services/master/sub-topic/sub-topic.service';

import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/services';

@Component({
  selector: 'app-audit-report-review',
  templateUrl: './audit-report-review.component.html',
  styleUrls: ['./audit-report-review.component.scss']
})
export class AuditReportReviewComponent implements OnInit {

  userType:number;
  constructor(private activatedRoute:ActivatedRoute,private router: Router, private SubTopicService: SubTopicService,
    public auditExecution:AuditExecutionService,public errorSummary: ErrorSummaryService,   
    private FindingsCorrectiveActionService:FindingsCorrectiveActionService,private modalService: NgbModal,
     private authservice:AuthenticationService) { }
  audit_plan_id:any;
  audit_id:any;
  audit_plan_unit_id:any;
  finding_id:any;
  reviewer_note: any;

  checklistForm : any = {};
  guidanceIncludeList:Array<any> = [];
  buttonDisable=false;
  loading:any;
  success:any;
  error:any;
  unit_id:any;
  tabIndex: any;
  panelOpenState = true;
  
  questionList = [];
  loadingInfo:any=[];
  reviewcommentlist=[];
  answerList:any = [];
editQuestion: any = {};
  findingTypeList:any = [];
  reviewcomments:any = [];
  enableReportCorrection:any=0;
  questionValueList = []
subTopicList: any;

get appid () {
  return  localStorage.getItem("appid")
}

get unitid () {
  return JSON.parse(localStorage.getItem("unitid"))  
}

  ngOnInit() {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.audit_plan_id = this.activatedRoute.snapshot.queryParams.audit_plan_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
    this.audit_plan_unit_id = this.activatedRoute.snapshot.queryParams.audit_plan_unit_id;

    this.loadingInfo['questions'] = true;
       this.authservice.currentUser.subscribe(x => {
        if(x){
          let user = this.authservice.getDecodeToken();
          this.userType= user.decodedToken.user_type;
        }
      });

       this.SubTopicService.getSubTopicList().subscribe(res => {
          
      this.subTopicList = res['subtopics'];
      this.tabIndex = this.subTopicList[0] ? this.subTopicList[0].id: "";
    });

     /*
    this.answerList = {1:'Yes',2:'No'};
    this.findingTypeList = {1:'Desk Study',2:'Follow-up Audit'};
    this.questionList = [
      {id:1,name:'test test',interpretation:'test guidance1',expected_evidance:'test evidance',severitylist:{1:'High',2:'Medium',3:'Low'},file_required:1,yes_comment:'Yes Comment',no_comment:'No Comment'},
      {id:2,name:'test test 1',interpretation:'test guidance2',expected_evidance:'test evidance 2',severitylist:{1:'High',2:'Critical',3:'Low'},file_required:0,yes_comment:'Yes Comment 2',no_comment:'No Comment 2'},
      {id:3,name:'test test 2',interpretation:'test guidance3',expected_evidance:'test evidance 3',severitylist:{1:'High',2:'Medium',3:'Low'},file_required:0,yes_comment:'Yes Comment 3',no_comment:'No Comment 3'},
      {id:4,name:'test test 3',interpretation:'test guidance4',expected_evidance:'test evidance 4',severitylist:{1:'High',2:'Critical',3:'Low'},file_required:1,yes_comment:'Yes Comment 4',no_comment:'No Comment 4'},
    ];
    
    this.reviewcomments = [
      {id:1,answer:2,finding:'test finding 1',file:'test.jpg',severity:1,finding_type:'',revieweranswer:'',reviewercomment:'' },
      {id:2,answer:1,finding:'test finding 2',severity:2,finding_type:'',revieweranswer:'',reviewercomment:'' },
      {id:3,answer:1,finding:'test finding 3',file:'test2.jpg',severity:3,finding_type:'',revieweranswer:'',reviewercomment:'' },
      {id:4,answer:2,finding:'test finding 4',file:'test3.jpg',severity:1,finding_type:'',revieweranswer:'',reviewercomment:'' },
    ];
    */
    
    //this.loadingInfo['questions'] = false;
    this.auditExecution.getReviewerQuestions({audit_id:this.audit_id,audit_plan_id:this.audit_plan_id,unit_id:this.unit_id}).pipe(
      
      first()
    ).subscribe(res => {
      this.questionList = res['questionList'];
      this.answerList = res['answerList'];
      this.loadingInfo['questions'] = false;
      this.findingTypeList = res['findingTypeList'];

      if(this.questionList && this.questionList.length>0){
        this.questionList.forEach(val=>{
           
          val.questions.forEach(qval=>{
            this.editQuestion[qval.id] = true;
            this.formErr[ "" +qval.sub_topic_id] = true
            if(this.questionValueList.indexOf(qval.sub_topic_id) == -1)
            this.questionValueList.push(qval.sub_topic_id)
            this.reviewcommentlist['qtd_'+val.unit_id+'_'+qval.id]=qval.answer;
            this.reviewcommentlist['finding_'+val.unit_id+'_'+qval.id]=qval.finding;
            this.reviewcommentlist['severity_'+val.unit_id+'_'+qval.id]=qval.severity;


            this.reviewcommentlist['findingType_'+val.unit_id+'_'+qval.id]=qval.findingType?qval.findingType:'';
            this.reviewcommentlist['revieweranswer_'+val.unit_id+'_'+qval.id]=qval.revieweranswer?qval.revieweranswer:'';
            this.reviewcommentlist['reviewercomment_'+val.unit_id+'_'+qval.id]=qval.reviewercomment?qval.reviewercomment:'';

            if(qval.revieweranswer ==2){
              this.reviewerCorrection.push(val.unit_id+'_'+qval.id);
            }

            if(qval['file']){
              this.questionfile[val.unit_id+'_'+qval.id] = {name:qval['file'],type:'server'};
            }
            
          })
        })

        this.questionValueList = this.questionValueList.sort((a, b) => {
          return +a - +b
        })
      }
      this.validateForm();
    });

    /*
    if(this.questionList && this.questionList.length>0){
      this.questionList.forEach(val=>{
        this.reviewcommentlist['qtd'+val.id]='';
        this.reviewcommentlist['severity'+val.id]='';
        this.reviewcommentlist['revieweranswer'+val.id]='';
      })
    }
    */
    /*
    if(this.reviewcomments && this.reviewcomments.length>0){
      this.reviewcomments.forEach(val=>{
        this.reviewcommentlist['qtd'+val.id]=val.answer;
        this.reviewcommentlist['finding'+val.id]=val.finding;
        this.reviewcommentlist['severity'+val.id]=val.answer;
        this.questionfile[val.id] = {name:val['file']};

        this.reviewcommentlist['findingType'+val.id]=val.finding_type;
        this.reviewcommentlist['revieweranswer'+val.id]=val.revieweranswer;
        this.reviewcommentlist['reviewercomment'+val.id]=val.reviewercomment;
      })
    }
    */


  }
  reviewerCorrection:any=[];
  getReviewerAnswer(val,qid){
    let qindex = this.reviewerCorrection.indexOf(qid);
    if(val==2){
      if(qindex === -1){
        this.reviewerCorrection.push(qid);
      }
    }else{
      if(qindex > -1){
        this.reviewerCorrection.splice(qindex,1);
      }
    }
  }

  changeFindingComment(val:any,unit_id,qid){
    let qids = unit_id+'_'+qid;
    let qunitdata = this.questionList.find(v=>v.unit_id == unit_id);
    let qdata = qunitdata.questions.find(v=>v.id == qid);
    
    if(val==1){
      this.reviewcommentlist['finding_'+qids]= qdata.yes_comment;
    }else if(val==2){
      this.reviewcommentlist['finding_'+qids]= qdata.no_comment;
    }else{
      this.reviewcommentlist['finding_'+qids]='';
    }
  }

  formData:FormData = new FormData();
  questionfile = [];
  removeFile(stdqid){
    this.questionfile[stdqid] = '';
    this.formData.delete("questionfile["+stdqid+"]");
  }

  toggleGuidance(checklistid){
    let index = this.guidanceIncludeList.indexOf(checklistid);
    if (index > -1) {
      this.guidanceIncludeList.splice(index, 1);
    }else{
      this.guidanceIncludeList.push(checklistid);
    }
  }

  modalss:any;
  open(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  downloadFindingFile(filename,finding_id){
    this.FindingsCorrectiveActionService.downloadEvidenceFile({id:finding_id})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop();  
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
     
    });
  }


  fileChange(element,stdqid:string) {
    let files = element.target.files;
    
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.formData.append("questionfile["+stdqid+"]", files[0], files[0].name);
      //this.company_file = files[0].name;
      this.questionfile[stdqid] = {name:files[0].name};
      this.fileErrList[stdqid]=false;
    }else{
      this.fileErrList[stdqid]=true;
    }
    element.target.value = '';
   
  }

  formErr = {}
  errQuestion= {}
  answerErrList= [];
  recurringErrList= [];
  commentErrList= [];
  fileErrList= [];

  
  setInvalidField(id) {
    this.tabIndex = id;
    this.validateForm();
     

  }

  sayhi() {
    alert("hi");
  }

  validateForm(f?:NgForm) {
    let formerror = false
    
     
    console.log(this.reviewcommentlist)
    this.questionList.forEach(element => {
       let formques = {}
      element.questions.forEach(qval=>{
          
          if(qval.sub_topic_id == this.tabIndex || this.tabIndex == this.questionValueList[this.questionValueList.length - 1] || f == undefined) {
            if(!formques[qval.sub_topic_id])
            formques[qval.sub_topic_id] = {}
            
        formques[qval.sub_topic_id][qval.id] = false
          
        let qid = element.unit_id+'_'+qval.id;

        // let answer = eval("f.value.qtd_"+qid);
        // let findings = eval("f.value.finding_"+qid);
          
        let severity = this.reviewcommentlist["severity_"+qid]//eval("f.value.);

        let findingType = this.reviewcommentlist["findingType_"+qid]// eval("f.value."+qid);
        let revieweranswer = this.reviewcommentlist["revieweranswer_"+qid]//eval("f.value."+qid);
       
        let reviewercomment = this.reviewcommentlist["reviewercomment_"+qid]//eval("f.value."+qid);
      
                console.log(revieweranswer, reviewercomment, severity, findingType )
        //console.log(answer);
        // if(answer==null || answer ==''){
        //   f.controls["qtd_"+qid].markAsTouched();
        //   formerror=true;
        // }
        
        if(revieweranswer==null || revieweranswer ==''){
         // if(f.controls["revieweranswer_"+qid])
         // f.controls["revieweranswer_"+qid].markAsTouched();
          formerror=true;
            
          formques[qval.sub_topic_id][qval.id] = true;
        } 
        
        
        // if((findings==null || findings.trim() =='')){
        //   f.controls["finding_"+qid].markAsTouched();
        //   formerror=true;
        // }
        
        if( revieweranswer == 2){
          if(reviewercomment==null || reviewercomment ==''){
          //  f.controls["reviewercomment_"+qid].markAsTouched();
            formerror=true;
             
            formques[qval.sub_topic_id][qval.id] = true
          } 
          
        }

         
      //  if( answer == 2){
          //this.commentErrList[qid]=true;
          if(severity != undefined && (severity==null || severity =='')){
          //  f.controls["severity_"+qid].markAsTouched();
            formerror=true;
             
            formques[qval.sub_topic_id][qval.id]= true;
          }
          // if(findingType != undefined && (findingType==null || findingType =='')){
          // //  f.controls["findingType_"+qid].markAsTouched();
          // debugger
          //   formerror=true;
          //   formques[qval.sub_topic_id][qval.id] = true;
          // }
       // }

        //console.log(qval.file_required+'&&'+element.file_required +'&&'+ answer);
        // if((qval.file_required ==1 && answer != 3) && (this.questionfile[qid]===undefined || this.questionfile[qid]==null || this.questionfile[qid] == '')){
        //   this.fileErrList[qid]=true;
        //   formerror=true;
        //   console.log(formerror);
        // }else{
        //   var index = this.fileErrList.indexOf(qid);
        //   if (index == -1) {
        //     this.fileErrList[qid]=false;
        //   }
        // }
       }
      
      });
      console.log(formques)
      for(let i in formques) {
         let validCheck = []
        for(let sub in formques[i]){

     
            validCheck.push(formques[i][sub])
         
        } 
        this.errQuestion = formques
        this.formErr[i] = validCheck.includes(true);
      }
      console.log(this.formErr)
    });

    
  }

  onSubmit(f:NgForm,actiontype) {

     if((actiontype == 'submit'|| actiontype == 'reportcorrection') && this.tabIndex != this.questionValueList[this.questionValueList.length - 1]) {
       this.validateForm(f)
        
        let index = this.questionValueList.indexOf("" +this.tabIndex)
        this.tabIndex = this.questionValueList[index + 1]
        window.scroll(0,0);
  
      } else {
    
    let formerror = false;
   
    //console.log(formerror);
    //return false;

    this.validateForm(f);

    for(let key in this.formErr) {
      if(this.formErr[key])
        formerror=true
    }

   
    if (!formerror && (actiontype=="submit" || (actiontype=="reportcorrection" && f.value.reviewer_note!='' && f.value.reviewer_note!==undefined))) {
      let questions = [];
      let allquestions = [];
      let unitList = [];
      this.questionList.forEach(element => {
        unitList.push(element.unit_id);
        element.questions.forEach(qval=>{
          let qid = element.unit_id+'_'+qval.id;
          let unit_id = element.unit_id;
          
           let answer = this.reviewcommentlist['qtd_'+qid] ? this.reviewcommentlist['qtd_'+qid]: qval.answer;
        let findings = this.reviewcommentlist['finding_'+qid] ? this.reviewcommentlist['finding_'+qid]: qval.finding ;
        // let severity = eval("f.value.severity_"+qid) ? eval("f.value.severity_"+qid): qval.severity ;
          // let answer = eval("f.value.findingType_"+qid); //qval.answer;
          // let findings = eval("f.value.findingType_"+qid) //qval.finding;
          // let severity =eval("f.value.findingType_"+qid) // qval.severity;

           let severity = this.reviewcommentlist["severity_"+qid]//eval("f.value.);

        let findingType = this.reviewcommentlist["findingType_"+qid]// eval("f.value."+qid);
        let revieweranswer = this.reviewcommentlist["revieweranswer_"+qid]//eval("f.value."+qid);
       
        let reviewercomment = this.reviewcommentlist["reviewercomment_"+qid]

      //    let findingType = eval("f.value.findingType_"+qid);
       //   let revieweranswer = eval("f.value.revieweranswer_"+qid);
        //  let reviewercomment = eval("f.value.reviewercomment_"+qid);

          let filename = (this.questionfile[qid] !== undefined)?this.questionfile[qid].name:'';
          let qdata= {unit_id:unit_id,execution_checklist_id:qval.execution_checklist_id,reviewercomment:reviewercomment,revieweranswer:revieweranswer,findingType:findingType,question:qval.name,sub_topic_id:qval.sub_topic_id,question_id:qval.id,answer:answer,findings:findings,severity:severity,file:filename};
          /*if(questions[unit_id] == undefined){
            questions[unit_id] = [];
          }*/
           
          allquestions.push(qdata);
        });
      });
      unitList.forEach(element => {
        let quest = allquestions.filter(val=> val.unit_id == element);
        questions.push({unit_id:element, questions:quest});
      });
      
      //let user_id = this.ngForm.control.get("user_id").value;

      let stddata = {actiontype,questions,audit_id:this.audit_id,audit_plan_id:this.audit_plan_id,unit_id:this.unit_id,audit_plan_unit_id:this.audit_plan_unit_id,note:f.value.reviewer_note};

      //console.log(unit_review_comment);
      //return false;
      this.loading  = true;
      this.formData.append('formvalues',JSON.stringify(stddata));
     // console.log(stddata);
      
      this.auditExecution.saveReviewAuditAnswers(this.formData)
      .pipe(first())
      .subscribe(res => {
          
          if(res.status==1){
              this.success = {summary:res.message};
              
              this.buttonDisable = true;
              
              setTimeout(() => {
                this.router.navigateByUrl('/audit/view-audit-plan?id='+this.audit_id);
              }, this.errorSummary.redirectTime);
              
            }else if(res.status == 0){
              this.error = {summary:res.message};
            }else{
              this.error = {summary:res};
            }
            this.loading = false;
          
      },
      error => {
          this.error = {summary:error};
          this.loading = false;
      });

     
      
      
    } else {
      this.error = {summary:'Please fill all the mandatory fields (marked with *)'};
    }
    }
  }
  
}
