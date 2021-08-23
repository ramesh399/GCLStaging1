import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { CertificateReviewerService } from '@app/services/certification/certificate-reviewer.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-certification-reviewer-checklist',
  templateUrl: './certification-reviewer-checklist.component.html',
  styleUrls: ['./certification-reviewer-checklist.component.scss']
})
export class CertificationReviewerChecklistComponent implements OnInit {

  questionList:any;
  guidanceIncludeList:Array<any> = [];
  reviewcommentlist=[];
  success:any='';
  error:any='';
  checklistForm : any = {};
  reviewchecklists= [];
  risklist:any=[];
  panelOpenState = true;

  reviewcomments=[];
  reviewerstatus = [];
  checklist_status = '';
  checklist_comments:any='';
  commentLabel = 'Comments';

  id:number;
  loading = false;
  buttonDisable = false;
  review_answers = '';

  answerArr:any;
  loadingInfo:any;
  audit_plan_id:number;
  certificate_id:number;
  product_addition_id:number;
  audit_id:number;
  modalss:any;
  closeResult: string;
  modalOptions:NgbModalOptions;

  constructor(private activatedRoute:ActivatedRoute,private modalService: NgbModal,private router: Router,public certificateReviewerService:CertificateReviewerService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
    this.loading = true;
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.audit_plan_id = this.activatedRoute.snapshot.queryParams.audit_plan_id;
    this.certificate_id = this.activatedRoute.snapshot.queryParams.certificate_id;
    this.product_addition_id = this.activatedRoute.snapshot.queryParams.product_addition_id;
    this.review_answers = '';

    this.certificateReviewerService.getReviewQuestions({audit_id:this.audit_id,audit_plan_id:this.audit_plan_id,certificate_id:this.certificate_id,product_addition_id:this.product_addition_id}).pipe(first())
    .subscribe(res => {
        this.questionList = res.data;  
        this.risklist = res.risklist;
        this.loading = false;   
    },
    error => {
      this.error = error;
      this.loading = false;
    });
  }


  reviewerCorrection:any=[];
  getReviewerAnswer(val,qid){
    let qindex = this.reviewerCorrection.indexOf(qid);
    if(val==2 || val==10){
      if(qindex === -1){
        this.reviewerCorrection.push(qid);
      }
    }else{
      if(qindex > -1){
        this.reviewerCorrection.splice(qindex,1);
      }
    }
  }

  toggleGuidance(checklistid){
    let index = this.guidanceIncludeList.indexOf(checklistid);
    if (index > -1) {
      this.guidanceIncludeList.splice(index, 1);
    }else{
      this.guidanceIncludeList.push(checklistid);
    }
  }

  alertInfoMessage:any;
  alertSuccessMessage:any;
  alertErrorMessage:any;
  onSubmit(f:NgForm,actiontype='',content){

    

    this.questionList.forEach(element => {
        let answer = eval("f.value.qtd"+element.id);
        let comment = eval("f.value.qtd_comments"+element.id);
        
        f.controls["qtd"+element.id].markAsTouched();
        f.controls["qtd_comments"+element.id].markAsTouched();
        
    });

    f.controls["checklist_status"].markAsTouched();
    f.controls["checklist_comments"].markAsTouched();

    let risk_value =  f.value.checklist_status;
    let risk_comments =  f.value.checklist_comments;

    if (f.valid) 
    {
      if(actiontype=='decline'){
        this.alertInfoMessage = 'Are you sure, do you want to decline the certificate ?';
      }else{
        this.alertInfoMessage = 'Are you sure, do you want to submit the checklist for certification ?';
      }
      

      this.modalss = this.modalService.open(content, this.modalOptions);
        //this.modalService.open(content, this.modalOptions).result.then((result) => {
      
      this.modalss.result.then((result) => {	
        //this.closeResult = `Closed with: ${result}`;	 
        let review_comment= [];
        this.questionList.forEach(element => {
          let ans = {question:element.name,question_id:element.id,answer:eval("f.value.qtd"+element.id),comment:eval("f.value.qtd_comments"+element.id)};
          review_comment.push(ans);
        });

        let reviewdata={
          audit_id:this.audit_id,  
          audit_plan_id:this.audit_plan_id,
          certificate_id:this.certificate_id,
          review_answers:review_comment,
          checklist_risk:risk_value,
          checklist_comment:risk_comments,
          actiontype:actiontype
        }
        
        this.loading  = true;
        this.certificateReviewerService.addReviewchecklist(reviewdata).pipe(first())
        .subscribe(res => {
            if(res.status==1){
              this.success = {summary:res.message};
              this.buttonDisable = true;
              setTimeout(() => {
                this.router.navigateByUrl('/certification/view-audit-plan?id='+this.audit_id+'&certificate_id='+this.certificate_id);
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
      }, (reason) => {
      
      });
      
    }
  }

}
