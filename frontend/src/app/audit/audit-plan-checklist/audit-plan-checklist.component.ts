import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditPlanService } from '@app/services/audit/audit-plan.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-audit-plan-checklist',
  templateUrl: './audit-plan-checklist.component.html',
  styleUrls: ['./audit-plan-checklist.component.scss']
})
export class AuditPlanChecklistComponent implements OnInit {

  questionList:any;
  guidanceIncludeList:Array<any> = [];
  reviewcommentlist=[];
  success:any='';
  error:any='';
  checklistForm : any = {};
  unitquestionList:any;
  units:Array<any>;
  panelOpenState = true;

  id:number;
  app_id:number;

  loading = false;
  buttonDisable = false;
  review_status = '';
  review_result_status = '';
  review_comments = '';
  
  answerArr:any;
  loadingInfo:any;
  reviewResultArr:Array<any>=[];
  reviewerstatus = [];
  applicableforms:any = [];
  apploaded:any=false;

  constructor(private activatedRoute:ActivatedRoute, private modalService: NgbModal,private router: Router,public auditPlanService:AuditPlanService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.app_id = this.activatedRoute.snapshot.queryParams.app_id;

    this.review_status = '';
    this.review_result_status = '';

    this.auditPlanService.getReviewQuestions({audit_id:this.id}).pipe(first())
    .subscribe(res => {
      this.questionList = res['data']['1'];
      this.unitquestionList = res['data']['2'];
      this.units = res['data']['units'];
      this.reviewResultArr = res['data']['reviewResult'];
      this.reviewerstatus = res['data']['reviewerstatus'];
      
      if(res['answerdata'] && res['answerdata']['auditanswer'].length>0){
        res['answerdata']['auditanswer'].forEach(element => {
          this.reviewcommentlist['qtd_comments'+element.question_id]=element.comment;
          this.reviewcommentlist['qtd'+element.question_id]=element.answer;
        });
      }else{
        this.questionList.forEach(element => {
          this.reviewcommentlist['qtd_comments'+element.id]='';
          this.reviewcommentlist['qtd'+element.id]='';
        });

        this.units.forEach(unitelement => {
          this.unitquestionList.forEach(element => {
          // console.log(unitelement.id+'_'+element.id);
            this.reviewcommentlist['unit_qtd_comments'+unitelement.id+'_'+element.id]='';
            this.reviewcommentlist['unit_qtd'+unitelement.id+'_'+element.id]='';
          });
        });

      }

      if(res['answerdata'] && res['answerdata']['auditunitanswer']){
        this.units.forEach(unitelement => {
          if(res['answerdata']['auditunitanswer'][unitelement.id]){
            res['answerdata']['auditunitanswer'][unitelement.id].forEach(element => {
              //this.reviewcommentlist['qtd_comments'+element.question_id]=element.comment;
              //this.reviewcommentlist['qtd'+element.question_id]=element.answer;
              this.reviewcommentlist['unit_qtd_comments'+unitelement.id+'_'+element.question_id]=element.comment;
              this.reviewcommentlist['unit_qtd'+unitelement.id+'_'+element.question_id]=element.answer;
            });
          }
        });
      }

    },
    error => {
        //this.error = {summary:error};
    });

    this.auditPlanService.getAuditReportDisplayStatus({app_id:this.app_id,audit_id:this.id}).pipe(first())
    .subscribe(res => {
      this.applicableforms = res;
      this.apploaded=true;
    },
    error => {
        this.error = error;
        this.loading = false;
    }); 
  }

  toggleGuidance(checklistid){
    let index = this.guidanceIncludeList.indexOf(checklistid);
    if (index > -1) {
      this.guidanceIncludeList.splice(index, 1);
    }else{
      this.guidanceIncludeList.push(checklistid);
    }
  }

  statusVal = 0;
  commentLabel = 'Comments';

 
  onSubmit(f:NgForm){

    this.questionList.forEach(element => {
      let answer = eval("f.value.qtd"+element.id);
      let comment = eval("f.value.qtd_comments"+element.id);
      
      f.controls["qtd"+element.id].markAsTouched();
      f.controls["qtd_comments"+element.id].markAsTouched();
    });

    this.units.forEach(unitelement => {
      this.unitquestionList.forEach(element => {

        let currid = unitelement.id+'_'+element.id;
        let answer = eval("f.value.unit_qtd"+currid);
        let comment = eval("f.value.unit_qtd_comments"+currid);
        f.controls["unit_qtd"+currid].markAsTouched();
        f.controls["unit_qtd_comments"+currid].markAsTouched();
      });
    });


    //f.controls["review_status"].markAsTouched();
    f.controls["review_result_status"].markAsTouched();
    f.controls["review_comments"].markAsTouched();

    
    if (f.valid) {
      let review_comment= [];
      let unit_review_comment= [];
      this.questionList.forEach(element => {
        let ans = {question:element.name,question_id:element.id,answer:eval("f.value.qtd"+element.id),comment:eval("f.value.qtd_comments"+element.id)};
        review_comment.push(ans);
      });

      this.units.forEach(unitelement => {
        this.unitquestionList.forEach(element => {
          let currid = unitelement.id+'_'+element.id;
          unit_review_comment.push({unit_id:unitelement.id,question:element.name,question_id:element.id,answer:eval("f.value.unit_qtd"+currid),comment:eval("f.value.unit_qtd_comments"+currid)});
        });
      });

      let reviewdata={
        audit_id:this.id,
        comment:f.value.review_comments,
        review_result_status:f.value.review_result_status,
        review_comment,
        unit_review_comment
      }
      //console.log(reviewdata);
      this.loading  = true;
      this.auditPlanService.addReviewchecklist(reviewdata)
      .pipe(first())
      .subscribe(res => {
            
          if(res.status==1){
              this.success = {summary:res.message};
              this.buttonDisable = true;
              setTimeout(() => {
                this.router.navigateByUrl('/audit/view-audit-plan?id='+this.id);
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
      this.error = {summary:this.errorSummary.errorSummaryText};
    }
  }

  //reviewerchecklist_status = true;
  report_status = true;
  generalinfo_status = false;
  checklist_status = false;
  supplierinfo_status = false;
  unittab:any = [];
  changeOfferTab(arg,unitid:any='')
  {
    //this.reviewerchecklist_status=false;
	this.report_status=false;
	this.generalinfo_status=false;
    this.supplierinfo_status=false;
    this.checklist_status=false;
    /*
    this.offerdata.units.forEach(x=>{
      this.unittab[x.id]=false;
    })
    */
    this.units.forEach(x => {
      this.unittab[x.id]=false;
    })
	
	if(arg=='audit_report'){
	  this.report_status=true;
	}else if(arg=='generalinfo'){
	  this.generalinfo_status=true;
	}else if(arg=='supplierinfo'){
      this.supplierinfo_status=true;
    }else if(arg=='checklist'){
      this.checklist_status=true;
    }else if(arg=='unit'){
      this.unittab[unitid]=true;
    }/*else if(arg=='reviewerchecklist'){
      this.reviewerchecklist_status =true;
    }*/
  }

  modalss:any;
  open(content) {
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {

    }, (reason) => {
     
    });
  }
}
