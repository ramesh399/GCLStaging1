import { Component, OnInit } from '@angular/core';
import { first,tap,map } from 'rxjs/operators';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl, Form } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditExecutionService } from '@app/services/audit/audit-execution.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import {saveAs} from 'file-saver';
import { FindingsCorrectiveActionService } from '@app/services/audit/findings-corrective-action.service';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { IndexedDBService } from '@app/indexeddbservices/indexed-db.service';
import {fromEvent, Observable, Subscription, interval} from 'rxjs';
import { takeWhile } from 'rxjs/operators';
import { AuthenticationService } from '@app/services';
import { ElementFinder } from 'protractor';
import { Console } from 'console';
import { MatSnackBar } from '@angular/material';
@Component({
  selector: 'app-audit-execution',
  templateUrl: './audit-execution.component.html',
  styleUrls: ['./audit-execution.component.scss']
})
export class AuditExecutionComponent implements OnInit {
  pagereloaded:any = 0;
  panelOpenState:any = false;
  questionIndex;
  count:number =0;
  autoSaveId;
  formCompleted = {};
  questionInvalid =  {};
  constructor(private activatedRoute:ActivatedRoute, private _snackBar: MatSnackBar, private router: Router,public auditExecution:AuditExecutionService,
    public errorSummary: ErrorSummaryService, private FindingsCorrectiveActionService:FindingsCorrectiveActionService,
    private modalService: NgbModal, private indexedDBService:IndexedDBService,public authservice:AuthenticationService) { 
      
      
      if(!window.location.hash) {
         
          window.location.href = window.location + '#loaded';
          window.location.reload();
      }else{
        this.pagereloaded = 1;
      }
      if(this.pagereloaded){
        // this.authservice.checkOnlineonload();
        // this.authservice.checkOnlines();
        this.indexedDBService.connectToDb().then(data=>{
        
        })
      }
      
      //window.addEventListener('offline',(e)=>{
        //console.log('event offline');
        //this.isOnline = 0;
        //this.dataToSync = 0;
      //});
      
      //window.addEventListener('online',(e)=>{
       // console.log('event online');
        //this.isOnline = 1;
        /*
        if(this.dataToSync == 0){
  
          this.indexedDBService
          .getChecklistAnswer(this.audit_plan_unit_id)
          .then(value=>{
            if(value){
              let subtopicarr = value.sub_topic_id.split(',').map(String);
              let cur_sub_topic_id = this.sub_topic_id.split(',').map(String);
              const intersection = subtopicarr.filter(el=> cur_sub_topic_id.includes(el));
              
              if(intersection.length > 0){
                this.dataToSync = 1;
              }
            }
          })
          .catch(()=>{ console.log('2')  })
          
        }
        */
      //});
  
      
      /*
      this.intervalID = window.setInterval((e)=>{
        let url = `https://ssl.gcl-intl.com/demo/backend/testonline.php`;
        let response:any = fetch(url);
    
        if (response.ok) { // if HTTP-status is 200-299
          // get the response body (the method explained below)
          //let json = await response.json();
          this.isOnline = 1;
        } else {
          //console.log("HTTP-Error: " + response.status);
          this.isOnline = 0;
        }
      }, 4500);
      */
  }


  

  
  intervalID:any;

  app_id:any;
  audit_plan_id:any;
  audit_id:any;
  unit_id:any;
  sub_topic_id:any;
  audit_plan_unit_id:any;
  checkListTabs:any =[];

  checklistForm : any = {};
  guidanceIncludeList:Array<any> = [];
  loading:any;
  success:any;
  error:any;
  buttonDisable:any;
  questionList = [];
  loadingInfo:any=[];
  reviewcommentlist=[];
  answerList:any = [];
  auditorAnswerList:any = [];
  saveIds:any = [];
  dataloaded = false;
  checklist_status=true;
  attendance_status=false; 
  sampling_status=false;
  interview_status=false; 
  client_information_status=false;
  environment_status=false; 
  living_wage_calc_status=false; 
  qbs_status=false; 
  chemical_list_status=false; 
  audit_ncn_report_status=false;
  report_status=false;
  applicableforms:any = [];

  audit_type:any;
  reportdetailsloaded=false;
  
  isOnline:any;
  dataToSync:number = 0;
  checkAnswerInInterval:any;
  stopInterval:any = 0;
  initloadedfromoffline:any = 0;
  initloadedfromOnline:any = 1;
  ngOnInit() {

    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.audit_plan_id = this.activatedRoute.snapshot.queryParams.audit_plan_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
    this.sub_topic_id = this.activatedRoute.snapshot.queryParams.subtopic;
    this.audit_plan_unit_id = this.activatedRoute.snapshot.queryParams.audit_plan_unit_id;
    this.app_id = this.activatedRoute.snapshot.queryParams.app_id;

    
    
    if(this.pagereloaded){
      
      this.authservice.currentUser.subscribe(authx => {
      this.authservice.userOnlineStatus.subscribe(x => {
        
        if(x === 1){ //Online
          
          this.initloadedfromOnline = 1;
          
            if(authx){

              //let user = this.authservice.getDecodeToken();
               //console.log(user);
               if(!this.initloadedfromoffline){
                this.getChecklistDetails();
               }else if(this.dataToSyncErrorMsg){
                this.getChecklistDetails();
               }
               this.getAuditReportDisplays();

            }else{
              this.router.navigate(['/login']);
              //console.log('fff');
            }
          
          if(this.dataToSyncErrorMsg && this.initloadedfromoffline){
            this.dataToSyncErrorMsg = 0;
            
          }

          
          if(this.dataToSync == 0){
            
            this.indexedDBService.connectToDb().then(data=>{
              this.indexedDBService
              .getChecklistAnswer(this.audit_plan_unit_id)
              .then(value=>{
                if(value){
                  let subtopicarr = value.sub_topic_id.split(',').map(String);
                  let cur_sub_topic_id = this.sub_topic_id.split(',').map(String);
                  const intersection = subtopicarr.filter(el=> cur_sub_topic_id.includes(el));
                  
                  if(intersection.length > 0){
                    this.dataToSync = 1;
                  }
                }
              })
              .catch(()=>{ console.log('2')  })
            })
            
          }
        }else if(x === 0){ // Offline
          
          //this.getAuditReportDisplays();
          if(!this.initloadedfromOnline){
            this.getChecklistDetails();
          }
          
          this.dataToSync = 0;
          this.initloadedfromoffline = 1;
        }else{
          
          this.getChecklistDetails();
          this.getAuditReportDisplays();
        }
      })
    });
      
      
      
      //this.getChecklistDetails();
      
    }
    
    /*
    addEventListener('offline',(e)=>{
      this.isOnline = 0;

    });
    addEventListener('online',(e)=>{
      this.isOnline = 1;
    });
    */
    

    /*
    Promise Example
    =================
    let formData = new FormData();
    let linksArr = [{url:`http://yii72.aescorp.in/yii19102301_gcl/ver1/web/site/getyear`,fdata:formData}, {url:`http://yii72.aescorp.in/yii19102301_gcl/ver1/web/site/getyear`,fdata:formData}];

    let promiseArr = linksArr.map(l => fetch(l.url,{
      method:'POST',
      headers:{
        'Authorization' : `Bearer `
      },
      body: l.fdata
    }).then(res => res.json()));
    
    Promise.all(promiseArr).then((values) => {
      console.log(values);
    });
     */
    /*
    Cursor to Get all Data
    ======================
    const request = indexedDB.open('gcl-db');
    request.onsuccess = (dbevent:any) => {
      let db = dbevent.target.result;
      const storerequest = db.transaction('audit_answers').objectStore('audit_answers')

      
      var request = storerequest.openCursor();
      request.onsuccess =
        function(evt) {
            let cursor = evt.target.result;
            if (cursor) {
                let user = cursor.value;
                //console.log(user);
               // console.log(cursor.key);
                cursor.continue();
            }
      }
        
    }
    */
    
 
    this.loadingInfo['questions'] = true;
   
    

    /*
    this.answerList = {1:'Yes',2:'No'};

    this.questionList = [
      {id:1,name:'test test',interpretation:'test guidance1',expected_evidance:'test evidance',answer_list:{1:'High',2:'Medium',3:'Low'},file_required:1,yes_comment:'Yes Comment',no_comment:'No Comment'},
      {id:2,name:'test test 1',interpretation:'test guidance2',expected_evidance:'test evidance 2',answer_list:{1:'High',2:'Critical',3:'Low'},file_required:0,yes_comment:'Yes Comment 2',no_comment:'No Comment 2'},
      {id:3,name:'test test 2',interpretation:'test guidance3',expected_evidance:'test evidance 3',answer_list:{1:'High',2:'Medium',3:'Low'},file_required:0,yes_comment:'Yes Comment 3',no_comment:'No Comment 3'},
      {id:4,name:'test test 3',interpretation:'test guidance4',expected_evidance:'test evidance 4',answer_list:{1:'High',2:'Critical',3:'Low'},file_required:1,yes_comment:'Yes Comment 4',no_comment:'No Comment 4'},
    ];
    */
    

    
    
    /*
    map((res)=>{
       
        
      }),
      */
    

    
    /*
    this.auditExecution.getReviewQuestions({audit_id:this.audit_id,audit_plan_id:this.audit_plan_id,unit_id:this.unit_id,sub_topic_id:this.sub_topic_id}).pipe(
      
      first()
    ).subscribe(res => {
      this.questionList = res['questionList'];
      this.answerList = res['answerList'];
      this.loadingInfo['questions'] = false;
     

      if(this.questionList && this.questionList.length>0){
        this.questionList.forEach(val=>{
          this.reviewcommentlist['qtd'+val.id]= val.answer?val.answer:'';
          this.reviewcommentlist['severity'+val.id]=val.severity?val.severity:'';
          this.reviewcommentlist['finding'+val.id]=val.finding?val.finding:'';
          if(val['file']){
            this.questionfile[val.id] = {name:val['file'],type:2};
          }
        })
      }
    });
    */

     /*
      if(this.auditorAnswerList && this.auditorAnswerList.length>0){
        this.questionList.forEach(val=>{

          this.reviewcommentlist['qtd'+val.id]=val.answer;
          this.reviewcommentlist['finding'+val.id]=val.finding;
          this.reviewcommentlist['severity'+val.id]=val.severity;
          this.reviewcommentlist['findingType'+val.id]='';
          this.reviewcommentlist['revieweranswer'+val.id]='';

          if(val['file']){
            this.questionfile[val.id] = {name:val['file']};
          }
          
        })
      }
      */
     
    


    
  
    
  }
   
  getAuditReportDisplays(){
    if(this.app_id){
      this.auditExecution.geAuditReportDisplayStatus({audit_id:this.audit_id,sub_topic_id:this.sub_topic_id,app_id:this.app_id,unit_id:this.unit_id}).pipe(first())
      .subscribe(res => {
        this.applicableforms = res;
        this.audit_type = res['audit_type'];
        this.loading = false;
        this.reportdetailsloaded = true;
      },
      error => {
          this.error = error;
          this.loading = false;
          this.reportdetailsloaded = true;
      }); 
    }
    
  }

  subtopicIDCheck(id) {
    this.questionIndex = id;
    this.count = 0;
  } 
 
  
  
  eventOnline(){
    console.log('sdf');
  }
  dataToSyncErrorMsg:any = 0;
  getChecklistDetails(){
    this.questionList = [];
    this.loadingInfo['questions'] = true;

    let getData = `audit_id=${this.audit_id}&audit_plan_id=${this.audit_plan_id}&unit_id=${this.unit_id}&sub_topic_id=${this.sub_topic_id}`;
    //this.auditExecution.getReviewQuestionsByGet({audit_id:this.audit_id,audit_plan_id:this.audit_plan_id,unit_id:this.unit_id,sub_topic_id:this.sub_topic_id}).pipe(
    this.auditExecution.getReviewQuestionsByGet(getData).pipe(
      first()
    ).subscribe(res => {
     
      this.dataToSyncErrorMsg = 0;
      this.questionList = res['questionList'];
      
      this.answerList = res['answerList'];
      
      this.loadingInfo['questions'] = false;

      this.dataloaded = true;

        let subtopicname = this.activatedRoute.snapshot.queryParams.subtopicname;
   this.checkListTabs = JSON.parse(subtopicname);
   console.log(this.questionList)
   this.checkListTabs.forEach(el => {
    el.name = el.name.replace("and", '&')
  }) 
   this.checkListTabs.forEach(element => {
          let listOfQues = this.questionList.filter(el => el.sub_topic_id === element.id);
   
    let ans = 0; 
   
    listOfQues.forEach(el => {
     
       if(el.answer != "" || el.finding !="" ) {
         ans++
         this.questionInvalid[el.id] = true
         if(el.answer != 3 &&  ((el.file_required == "1" && el.file =="" && el.answer == 1) 
         || (el.answer == 2 && (el.severity == null || el.severity == "")))) {
           this.questionInvalid[el.id] = false
           ans--
           
          } else if (el.answer == 3 && el.finding =="" ) {
           this.questionInvalid[el.id] = false
           
         }
       } 
       
       
    })

    this.formCompleted[element.id] = listOfQues.length === ans;
    });
    if( this.checkListTabs[0])
     this.questionIndex = this.checkListTabs[0].id;
      /*
      if(this.auditorAnswerList && this.auditorAnswerList.length>0){
        this.questionList.forEach(val=>{

          this.reviewcommentlist['qtd'+val.id]=val.answer;
          this.reviewcommentlist['finding'+val.id]=val.finding;
          this.reviewcommentlist['severity'+val.id]=val.severity;
          this.reviewcommentlist['findingType'+val.id]='';
          this.reviewcommentlist['revieweranswer'+val.id]='';

          if(val['file']){
            this.questionfile[val.id] = {name:val['file']};
          }
          
        })
      }
      */

      if(this.questionList && this.questionList.length>0){
        this.questionList.forEach(val=>{
          this.reviewcommentlist['qtd'+val.id]= val.answer?val.answer:'';
          this.reviewcommentlist['severity'+val.id]=val.severity?val.severity:'';
          this.reviewcommentlist['finding'+val.id]=val.finding?val.finding:'';
          if(val['file']){
            this.questionfile[val.id] = {name:val['file'],type:2};
          }
        })
      }




      //if(!navigator.onLine){
      //"unit_id":"526","audit_id":"175","audit_plan_id":"162","audit_plan_unit_id":"419"
      this.indexedDBService.connectToDb().then(ldbdata=>{
        this.indexedDBService
        .getChecklistAnswer(this.audit_plan_unit_id)
        .then(value=>{
          if(value && this.audit_plan_unit_id == value.audit_plan_unit_id && value.audit_id==this.audit_id && this.audit_plan_id==value.audit_plan_id){
            if(value && value.questions && value.questions.length >0){
              
              let subtopicarr = value.sub_topic_id.split(',').map(String);
              let cur_sub_topic_id = this.sub_topic_id.split(',').map(String);
              const intersection = subtopicarr.filter(el=> cur_sub_topic_id.includes(el));
              console.log(intersection);
              if(intersection.length > 0){
                this.dataToSync = 1;
              }
              
              value.questions.forEach(ans=>{
                
                let question = this.questionList.find(q=>q.id == ans.question_id);
                if(question!== undefined){
                  question.finding = ans.findings;
                  question.answer = ans.answer;
                  question.severity = ans.severity;

                  this.reviewcommentlist['qtd'+question.id]= question.answer?question.answer:'';
                  this.reviewcommentlist['severity'+question.id]=question.severity?question.severity:'';
                  this.reviewcommentlist['finding'+question.id]=question.finding?question.finding:'';

                  if(ans.file && ans.file!=''){
                    this.questionfile[question.id] = {name:ans.filename,file:ans.file,type:1};
                  }else{
                    this.questionfile[question.id] = {name:ans.filename,type:1};
                  }
                  
                }
                  
              })
            }
          }
         })
        .catch(console.log);
      });
      //}

      
    },
    err=>{
      this.dataloaded = true;
      this.loadingInfo['questions'] = false;
      this.dataToSyncErrorMsg = 1;
      //No checklist found in offline for this Unit and Sub Topic. If you need this checklist in offline, you should select the Sub Topic and load the checklist when you are in online.
    });
  }
  changeFindingComment(val:any,qid){
    let qdata = this.questionList.find(v=>v.id == qid);
    
    if(val==1){
      this.reviewcommentlist['finding'+qid]= qdata.yes_comment;
    }else if(val==2){
      this.reviewcommentlist['finding'+qid]= qdata.no_comment;
    }else{
      this.reviewcommentlist['finding'+qid]='';
    }

    if(this.reviewcommentlist['qtd'+qid] == '2' && this.reviewcommentlist['severity'+qid] == "") {
        this.questionInvalid[qid] = false;
        return false;
      }
  }

  formData:FormData = new FormData();
  questionfile = [];
  currentQuestion = ""
  removeFile(stdqid, ans){
    this.questionfile[stdqid] = '';
    
    if(ans != '3') {
      this.questionInvalid[stdqid] = false;
      this.formCompleted[this.questionIndex] = false;
    }
    this.formData.delete("questionfile["+stdqid+"]");
    this.fileErrList[stdqid]=false;
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
    //console.log(filename+','+finding_id);
    this.FindingsCorrectiveActionService.downloadEvidenceFile({id:finding_id})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
     
    });
  }

  fileChange(element,stdqid:string, f?:NgForm) {
    let files = element.target.files;
     
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
		let file = files[0];
		let reader = new FileReader();
		reader.readAsDataURL(file);
		
		reader.onloadend = ()=>{
     		const bits:any = reader.result; // readyState will be 2
			
			/*
			let ob = {
				name:file.name,
				data:bits
			};
			*/			
			
      //this.formData.append("questionfile["+stdqid+"]", bits, files[0].name);
      
      this.questionfile[stdqid] = {name:file.name,file:bits,type:1};
      this.onBlur(f, stdqid);
		};	
				
		//this.company_file = files[0].name;		
    this.fileErrList[stdqid]=false;
   
    }else{
      this.fileErrList[stdqid]=true;
     
    }
    element.target.value = '';
    
  
  }
  answerErrList= [];
  recurringErrList= [];
  commentErrList= [];
  fileErrList= [];
  checkTabs () {
   let  err = false  
    for(let key in this.formCompleted) {
      if(!this.formCompleted[key]) {
          err = true
      }
    }
    if(err) {
          this._snackBar.open('Please fill all the mandatory fields (marked with *)', 'close', { duration: 2500 ,  panelClass: ['notif-warn']});
    } else {
        
        this._snackBar.open("All Questions Saved Successfully", 'close', { duration: 2500 ,  panelClass: ['notif-success']});
    


    }
  }
  onSubmit(f:NgForm, saveId?, submitType?) {
     
    //console.log(this.authservice.authenticateIsOnline);
    //this.review_rfalse; 
    ///this.status_comment_error = false;
    if(submitType) {
      this.checkTabs()
    } else {
      let formerror = false;
      //console.log(f.valid); return false;
      //let stddetails = this.questionList.find(x=>x.id==standard_id);
      this.questionList.forEach(element => {
        if(element.sub_topic_id == this.questionIndex && (submitType || this.currentQuestion == element.id )) {
           
  
  
        let qid = element.id;
  
        let answer = eval("f.value.qtd"+qid);
        let findings = eval("f.value.finding"+qid);
        let severity = eval("f.value.severity"+qid);
       // let qtd_validuntil = eval("f.value.qtd_validuntil"+qid);
  
        //console.log(answer);
        
        if(answer==null || answer ==''){
          f.controls["qtd"+qid].markAsTouched();
          //this.answerErrList
          //this.answerErrList[qid]=true;
          formerror=true;
        }
        if(answer != 3 && (findings==null || findings.trim() =='')){
          f.controls["finding"+qid].markAsTouched();
          formerror=true;
        }
         
        if( answer == 2){
          //this.commentErrList[qid]=true;
          
          if((severity==null || severity =='')){
            f.controls["severity"+qid].markAsTouched();
            formerror=true;
          }
        }
  
        
        if(answer !=3 && answer != 2 && element.file_required ==1 && (this.questionfile[qid]===undefined || this.questionfile[qid]==null || this.questionfile[qid] == '')){
          this.fileErrList[qid]=true;
          formerror=true;
        }else{
          var index = this.fileErrList.indexOf(qid);
          if (index == -1) {
            this.fileErrList[qid]=false;
          }
        }
      }
      });
  
  
      if (submitType) {
        this.checkListTabs.forEach(element => {
          if(!this.formCompleted[element.id]) {
            formerror = true;
          }
        });
      }
      if (!formerror && saveId != 'fromButton') {
        let qid ;
        let questions = [];
        let question_ids = [];
        this.questionList.forEach(element => {
          if(element.sub_topic_id == this.questionIndex) {
            if(saveId == element.id) {
          qid = element.id;
          let answer = eval("f.value.qtd"+qid);
          let findings = eval("f.value.finding"+qid);
          let severity = eval("f.value.severity"+qid);
  
          let filename = (this.questionfile[qid] !== undefined)?this.questionfile[qid].name:'';
          let file = (this.questionfile[qid] !== undefined)?this.questionfile[qid].file:'';
          let qdata= {sub_topic_id:element.sub_topic_id,question:element.name,question_id:qid,answer:answer,findings:findings,severity:severity,filename:filename,file:file};
          questions.push(qdata);
          question_ids.push(qid);
            }
          }
        });
        
        //let user_id = this.ngForm.control.get("user_id").value;
  
        let stddata = {questions,unit_id:this.unit_id,audit_id:this.audit_id,audit_plan_id:this.audit_plan_id,audit_plan_unit_id:this.audit_plan_unit_id,sub_topic_id:this.sub_topic_id};
     
        //console.log(unit_review_comment);
        //return false;
        this.loading  = true;
         this.saveIds.push(qid);
        this.formData = new FormData()
        this.formData.append('formvalues',JSON.stringify(stddata));
        // console.log(stddata);
        this.buttonDisable = true;
  
        //if(!navigator.onLine){
        if(!this.authservice.authenticateIsOnline){
          
          this.saveDataOffline(questions,stddata);
        }else{
          this.auditExecution.saveAuditAnswers(this.formData)
          .pipe(first())
          .subscribe(res => {
            if(res){
              if(res.status==1){
   
                this._snackBar.open(res.message, 'close', { duration: 2500 ,  panelClass: ['notif-success']});
                this.questionInvalid[qid] = true;
                let listOfQues = this.questionList.filter(el => el.sub_topic_id === this.questionIndex);
                let completedQue = []
                listOfQues.forEach(el => {
                  completedQue.push(this.questionInvalid[el.id])
                })
                this.formCompleted[this.questionIndex] = completedQue.filter(el => !el).length === 0
                this.saveIds.splice(this.saveIds.indexOf(qid), 1);
               
                this.indexedDBService
                .getChecklistAnswer(this.audit_plan_unit_id)
                .then(indexanswers=>{
                  if(indexanswers){
                    let removedquestions = this.indexedDBService.deleteChecklistAnswerQuestions(indexanswers,question_ids);
                    if(removedquestions.sub_topic_id =='' || removedquestions.questions.length<=0){
                      this.indexedDBService.deleteChecklistAnswer(this.audit_plan_unit_id).then(d=>{});
                    }else{
                      this.indexedDBService.addChecklistAnswer(removedquestions, 'checklist_answer_'+this.audit_plan_unit_id)
                      .then(r=>{});
                    }
                  }
                  
                  
                })
                 
                
                this.indexedDBService
                .deleteUpdateError(this.audit_plan_unit_id)
                .then(val=>{});
                this.dataToSync = 0;
                
                setTimeout(() => {
                  // this.getChecklistDetails();
                  this.buttonDisable = false;
                  //this.router.navigateByUrl('/audit/view-audit-plan?id='+this.audit_id);
                }, this.errorSummary.redirectTime);
                
                
              }else if(res.status == 0){
                this.buttonDisable = false;
                this.error = {summary:res.message};
              }else{
                this.buttonDisable = false;
                this.error = {summary:res};
              }
            }else{
              this.buttonDisable = false;
              this.loading = false;
            }
            // this.formCompleted[this.questionIndex] = true;
            
              this.loading = false;
            
          },
          error => {
          this._snackBar.open('Error occured... Please try again', 'close', { duration: 2500 ,  panelClass: ['notif-warn']});
            
            //if(!navigator.onLine){
           // this.saveDataOffline(questions,stddata);
            //}
            //this.buttonDisable = false;
            this.loading = false;
          });
        }
        
       
        
      }
      
      if(saveId === 'fromButton') {
        let index = this.checkListTabs.indexOf(this.checkListTabs.filter(el => el.id === this.questionIndex)[0])
        if(this.checkListTabs[index + 1])
        this.questionIndex = this.checkListTabs[index + 1].id;
        if(!formerror)  
        window.scroll(0,0);
        
       // this.auditExecution.validationResult = this.formCompleted
      }
    }
  }

  onBlur(f:NgForm, qid) {

    // if( (this.reviewcommentlist['finding'+qid] && this.reviewcommentlist['qtd'+qid]) || this.fileErrList[qid] == false ) {
    //   this.autoSaveId = qid;
    //   this.onSubmit(f, qid);
    // }
       
   this.currentQuestion = qid
    let temp = this.questionList.find((element) => {return element.id == qid});
     
    if( (this.reviewcommentlist['finding'+qid] && this.reviewcommentlist['finding'+qid].trim() != ""
        && this.reviewcommentlist['qtd'+qid] != "") 
        || this.fileErrList[qid] == false || ( f.value["qtd" + qid] == "3" && this.reviewcommentlist['finding'+qid].trim() == "" ) ) {
          if(this.reviewcommentlist['qtd'+qid] == '2' && this.reviewcommentlist['severity'+qid] == "" 
          || f.value["qtd" + qid] == "3" && this.reviewcommentlist['finding'+qid].trim() == "" ) {
            this.questionInvalid[qid] = false;
           this.formCompleted[this.questionIndex] = false;

            return false; 
          }
           
          if(( temp.file_required == 1 && (this.questionfile[qid] || f.value["qtd" + qid] == "2")) || (f.value["qtd" + qid] == "3")) {
            this.autoSaveId = qid;
            this.onSubmit(f, qid, false);
          } else if(temp.file_required == 0) {
            this.autoSaveId = qid;
            this.onSubmit(f, qid, false);
          } else {
            return false;
          }
      
    }  
     
    if( f.value["qtd" + qid] != "3"  && ( f.value['qtd' + qid] == "" 
        || f.value["finding" + qid].trim() == ""
        || this.fileErrList[qid]
        || (f.value['qtd' + qid] == 2 && f.value["severity" + qid] == ""))) {
           
      this.questionInvalid[qid] = false;
      this.formCompleted[this.questionIndex] = false;
    }  
    
  }
  saveDataOffline(questions:any,stddata:any){
    this.indexedDBService
    .getChecklistAnswer(this.audit_plan_unit_id)
    .then(indexanswers=>{
      if(indexanswers && indexanswers.questions.length>0){
        let subtopicarr = this.sub_topic_id.split(',');
        let indexdbsubtopicarr = indexanswers.sub_topic_id.split(',');
        if(subtopicarr && subtopicarr.length>0){
          subtopicarr.forEach(subtopicid=>{
            //let subindex = indexdbsubtopicarr.findIndex(x=>x==indexdbsubtopicarr.find(f=>f==x));
            let subindex = indexdbsubtopicarr.findIndex(x=>x==subtopicid);
            //console.log(subindex);
            if(subindex !== -1){
              indexanswers.questions = indexanswers.questions.filter(qf=>qf.sub_topic_id!=subtopicid);
            }else{
              indexanswers.sub_topic_id = indexanswers.sub_topic_id+','+subtopicid;
            }
            let submittedanswers = questions.filter(q=>q.sub_topic_id==subtopicid);
            //console.log(submittedanswers);
            //indexanswers.questions.push(submittedanswers);
            indexanswers.questions = [...indexanswers.questions,...submittedanswers];
          })
        }
        //console.log(indexanswers);
        this.indexedDBService.addChecklistAnswer(indexanswers, 'checklist_answer_'+this.audit_plan_unit_id)
            .then(r=>{
              let msg:string = 'Your data is stored in Local Database and please try again to save the data.';
              if(!this.authservice.authenticateIsOnline){
                msg = "You are offline! Your data is stored in Local Database and when you come back to online, please click submit to save the data.";
              }

              this.success = {summary:msg};
              this.buttonDisable = false;
              this.loading = false;
            });
      }else{
        this.indexedDBService.addChecklistAnswer(stddata, 'checklist_answer_'+this.audit_plan_unit_id)
            .then(r=>{
              let msg:string = 'Your data is stored in Local Database and please try again to save the data.';
              if(!this.authservice.authenticateIsOnline){
                msg = "You are offline! Your data is stored in Local Database and when you come back to online, please click submit to save the data.";
              }
              this.success = {summary:msg};
              this.buttonDisable = false;
              this.loading = false;
            });
      }
    }).catch(m=>{ 
      this.buttonDisable = false;
      this.loading = false; 
    });
    
    
  }
  //test codes
  /*
  this.indexedDBService
  .addChecklistAnswer(stddata, 'checklist_answer_'+this.audit_plan_unit_id)
  .catch(console.log);

  */
  /*
  this.indexedDBService
  .addChecklistAnswer(stddata, 'checklist_answer_'+this.audit_plan_unit_id)
  .then(this.backgroundSync)
  .catch(console.log);
    */
  //deleteChecklistAnswer
  /*
  this.indexedDBService
  .deleteChecklistAnswer(this.audit_plan_unit_id,question_ids)
  .then(val=>{});
  */
  backgroundSync(){
    /*
    navigator.serviceWorker.ready
    .then((SwRegistration) => SwRegistration.sync.register('post-audit-execution-data'))
    .catch(console.log);
    */
  }

  changeAuditExecutionTab(arg)
  {
    this.checklist_status=false;
    this.report_status=false;
	  this.attendance_status=false; 
	  this.sampling_status=false;
	  this.interview_status=false; 
	  this.client_information_status=false;
	  this.environment_status=false; 
	  this.living_wage_calc_status=false; 
	  this.qbs_status=false; 
	  this.chemical_list_status=false; 
	  this.audit_ncn_report_status=false;
	  this.success = '';
	  if(arg=='checklist'){
		   this.checklist_status=true;
	  }else if(arg=='attendance'){
      this.attendance_status=true;
    }else if(arg=='sampling'){
      this.sampling_status=true;
    }else if(arg=='interview'){
      this.interview_status=true;
    }else if(arg=='client_information'){
      this.client_information_status=true;
    }else if(arg=='environment'){
      this.environment_status=true;
    }else if(arg=='living_wage_calc'){
      this.living_wage_calc_status=true;
    }else if(arg=='qbs'){
      this.qbs_status=true;
    }else if(arg=='chemical_list'){
      this.chemical_list_status=true;
    }else if(arg=='audit_ncn_report'){
      this.audit_ncn_report_status=true;
    }else if(arg=='audit_report'){
      this.report_status=true;
    }			
  }
  
  ngOnDestroy() {
    //console.log('ngOnDestroy: cleaning up...');
    this.authservice.deleteInterval();
  }
}
 /*
  this.indexedDBService
  .getChecklistAnswer(this.audit_plan_unit_id)
  .then(value=>{
    if(value){
      //this.checkAnswerInInterval = interval(5000);
      //this.checkAnswerInInterval = interval(1000);
      //console.log('s2'+this.stopInterval);
      this.checkAnswerInInterval = interval(20000);
      this.checkAnswerInInterval
        .pipe(takeWhile((ic) => this.stopInterval < 1 ))
        .subscribe((i)=>{
          
          this.indexedDBService
          .getChecklistAnswer(this.audit_plan_unit_id)
          .then(value=>{
            if(value){
              let subtopicarr = value.sub_topic_id.split(',').map(String);
              let cur_sub_topic_id = this.sub_topic_id.split(',').map(String);
              const intersection = subtopicarr.filter(el=> cur_sub_topic_id.includes(el));
              
              if(intersection.length > 0){
                this.dataToSync = 1;
              }
              
              
                //this.indexedDBService
                // .getUpdateError(this.audit_plan_unit_id)
                //.then(errvalue=>{
                //  if(errvalue && errvalue ==1){
                ///    this.dataToSync = 1;
                  //   this.stopInterval = 1;
                //   }
                // })
                //.catch(()=>{  });
              

            }
          })
          .catch(()=>{ this.stopInterval = 1;  })
          
          
        });




    }
  });
  */
  /*
    this.indexedDBService.keysData().then(allkeydata=>{
      console.log(allkeydata);  
    })
    */
    
    /*
    this.indexedDBService
    .getUpdateError(this.audit_plan_unit_id)
    .then(value=>{
      if(value && value ==1){
        this.dataToSync = 1;
        
      }
    })
    .catch(console.log);
    */