import { Component, OnInit,ViewChild } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { UserqualificationChecklistService } from '@app/services/master/checklist/userqualification-checklist.service';
import { User } from '@app/models/master/user';
import { QualificationChecklist } from '@app/models/master/qualification-checklist';
import { first,takeUntil } from 'rxjs/operators';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Subject,ReplaySubject } from 'rxjs';

@Component({
  selector: 'app-qualification-review',
  templateUrl: './qualification-review.component.html',
  styleUrls: ['./qualification-review.component.scss']
})
export class QualificationReviewComponent implements OnInit {

  userList:User[];
  questionList:QualificationChecklist[];
  guidanceIncludeList:Array<any> = [];
  standardList:Array<any> = [];
  roleList:Array<any> = [];
  checklistForm : any = {user_id:null};
  reviewcommentlist=[];
  answerArr:any=[];
  recurringPeriod:any=[];
  success:any='';
  error:any='';
  panelOpenState = true;
  formChangesSubscription:any;
  user_id:any;
  userFilterCtrl:any;
  standard:any;
  role:any;
  questionAnswerArr:any;
  loadingInfo:any=[];


  //formChangesSubscriptionss:any;
  @ViewChild('checklistForm', {static: false}) ngForm: NgForm;
  //@ViewChild('user_id', { static: false,read: NgControl }) user_id: NgControl;
  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,public userService:UserService,public qualificationService:UserqualificationChecklistService,private errorSummary: ErrorSummaryService) { }

  searchvalue = '';
  ngOnInit() {

    setTimeout(() => {
      this.formChangesSubscription = this.ngForm.control.get("userFilterCtrl").valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterProcess();
      });
      /*.valueChanges.subscribe(x => {
        console.log(x);
      });
      */
    });

    this.userService.getAllUser({type:1}).pipe(first())
    .subscribe(res => {
      this.userList = res['users'];
      this.filteredprocessMulti.next(this.userList.slice());
    },
    error => {
        //this.error = {summary:error};
    });


    
    /*
    this.qualificationService.getQualificationChecklist().pipe(first())
    .subscribe(res => {
      this.questionList = res['data']['standard'];
      this.answerArr = res['data']['answerArr']?res['data']['answerArr']:[];
      this.recurringPeriod = res['data']['recurringPeriod']?res['data']['recurringPeriod']:[];
      
      this.questionList.forEach(element => {

        element.question.forEach(qtd => {
          this.reviewcommentlist['qtd'+element.id+''+qtd.id]='';
          this.reviewcommentlist['qtd_recurring'+element.id+''+qtd.id]='';
            
        });
        
      })
      //console.log(this.answerArr);
    },
    error => {
        //this.error = {summary:error};
    });
    */

  }
  private _onDestroy = new Subject<void>();
  ngOnDestroy() {
    this._onDestroy.next();
    this._onDestroy.complete();
  }
  public filteredprocessMulti: ReplaySubject<User[]> = new ReplaySubject<User[]>(1);
  private filterProcess() {
    if (!this.userList) {
      return;
    }
    // get the search keyword
    let search = this.ngForm.control.get("userFilterCtrl").value;//this.f.processFilterCtrl.value;
    if (!search) {
      this.filteredprocessMulti.next(this.userList.slice());
      return;
    } else {
      search = search.toLowerCase();
    }
    // filter the banks
    this.filteredprocessMulti.next(
      this.userList.filter(p => p.first_name.concat('',p.last_name).toLowerCase().indexOf(search) > -1)
    );
  }

  answerChanges(answerVal,std_id,qid){
    //approvestd
    let stddetails = this.questionList.find(x=>x.id==std_id);
    let stdResult=true;
    stddetails['question'].forEach(element => {

      let qid = stddetails.id+''+element.id;
      let answer = this.ngForm.control.get("qtd"+qid).value;
      //let answer = eval("f.value.qtd"+qid);
      //console.log(answer);
      if(answer==2 || answer=='' || answer==null){
        stdResult = false;
      }
    });
    if(stdResult){
      this.approvestd[std_id]=true;
    }else{
      this.approvestd[std_id]=false;
    }
  }
  businessSectorList:Array<any> = [];
  businessSectorGroupList:Array<any> = [];
  processList:Array<any> = [];
  notFoundList = [];
  getUserDetails(userid){
    this.standardList = [];
    this.roleList = [];
    this.questionList = [];
    this.answerArr = [];
    this.recurringPeriod = [];
    this.standard = [];
    this.role = [];

    if(userid){
      this.loadingInfo['stdrole'] = 1;
      this.userService.getUserStdRoles(userid).pipe(first())
      .subscribe(res => {
        let data = res['data'];
        this.standardList = data['standards'];
        this.roleList = data['roles'];
        //this.processList = data['processes'];

        this.businessSectorList = [];
        this.businessSectorGroupList = [];
        //this.businessSectorList = data['business_sector'];
        //this.businessSectorGroupList = data['business_sector_group'];
        
        
        let error=[];
        if(this.standardList.length <=0){
          error.push('Standard');
        }
        if(this.businessSectorList.length <=0){
          error.push('Business Sector');
        }
        /*if(this.businessSectorGroupList.length <=0){
          error.push('Business Sector Group');
        }*/
        if(this.roleList.length <=0){
          error.push('Role');
        }
        this.notFoundList = error;
        this.loadingInfo['stdrole'] = 0;
        //this.userList = res['users'];
        //this.filteredprocessMulti.next(this.userList.slice());
      },
      error => {
        this.loadingInfo['stdrole'] = 0;
          //this.error = {summary:error};
      });
    }else{
      this.standardList = [];
      this.roleList = [];
      this.businessSectorList = [];
      this.businessSectorGroupList = [];
      //this.error[0] = {summary:"No Question Found"};
    }
  }
  getSelectedValue(type,val)
  {
    if(type=='standard'){
      return this.standardList.find(x=> x.id==val).name;
    }else if(type=='role'){
      return this.roleList.find(x=> x.id==val).name;
    }else if(type=='sector'){
      if(this.businessSectorList!==undefined){
        return this.businessSectorList.find(x=> x.id==val).name;
      }
      return '';
    }else if(type=='group'){
      if(this.businessSectorGroupList!==undefined){
        return this.businessSectorGroupList.find(x=> x.id==val).name;
      }
      return '';
    }
  }


  business_sector = [];
  business_sector_group = [];
  getSector(){
    //let business_sectors = this.ngForm.control.get("business_sector").value;
    let standard_ids = this.ngForm.control.get("standard").value;
    //let business_sector_groups = this.ngForm.control.get("business_sector_group").value;
    let user_id = this.ngForm.control.get("user_id").value;

    this.business_sector = [];
    this.business_sector_group = [];
    this.businessSectorList = [];
    this.businessSectorGroupList = [];

    //console.log(role_ids);
    if(standard_ids!==undefined && standard_ids.length >0){
      
      this.loadingInfo['questions'] = 1;
      

      this.qualificationService.getUserBusinessSector({standard_ids,user_id}).pipe(
        first()
      )
      .subscribe(res => {
        //let role_ids = this.ngForm.control.get("role").value;

        //console.log('22--'+role_ids);
        
        let standard_ids = this.ngForm.control.get("standard").value;
        /*
        let role_ids = this.ngForm.control.get("role").value;
        
        let business_sectors = this.ngForm.control.get("business_sector").value;
        let business_sector_groups = this.ngForm.control.get("business_sector_group").value;
        */
        /*
        user_id && standard_ids!==undefined && standard_ids.length >0 && role_ids!==undefined && role_ids.length >0 
          && 
        */
        if(standard_ids!==undefined && standard_ids.length >0 ){
          
          this.businessSectorList = res;
          
          
          this.questionList = [];
          this.answerArr = [];
          this.recurringPeriod = [];
          this.businessSectorGroupList = [];

          this.getQuestion();

        }
        this.loadingInfo['questions'] = 0;
        
      },
      error => {
          //this.error = {summary:error};
        this.questionList = [];
        this.businessSectorList = [];
        this.businessSectorGroupList = [];
        this.answerArr = [];
        this.recurringPeriod = [];
        this.error = {summary:error};
        this.loadingInfo['questions'] = 0;
      });
    }else{
      this.questionList = [];
      this.answerArr = [];
      this.businessSectorList = [];
      this.businessSectorGroupList = [];
      this.recurringPeriod = [];
      //this.error = {summary:"No Question Found"};
    }
  }

  getSectorGroup(){
    let business_sectors = this.ngForm.control.get("business_sector").value;
    let standard_ids = this.ngForm.control.get("standard").value;
    let business_sector_groups = this.ngForm.control.get("business_sector_group").value;
    let user_id = this.ngForm.control.get("user_id").value;
    //console.log(role_ids);
    this.business_sector_group = [];
    this.businessSectorGroupList = [];

    if(business_sectors!==undefined && business_sectors.length >0 && standard_ids!==undefined && standard_ids.length >0){
      
      this.loadingInfo['questions'] = 1;
      

      this.qualificationService.getBusinessSectorGroup({user_id,business_sectors,standard_ids}).pipe(
        first()
      )
      .subscribe(res => {
        //let role_ids = this.ngForm.control.get("role").value;

        //console.log('22--'+role_ids);
        let user_id = this.ngForm.control.get("user_id").value;
        let standard_ids = this.ngForm.control.get("standard").value;
        let role_ids = this.ngForm.control.get("role").value;
        
        let business_sectors = this.ngForm.control.get("business_sector").value;
        let business_sector_groups = this.ngForm.control.get("business_sector_group").value;
        /*
        user_id && standard_ids!==undefined && standard_ids.length >0 && role_ids!==undefined && role_ids.length >0 
          && 
        */
        if(business_sectors!==undefined && business_sectors.length >0 ){
          
          this.businessSectorGroupList = res;
          
          
          this.questionList = [];
          this.answerArr = [];
          this.recurringPeriod = [];

          this.getQuestion();
        }
        this.loadingInfo['questions'] = 0;
        
      },
      error => {
          //this.error = {summary:error};
        this.questionList = [];
        this.answerArr = [];
        this.recurringPeriod = [];
        //this.businessSectorList = [];
        this.businessSectorGroupList = [];

        this.error = {summary:error};
        this.loadingInfo['questions'] = 0;
      });
    }else{
      this.questionList = [];
      this.answerArr = [];
      this.recurringPeriod = [];
      //this.businessSectorList = [];
      this.businessSectorGroupList = [];
      //this.error = {summary:"No Question Found"};
    }
  }
  getQuestion(){
    //console.log('232');
    //console.log(userid);
    let user_id = this.ngForm.control.get("user_id").value;
    let standard_ids = this.ngForm.control.get("standard").value;
    let role_ids = this.ngForm.control.get("role").value;
    let business_sectors = this.ngForm.control.get("business_sector").value;
    let business_sector_groups = this.ngForm.control.get("business_sector_group").value;
    
    //console.log(role_ids);
    if(user_id && standard_ids!==undefined && standard_ids.length >0 && role_ids!==undefined && role_ids.length >0 
      && business_sectors!==undefined && business_sectors.length >0
      && business_sector_groups!==undefined && business_sector_groups.length >0 ){
      //console.log(standard_ids.length);
      //console.log(role_ids.length);
      
      //console.log('22--'+role_ids);
      this.loadingInfo['questions'] = 1;

      this.qualificationService.getQualificationChecklist({user_id,standard_ids,role_ids,business_sectors,business_sector_groups}).pipe(
        first()
      )
      .subscribe(res => {
        //let role_ids = this.ngForm.control.get("role").value;

        //console.log('22--'+role_ids);
        let user_id = this.ngForm.control.get("user_id").value;
        let standard_ids = this.ngForm.control.get("standard").value;
        let role_ids = this.ngForm.control.get("role").value;
        let business_sectors = this.ngForm.control.get("business_sector").value;
        let business_sector_groups = this.ngForm.control.get("business_sector_group").value;

        if(user_id && standard_ids!==undefined && standard_ids.length >0 && role_ids!==undefined && role_ids.length >0 
          && business_sectors!==undefined && business_sectors.length >0
          && business_sector_groups!==undefined && business_sector_groups.length >0){
          this.questionList = res['data']['questionArr'];
          this.answerArr = res['data']['answerArr']?res['data']['answerArr']:[];
          this.questionAnswerArr = res['data']['questionAnswerArr']?res['data']['questionAnswerArr']:[];

          if(this.questionList && this.questionList.length>0){
            this.questionList.forEach(element => {

              //element.question.forEach(qtd => {
                this.reviewcommentlist['qtd'+element.id]='';
                if(this.questionAnswerArr[element.id] !==undefined){
                  //console.log(this.questionAnswerArr[element.id]);
                  //this.reviewcommentlist['qtd_comments'+element.id]='222';
                  this.reviewcommentlist['qtd_comments'+element.id]=this.questionAnswerArr[element.id]['comment'];
                  this.questionfile[element.id] = {name:this.questionAnswerArr[element.id]['file']};
                  //this.reviewcommentlist['qtd_file'+element.id]=this.questionAnswerArr[element.id]['file'];
                }
                //this.reviewcommentlist['qtd_recurring'+element.id+''+qtd.id]=qtd.recurring_period;
                  
              //});
              
            })
          }else{
            this.questionList = [];
            this.answerArr = [];
            this.recurringPeriod = [];
            //this.error = {summary:''};
            this.loadingInfo['questions'] = 0;
          }
        }else{
          this.error = {summary:"No Question Found"};
        }
        this.loadingInfo['questions'] = 0;
        /*
        this.questionList = res['data']['standard'];
        this.answerArr = res['data']['answerArr']?res['data']['answerArr']:[];
        this.recurringPeriod = res['data']['recurringPeriod']?res['data']['recurringPeriod']:[];
        if(this.questionList && this.questionList.length>0){
          this.questionList.forEach(element => {

            element.question.forEach(qtd => {
              this.reviewcommentlist['qtd'+element.id+''+qtd.id]='';
              this.reviewcommentlist['qtd_recurring'+element.id+''+qtd.id]=qtd.recurring_period;
                
            });
            
          })
        }else{
          this.error[0] = {summary:"No Question Found"};
        }
        */
        //console.log(this.answerArr);
      },
      error => {
          //this.error = {summary:error};
        this.questionList = [];
        this.answerArr = [];
        this.recurringPeriod = [];
        this.error = {summary:error};
        this.loadingInfo['questions'] = 0;
      });
    }else{
      this.questionList = [];
      this.answerArr = [];
      this.recurringPeriod = [];
      //this.error = {summary:"No Question Found"};
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

  formData:FormData = new FormData();
  questionfile = [];
  removeFile(stdqid){
    this.questionfile[stdqid] = '';
    this.formData.delete("questionfile["+stdqid+"]");
  }
  fileChange(element,stdqid:string) {
    let files = element.target.files;
    
    let fileextension = files[0].name.split('.').pop();
    if(this.qualificationService.validDocs.includes(fileextension))
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


  answerErrList= [];
  recurringErrList= [];
  commentErrList= [];
  fileErrList= [];
  loading = false;
  approvestd = [];
  hideStatus=[];
  buttonDisable=false;
  onSubmit(f:NgForm) {
    //this.review_rfalse;
    ///this.status_comment_error = false;
    let formerror = false;
    //let stddetails = this.questionList.find(x=>x.id==standard_id);
    this.questionList.forEach(element => {

      let qid = element.id;

      let answer = eval("f.value.qtd"+qid);
      let qtd_recurring = eval("f.value.qtd_recurring"+qid);
      let qtd_comments = eval("f.value.qtd_comments"+qid);
      let qtd_validuntil = eval("f.value.qtd_validuntil"+qid);

      //console.log(answer);
      if(answer==null || answer ==''){
        f.controls["qtd"+qid].markAsTouched();
        //this.answerErrList
        //this.answerErrList[qid]=true;
        formerror=true;
      }/*else{
        var index = this.answerErrList.indexOf(qid);
        //console.log(index);
        if (index == -1) {
          //this.answerErrList[qid]=false;
        }
      }*/
      /*if(qtd_recurring==null || qtd_recurring ==''){
        //this.recurringErrList[qid]=true;
        f.controls["qtd_recurring"+qid].markAsTouched();
        formerror=true;
      }else{
        var index = this.recurringErrList.indexOf(qid);
        if (index == -1) {
          //this.recurringErrList[qid]=false;
        }
      }*/
      if( answer == 2 && (qtd_comments==null || qtd_comments.trim() =='')){
        //this.commentErrList[qid]=true;
        f.controls["qtd_comments"+qid].markAsTouched();
        formerror=true;
      }/*else{
        var index = this.commentErrList.indexOf(qid);
        if (index == -1) {
          this.commentErrList[qid]=false;
        }
      }*/
      if(element.file_upload_required ==1 && answer == 1 && (this.questionfile[qid]===undefined || this.questionfile[qid]==null || this.questionfile[qid] == '')){
        this.fileErrList[qid]=true;
        formerror=true;
      }else{
        var index = this.fileErrList.indexOf(qid);
        if (index == -1) {
          this.fileErrList[qid]=false;
        }
      }
      
      //console.log("f.value.qtd"+element.id+''+stddetails.id);
      //let comment = eval("f.value.qtd_comments"+element.id);
      /*if(answer ==undefined || answer==''){
        this.myArrayList.push(element.id);
      }else if(answer!=1 && (comment ==undefined || comment.trim()=='')){
        this.myArrayList2.push(element.id);
        

        this.myArrayList = this.myArrayList.filter(item => item !== element.id);
        
      }else{

        this.myArrayList = this.myArrayList.filter(item => item !== element.id);
        
        this.myArrayList2 = this.myArrayList2.filter(item => item !== element.id);
        
      }
    */

    });

    if (!formerror) {
      
      // let stddetails = this.questionList.find(x=>x.id==standard_id);
      
      let questions = [];
      this.questionList.forEach(element => {

        let qid = element.id;
        let answer = eval("f.value.qtd"+qid);
        //let qtd_recurring = eval("f.value.qtd_recurring"+qid);
        let qtd_comments = eval("f.value.qtd_comments"+qid);
        //let qtd_validuntil = eval("f.value.qtd_validuntil"+qid);
        let filename = (this.questionfile[qid] !== undefined)?this.questionfile[qid].name:'';
        let qdata= {business_sector_group_ids:element.business_sector_group_ids,standard_ids:element.standard_ids,role_ids:element.role_ids, question_id:element.id,question:element.name ,answer:answer ,comment:qtd_comments,file:filename};
        questions.push(qdata);

      });
      
      let user_id = this.ngForm.control.get("user_id").value;

      let stddata = {"standard":[{user_id,questions}]};

      //console.log(unit_review_comment);
      //return false;
      this.loading  = true;
      this.formData.append('formvalues',JSON.stringify(stddata));

      this.qualificationService.addData(this.formData)
      .pipe(first())
      .subscribe(res => {
          
          if(res.status==1){
              this.success = {summary:res.message};
              
              this.buttonDisable = true;
              
              setTimeout(() => {
                //this.router.navigateByUrl('/master/user/qualification-review');
                
                this.router.navigateByUrl('/master/user/qualification-view?id='+user_id);
                //this.hideStatus[standard_id] = true;
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
      //alert(JSON.stringify(this.checklistForm.value))
      //this.error = 'Please';
      this.error = {summary:'Please fill all the mandatory fields (marked with *)'};
    }

    
/*
    this.units.forEach(unitelement => {
      this.unitReviewchecklists.forEach(element => {

        let currid = unitelement.id+''+element.id;
        let answer = eval("f.value.unit_qtd"+currid);
        let comment = eval("f.value.unit_qtd_comments"+currid);
        if(answer ==undefined || answer==''){
          this.unitArrayList.push(currid);
        }else if(answer==2 && (comment ==undefined || comment=='')){
          this.unitArrayList2.push(currid);
          

          this.unitArrayList = this.unitArrayList.filter(item => item !== currid);
          
        }else{

          this.unitArrayList = this.unitArrayList.filter(item => item !== currid);
          
          this.unitArrayList2 = this.unitArrayList2.filter(item => item !== currid);
          
        }
      });
    });



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
          let currid = unitelement.id+''+element.id;
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
              
              setTimeout(() => {
                this.router.navigateByUrl('/application/apps/view?id='+this.id);
              }, 2000);
              
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
      //alert(JSON.stringify(this.checklistForm.value))
      //this.error = 'Please';
    }*/
  }

}
