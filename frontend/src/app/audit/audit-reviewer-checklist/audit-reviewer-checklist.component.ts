import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditReviewerService } from '@app/services/audit/audit-reviewer.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-audit-reviewer-checklist',
  templateUrl: './audit-reviewer-checklist.component.html',
  styleUrls: ['./audit-reviewer-checklist.component.scss']
})
export class AuditReviewerChecklistComponent implements OnInit {

  questionList:any;
  guidanceIncludeList:Array<any> = [];
  reviewcommentlist=[];
  success:any='';
  error:any='';
  checklistForm : any = {};
  reviewchecklists= [];
  panelOpenState = true;

  id:number;
  loading = false;
  buttonDisable = false;
  review_answers = '';

  answerArr:any;
  loadingInfo:any;
  audit_plan_id:number;
  audit_id:number;
  constructor(private activatedRoute:ActivatedRoute,private router: Router,public auditReviewerService:AuditReviewerService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
    this.loading = true;
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.audit_plan_id = this.activatedRoute.snapshot.queryParams.audit_plan_id;
    this.review_answers = '';

    this.auditReviewerService.getReviewQuestions().pipe(first())
    .subscribe(res => {
        this.questionList = res.data;  
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

  toggleGuidance(checklistid){
    let index = this.guidanceIncludeList.indexOf(checklistid);
    if (index > -1) {
      this.guidanceIncludeList.splice(index, 1);
    }else{
      this.guidanceIncludeList.push(checklistid);
    }
  }
  onSubmit(f:NgForm,actiontype=''){

    this.questionList.forEach(element => {
        let answer = eval("f.value.qtd"+element.id);
        let comment = eval("f.value.qtd_comments"+element.id);
        
        f.controls["qtd"+element.id].markAsTouched();
        f.controls["qtd_comments"+element.id].markAsTouched();
    });

    if (f.valid) 
    {
      let review_comment= [];
      this.questionList.forEach(element => {
        let ans = {question:element.name,question_id:element.id,answer:eval("f.value.qtd"+element.id),comment:eval("f.value.qtd_comments"+element.id)};
        review_comment.push(ans);
      });

      let reviewdata={
        audit_plan_id:this.audit_plan_id,
        review_answers:review_comment,
        actiontype:actiontype
      }

      this.loading  = true;
      this.auditReviewerService.addReviewchecklist(reviewdata).pipe(first())
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
    }
  }

}
