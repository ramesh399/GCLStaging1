import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { ReviewchecklistService } from '@app/services/application/reviewchecklist.service';
import {Reviewchecklist} from '@app/models/application/reviewchecklist';
import { first,tap,map } from 'rxjs/operators';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { Application } from '@app/models/application/application';

@Component({
  selector: 'app-review-checklist',
  templateUrl: './reviewchecklist.component.html',
  styleUrls: ['./reviewchecklist.component.scss']
})
export class ReviewchecklistComponent implements OnInit {
	 
  reviewchecklists:Reviewchecklist[];
  unitReviewchecklists:Reviewchecklist[];
  riskArr:Array<any>=[];
  reviewResultArr:Array<any>=[];

  units:Array<any>;
  panelOpenState = true;
  checklistForm : any = {};
  isSubmitted=false;
  
  id:number;
  loading = false;
  buttonDisable = false;
  success:any;
  error:any;
  
  guidanceIncludeList:Array<any> = [];

  
  commentLabel = 'Comments';
  review_comments:any;
  //review_status_error = false;
  //myArrayList = [];
  //myArrayList2 = [];
  //unitArrayList = [];
  //unitArrayList2 = [];
  //review_result_status_error = false;
  //status_comment_error = false;
  
  applicationdata:Application;

  constructor(private fb:FormBuilder,
    private reviewchecklistservice: ReviewchecklistService,
    private activatedRoute:ActivatedRoute,
    private router:Router,
    private errorSummary: ErrorSummaryService,
	private applicationDetail:ApplicationDetailService) 
  { }
  
  reviewcomments=[];
  reviewunitcomments=[];
  reviewcommentlist=[];
  reviewerstatus = [];
  review_status = '';
  review_result_status = '';
  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.review_status = '';
    this.review_result_status = '';
	
	this.applicationDetail.getApplication(this.id).pipe(first())
      .subscribe(res => {
        this.applicationdata = res;      
    },
    error => {
      this.error = error;
      this.loading = false;
    });
	
    this.reviewchecklistservice.getReviewchecklist().pipe(
      map((res)=>{

        this.reviewchecklistservice.getReviewerchecklistEntries(this.id).pipe(first()).subscribe(list=>{
          if(list['applicationcomment']){
            this.reviewcomments = list['applicationcomment'];
          }else{
            let reviewchecklists = res['checklists'].filter(val=>val.category==1);
            reviewchecklists.forEach(val => {
              this.reviewcommentlist['qtd_comments'+val.id]='';
              this.reviewcommentlist['qtd'+val.id]='';
            });
          }
          if(list['applicationunitcomment']){
            this.reviewunitcomments = list['applicationunitcomment'];
          }else{
            if(this.units){

              let reviewunitchecklists = this.unitReviewchecklists = res['checklists'].filter(val=>val.category==2);
              this.units.forEach(vals=>{
                reviewunitchecklists.forEach(val => {
                  this.reviewcommentlist['unit_qtd_comments'+vals.id+'_'+val.id]='';
                  this.reviewcommentlist['unit_qtd'+vals.id+'_'+val.id]='';
                });
              });
            }
          }
          //console.log(this.reviewcomments);
          this.reviewcomments.forEach(val => {
            this.reviewcommentlist['qtd_comments'+val.question_id]=val.comment;
            this.reviewcommentlist['qtd'+val.question_id]=val.answer;
          });

          this.reviewunitcomments.forEach(val => {
            this.reviewcommentlist['unit_qtd_comments'+val.unit_id+'_'+val.question_id]=val.comment;
            this.reviewcommentlist['unit_qtd'+val.unit_id+'_'+val.question_id]=val.answer;
          });


          this.reviewchecklists = res['checklists'].filter(val=>val.category==1);
          this.unitReviewchecklists = res['checklists'].filter(val=>val.category==2);
          this.riskArr = res['risklists'];
          this.reviewResultArr = res['reviewResult'];
        })
        
      }),
      first()
    ).subscribe(res => {
      //this.reviewcommentlist = {qtd_comments1: 'asdfasdfasdf'};
      
      
      //this.checklistForm['qtd_comments1'] = '2asdfasdfasdf';

      //this.qtd[1
      /*this.enquiryForm.patchValue({
        company_name:res.company_name,
        company_address:res.address,
        zipcode:res.zipcode
      });
      */
    });
    this.reviewchecklistservice.getApplicationDetails(this.id).subscribe(res => {
      this.units = res.units;
      this.reviewerstatus = res.reviewerstatus;
      //list.sort((a, b) => (a.color > b.color) ? 1 : -1)
      //let keys = Object.keys(res.reviewerstatus);
      //keys.sort(function(a, b) { return obj[a] < obj[b] });

    }); 

        
  }
  statusVal = 0;
  fnCommentLabel(statusVal:number){
    if(statusVal == 2){
      this.commentLabel = 'Reason for rejection of application';
    }else if(statusVal == 3){
      this.commentLabel = 'Further Informations Required are';
    }else{
      this.commentLabel = 'Comments';
    }
    this.statusVal =statusVal;
  }
  criticalcount=0;
  incCritical(stvalue){
    //console.log(stvalue);
    if(stvalue ==6){
      this.criticalcount = this.criticalcount+1;
    }
  }
  filterReviewResultArr(){
    if(this.statusVal !== 0){
      if(this.statusVal==1){
        return this.reviewResultArr.filter(x=>x.id !=6);
      }else if(this.statusVal==2){
        return this.reviewResultArr.filter(x=>x.id ==6);
      }
    }
    return this.reviewResultArr;
    
  }
  //reviewResultArr
  
  toggleGuidance(checklistid){
    let index = this.guidanceIncludeList.indexOf(checklistid);
    if (index > -1) {
      this.guidanceIncludeList.splice(index, 1);
    }else{
      this.guidanceIncludeList.push(checklistid);
    }
  }
  onSubmit(f:NgForm) {
    //this.review_result_status_error = false;
    //this.status_comment_error = false;
    

    this.reviewchecklists.forEach(element => {
      let answer = eval("f.value.qtd"+element.id);
      let comment = eval("f.value.qtd_comments"+element.id);
      
      f.controls["qtd"+element.id].markAsTouched();
      f.controls["qtd_comments"+element.id].markAsTouched();
      /*if(answer ==undefined || answer==''){
        //this.myArrayList.push(element.id);
      }else if(answer!=1 && (comment ==undefined || comment.trim()=='')){
        //this.myArrayList2.push(element.id);
        

        //this.myArrayList = this.myArrayList.filter(item => item !== element.id);
        
      }else{

        //this.myArrayList = this.myArrayList.filter(item => item !== element.id);
        
        //this.myArrayList2 = this.myArrayList2.filter(item => item !== element.id);
        
      }
      */


    });

    this.units.forEach(unitelement => {
      this.unitReviewchecklists.forEach(element => {

        let currid = unitelement.id+'_'+element.id;
        let answer = eval("f.value.unit_qtd"+currid);
        let comment = eval("f.value.unit_qtd_comments"+currid);
        f.controls["unit_qtd"+currid].markAsTouched();
        f.controls["unit_qtd_comments"+currid].markAsTouched();

        /*if(answer ==undefined || answer==''){
          //this.unitArrayList.push(currid);
        }else if(answer!=1 && (comment ==undefined || comment.trim() =='')){
          //this.unitArrayList2.push(currid);
          

         // this.unitArrayList = this.unitArrayList.filter(item => item !== currid);
          
        }else{

          //this.unitArrayList = this.unitArrayList.filter(item => item !== currid);
          
          //this.unitArrayList2 = this.unitArrayList2.filter(item => item !== currid);
          
        }
        */
      });
    });


    f.controls["review_status"].markAsTouched();
    f.controls["review_result_status"].markAsTouched();
    f.controls["review_comments"].markAsTouched();

    /*
    if(f.value.review_status==''){
      this.review_status_error = true;
    }else{
      this.review_status_error = false;
    }

    if(f.value.review_result_status==''){
      this.review_result_status_error = true;
    }else{
      this.review_result_status_error = false;
    }


    if(f.value.review_status==2 || f.value.review_status==3){
      if(f.value.review_comments == undefined || f.value.review_comments.trim() == ''){
        this.status_comment_error = true;
      }
    }
    if(this.myArrayList.length >0 || this.myArrayList2.length>0 
      || this.review_status_error || this.unitArrayList.length >0 || this.unitArrayList2.length >0
      || this.review_result_status_error == true || this.status_comment_error == true
    ){
      this.error = {summary:this.errorSummary.errorSummaryText};
      return false;
    }
    */
    //return false;
    if (f.valid) {
      let review_comment= [];
      let unit_review_comment= [];
      this.reviewchecklists.forEach(element => {
        //,comment:f.value.qtd_comments
        let ans = {question:element.name,question_id:element.id,answer:eval("f.value.qtd"+element.id),comment:eval("f.value.qtd_comments"+element.id)};
        review_comment.push(ans);
      });

      this.units.forEach(unitelement => {
        //let ansarr = [];
        //ansarr[unitelement.id]=[];
        this.unitReviewchecklists.forEach(element => {
          //,comment:f.value.qtd_comments
          let currid = unitelement.id+'_'+element.id;
          unit_review_comment.push({unit_id:unitelement.id,question:element.name,question_id:element.id,answer:eval("f.value.unit_qtd"+currid),comment:eval("f.value.unit_qtd_comments"+currid)});
          
        });
        //unit_review_comment.push(ansarr);
      });

      let reviewdata={
        app_id:this.id,
        answer:f.value.review_status,
        comment:f.value.review_comments,
        review_result_status:f.value.review_result_status,
        review_comment,
        unit_review_comment
      }
      //console.log(unit_review_comment);
      //return false;
      this.loading  = true;
      this.reviewchecklistservice.addReviewchecklist(reviewdata)
      .pipe(first())
      .subscribe(res => {
            
          if(res.status==1){
              this.success = {summary:res.message};
              this.buttonDisable = true;
              setTimeout(() => {
                this.router.navigateByUrl('/application/apps/view?id='+this.id);
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
      
      
      //return false;
    } else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      //alert(JSON.stringify(this.checklistForm.value))
      //this.error = 'Please';
    }
  }

}
