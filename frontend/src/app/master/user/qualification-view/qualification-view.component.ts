import { Component, OnInit,ViewChild } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { UserqualificationChecklistService } from '@app/services/master/checklist/userqualification-checklist.service';
import { User } from '@app/models/master/user';
import { QualificationChecklist } from '@app/models/master/qualification-checklist';
import { first } from 'rxjs/operators';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import {saveAs} from 'file-saver';

@Component({
  selector: 'app-qualification-view',
  templateUrl: './qualification-view.component.html',
  styleUrls: ['./qualification-view.component.scss'],
  providers: [UserqualificationChecklistService]
})
export class QualificationViewComponent implements OnInit {

  userList:User[];
  questionList:QualificationChecklist[];
  guidanceIncludeList:Array<any> = [];
  userData:User;
  checklistForm : any = {standard:[]};
  reviewcommentlist=[];
  answerArr:any=[];
  recurringPeriod:any=[];
  success:any=[];
  error:any=[];
  id:number;
  panelOpenState = false;
  historyReviewData:any;
  historyApprovalData:any;
  standardList:Array<any> = [];
  roleList:Array<any> = [];
  standard:any;
  role:any;
  business_sector:any;
  business_sector_group:any;
  buttonDisable = false;

  @ViewChild('checklistForm', {static: false}) ngForm: NgForm;

  constructor(private activatedRoute:ActivatedRoute,private router: Router,public userService:UserService,public qualificationService:UserqualificationChecklistService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
    //this.success = {summary:'rrrr'};
	  this.id = this.activatedRoute.snapshot.queryParams.id;
    /*
    this.userService.getAllUser({type:1}).pipe(first())
    .subscribe(res => {
      this.userList = res['users'];
    },
    error => {
        //this.error = {summary:error};
    });
*/  
    this.userService.getUserStdRoles(this.id).pipe(first())
    .subscribe(res => {
      let data = res['data'];
      this.standardList = data['standards'];
      this.roleList = data['roles'];
      this.userData = data['userdata'];

      this.standard = [];
      this.role = [];
      /*this.standardList.forEach((x)=>{
        this.standard.push(x.id);
      })
      this.roleList.forEach((x)=>{
        this.role.push(x.id);
      })

      this.getQuestion();
      */
      //this.userList = res['users'];
      //this.filteredprocessMulti.next(this.userList.slice());
    },
    error => {
        this.error = {summary:error};
    });
    this.qualificationService.getQualificationHistoryData(this.id).pipe(first())
    .subscribe(res => {
      this.historyReviewData = res['data']['review'];
      this.historyApprovalData = res['data']['approval'];
      this.answerArr = res['answerArr'];
      this.recurringPeriod = res['recurringPeriod'];

    },
    error => {
        this.error = {summary:error};
    });

    /*
    var valid_untilDate = new Date('2019-12-30');
    var currentDate = new Date('2019-12-31');
    if(valid_untilDate >= currentDate) {
      //valid_untilDate+days
    }else{
      //currentDate+days
    }
    */
    //var newDate = new Date(date.setMonth(date.getMonth()+8));
    //result.setDate(date.getDate() + days);

    //
    /*
    this.qualificationService.getQualificationView(this.id).pipe(first())
    .subscribe(res => {
      
      this.standardList = res['standards'];
      this.roleList = res['roles'];
      this.answerArr = res['data']['answerArr'];
      this.recurringPeriod = res['data']['recurringPeriod'];
      this.userData = res['data']['userdetails'];

      
      this.historyData = res['historydata'];
      this.questionList = res['data']['questions'];
      
      if(this.questionList && this.questionList.length>0){
        this.questionList.forEach(element => {
            this.reviewcommentlist['qtd_recurring'+element.id]=element.recurring_period;
            if(element.recurring_period !=6){
              this.reviewcommentlist['qtd_valid'+element.id]=new Date(element.new_valid_until);
            }
            
        })
      }else{
        this.error = {summary:"No Question Found"};
      }
       
	    //console.log(this.recurringPeriod);
    },
    error => {
        //this.error = {summary:error};
    });
    */
  }
  monthToAddArr = {'1':1,'2':2,'3':3,'4':6,'5':12};
  changeValidDate(rperiod,qid){
    let qtdetails = this.questionList.find(x=>x.id==qid);
    let valid_until = qtdetails.valid_until;
    let currentdate = qtdetails.currentdate;
    let newDate = new Date(currentdate);
    if(rperiod != 6 && rperiod !='' && rperiod != null){
      let monthToAdd = this.monthToAddArr[rperiod];
      let currentformatDate = new Date(currentdate);
      if(valid_until !=='' && valid_until!= null && valid_until!='0000-00-00'){
        let valid_untilDate = new Date(valid_until);
       
        
        if(valid_untilDate >= currentformatDate) {
          newDate = new Date(valid_untilDate.setMonth(valid_untilDate.getMonth()+monthToAdd));
        }else{
          newDate = new Date(currentformatDate.setMonth(currentformatDate.getMonth()+monthToAdd));
        }
      }else{
        newDate = new Date(currentformatDate.setMonth(currentformatDate.getMonth()+monthToAdd));
      }
      this.reviewcommentlist['qtd_valid'+qid] = newDate;
    }else{
      this.reviewcommentlist['qtd_valid'+qid] = '';
    }
    //console.log(rperiod);
    /*
    var valid_untilDate = new Date('2019-12-30');
    var currentDate = new Date('2019-12-31');
    if(valid_untilDate >= currentDate) {
      //valid_untilDate+days
    }
    
    else{
      //currentDate+days
    }
    */
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
  
  downloadFile(id,filename,type=''){
    this.qualificationService.downloadFile({id:id,type})
    .subscribe(res => {
      
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.docsContentType[fileextension];
      saveAs(new Blob([res],{type:contenttype}),filename);
     
    });
  }

  businessSectorList:Array<any> = [];
  businessSectorGroupList:Array<any> = [];

  getSector(){
    //let business_sectors = this.ngForm.control.get("business_sector").value;
    let standard_ids = this.ngForm.control.get("standard").value;
    //let business_sector_groups = this.ngForm.control.get("business_sector_group").value;
    let user_id = this.id;

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
        let standard_ids = this.ngForm.control.get("standard").value;
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
    let user_id = this.id; //this.ngForm.control.get("user_id").value;
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
        //let user_id = this.ngForm.control.get("user_id").value;
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
        this.businessSectorGroupList = [];
        this.recurringPeriod = [];
        this.error = {summary:error};
        this.loadingInfo['questions'] = 0;
      });
    }else{
      this.questionList = [];
      this.answerArr = [];
      this.recurringPeriod = [];
      this.businessSectorGroupList = [];
      //this.error = {summary:"No Question Found"};
    }
  }



  loadingInfo:any=[];
  getQuestion(){
    let user_id = this.id;
    //let standard_ids = this.ngForm.control.get("standard").value;
    //let role_ids = this.ngForm.control.get("role").value;

    let standard_ids = this.standard;
    let role_ids = this.role;

    let business_sectors = this.ngForm.control.get("business_sector")?this.ngForm.control.get("business_sector").value:[];
    let business_sector_groups = this.ngForm.control.get("business_sector_group")?this.ngForm.control.get("business_sector_group").value:[];

    
    
    //return false;
    if(user_id && standard_ids!==undefined && standard_ids.length >0 && role_ids!==undefined && role_ids.length >0 
      && business_sectors!==undefined && business_sectors.length >0
      && business_sector_groups!==undefined && business_sector_groups.length >0){
      this.qualificationService.getQualificationAnswerData({id:user_id,standard_ids,role_ids,business_sector_groups}).pipe(first())
      .subscribe(res => {
        let standard_ids = this.standard;
        let role_ids = this.role;

        if(standard_ids!==undefined && role_ids!==undefined && standard_ids.length >0 && role_ids.length >0){
          this.questionList = res['data']['questions'];
          this.answerArr = res['data']['answerArr'];
          this.recurringPeriod = res['data']['recurringPeriod'];
          this.userData = res['data']['userdetails'];
          //this.historyData = res['historydata'];
          
          if(this.questionList && this.questionList.length>0){
            this.questionList.forEach(element => {
                this.reviewcommentlist['qtd_recurring'+element.id]=element.recurring_period;
                if(element.recurring_period !=6){
                  this.reviewcommentlist['qtd_valid'+element.id]=new Date(element.new_valid_until);
                }
                
            })
          }else{
            this.error = {summary:"No Question Found"};
          }
        }else{
          this.questionList = [];
          this.answerArr = [];
          this.recurringPeriod = [];
        }
      },
      error => {
        this.error = {summary:error};
        
      });
    }else{
      this.questionList = [];
      this.answerArr = [];
      this.recurringPeriod = [];
      //this.historyData = [];
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


  answerErrList= [];
  recurringErrList= [];
  commentErrList= [];
  fileErrList= [];
  loading = false;
  approvestd = [];
  hideStatus=[];
  onSubmit(f:NgForm) {
    //this.review_rfalse;
    ///this.status_comment_error = false;
    let formerror = false;
    //let stddetails = this.questionList.find(x=>x.id==standard_id);
    this.questionList.forEach(element => {

      let qid = element.id;

      let qtd_validuntil = eval("f.value.qtd_validuntil"+qid);
      let qtd_recurring = eval("f.value.qtd_recurring"+qid);


      if(qtd_recurring==null || qtd_recurring ==''){
        f.controls["qtd_validuntil"+qid].markAsTouched();
        //console.log(1);
        formerror=true;
      }
      if( qtd_recurring!=null && qtd_recurring != '' && qtd_recurring != 6 && (qtd_validuntil=='' || qtd_validuntil==null)){
        //console.log(2);
        f.controls["qtd_validuntil"+qid].markAsTouched();
        formerror=true;
      }
    });
    //console.log(formerror);
    if (!formerror) {
      
      // let stddetails = this.questionList.find(x=>x.id==standard_id);
     
      let questions = [];
      this.questionList.forEach(element => {

        let qid = element.id;
        let qtd_recurring = eval("f.value.qtd_recurring"+qid);
        let qtd_validuntil = '';
        if( qtd_recurring != '' && qtd_recurring != 6){
          qtd_validuntil = eval("f.value.qtd_validuntil"+qid);
        }
        
        
        
        let qdata= {valid_until:qtd_validuntil,recurring_period:qtd_recurring,business_sector_group_ids:element.business_sector_group_ids,standard_ids:element.standard_ids,role_ids:element.role_ids, question_id:element.id,question:element.name ,answer:element.answer ,comment:element.comment,file:element.file};
        questions.push(qdata);

      });
      
      //let user_id = this.ngForm.control.get("user_id").value;

      let user_id =  this.userData.id;
      let stddata = {"standard":[{user_id,questions}]};

      //console.log(unit_review_comment);
      //return false;
      this.loading  = true;
      //this.formData.append('formvalues',JSON.stringify(stddata));

      this.qualificationService.approveData(stddata)
      .pipe(first())
      .subscribe(res => {
          
          if(res.status==1){
              this.success = {summary:res.message};
              this.buttonDisable = true;
              setTimeout(() => {
                this.router.navigateByUrl('/master/user/list');
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
      this.error = {summary:'Please fill all the mandatory fields (marked with *)'};
      //alert(JSON.stringify(this.checklistForm.value))
      //this.error = 'Please';
    }
  }
}