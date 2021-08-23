import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import { UserService } from '@app/services/master/user/user.service';
import { UserRoleService } from '@app/services/master/userrole/userrole.service';
import { first } from 'rxjs/operators';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/services/authentication.service';
import {MatDatepickerInputEvent} from '@angular/material/datepicker';

@Component({
  selector: 'app-view-user',
  templateUrl: './view-user.component.html',
  styleUrls: ['./view-user.component.scss']
})
export class ViewUserComponent implements OnInit {


  constructor( private modalService: NgbModal, private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, public userService:UserService, private userRoleService:UserRoleService,public errorSummary: ErrorSummaryService,private authservice:AuthenticationService) { }
  userData:any;
  modalOptions:NgbModalOptions;
  error:any;
  id:any;
  tab:number;
  model: any = {id:null,action:null,status:'',comment:'',witness_date:'',witness_file:'',standard_pk_id:'',witness_comment:'',valid_until:'',user_role_type:'',bscodeid:''};
  panelOpenState = true;
  userType:number;
  userdetails:any;
  userdecoded:any;
  stdData:any=[];
  decData:any=[];
  roleData:any=[];
  RoleData:any=[]
  bgroupData:any=[];
  bgroupcodeData:any=[];
  stdhistoryData:any=[];
  dechistoryData:any=[];

  personnel_details_status=true;
  role_status=false;
  closeResult: string;
  standards_business_sectors_status=false;
  qualification_details_status=false;
  working_experience_status=false;
  inspection_audit_experience_status=false;
  consultancy_experience_status=false;
  certificate_details_status=false;
  cpd_status=false; 
  declaration_status=false;
  business_sectors_status = false;
  te_business_sectors_status = false;
  user_qualification_status=false;
  alertInfoMessage:any;
  alertSuccessMessage:any;
  alertErrorMessage:any;
  cancelBtn=true;
  okBtn=true;
  form : FormGroup;
  roleform : FormGroup;
  loading=false;
  map_group_user_role_status = false;
  technical_expert_business_group_status = false;

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
	  this.minDate = new Date();
	  this.authservice.currentUser.subscribe(x => {
		if(x){
            let user = this.authservice.getDecodeToken();
			this.userType= user.decodedToken.user_type;
			this.userdetails= user.decodedToken;        
		}else{
			this.userdecoded=null;
		}
    });

    this.userService.getUserInformation(this.id).pipe(first())
    .subscribe(res => {
      this.userData = res.data;
      
    },
    error => {
        this.error = {summary:error};
        //this.loading = false;
    }); 
	
	  this.form = this.fb.group({
      id:[''],	
      role_id:[''],	  
      status:['',[Validators.required]],
      comment:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
	    username:[''],
      user_password:[''],	  
      //username:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15),this.errorSummary.cannotContainSpaceValidator]],
      //user_password:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15),this.errorSummary.cannotContainSpaceValidator]],          
    });

    this.roleform = this.fb.group({
      role_id:[''],
      username:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(25),this.errorSummary.cannotContainSpaceValidator]],
      user_password:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(25),this.errorSummary.cannotContainSpaceValidator]], 
    });

  }

  displayPopBtn:any = true;
  openConfirm(content,action,id,actiontype,bscodeid='') 
  {
    this.displayPopBtn = true;
    this.model.id = id;	
    this.model.action = action;	
    this.model.actiontype = actiontype;
    this.model.bscodeid = bscodeid;		
    
    if(action=='activate'){		
        this.alertInfoMessage='Are you sure, do you want to activate?';
    }else if(action=='deactivate'){		
        this.alertInfoMessage='Are you sure, do you want to deactivate?';
    }else if(action=='delete'){		
        this.alertInfoMessage='Are you sure, do you want to delete?';	
    }
  
    this.modalss = this.modalService.open(content, this.modalOptions);
  
    this.modalss.result.then((result) => {	
  
    }, (reason) => {
      this.model.id = null;  
      this.model.action = null;
      this.model.actiontype = null;
      this.model.bscodeid = null;
      this.alertErrorMessage = '';
      this.alertSuccessMessage = '';
      this.alertInfoMessage='';  
    });
  }

  commonUpdateData() 
  {
    this.displayPopBtn = false;
    this.alertInfoMessage='Please wait. Your request is processing';
    this.userService.deleteUserData({id:this.model.id,user_id:this.id,actiontype:this.model.actiontype,typeaction:this.model.action,business_sector_group_code_id:this.model.bscodeid}).pipe(first())
      .subscribe(res => {

        this.getUserData(this.model.actiontype);
        
        this.model.id = null;
        this.model.action = null;
        this.model.actiontype = null;
        if(this.model.actiontype == 'business_group' || this.model.actiontype == 'business_group_code'){
          this.getUserData('mapuserrole');
          this.getUserData('te_business_group');
        }
        this.cancelBtn=false;
        this.okBtn=false;
        
        if(res.status){
          this.alertInfoMessage='';
          this.alertSuccessMessage = res.message;
          setTimeout(()=>{
            this.modalss.close('');
            this.alertSuccessMessage = '';
            this.alertErrorMessage = '';
          },this.errorSummary.redirectTime);

          
        }else if(res.status == 0){			
          this.alertInfoMessage='';
          this.alertErrorMessage = res.message;	
        }else{
          this.alertInfoMessage='';
          this.alertErrorMessage = res.message;
        }				
      },
    error => {
      this.alertInfoMessage='';
      this.alertErrorMessage = error;
    });
  } 



  modalss:any;
  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  editCredentials(content,data)
  {
    this.roleform.patchValue({
      user_password:'',              
    });
    this.roleform.reset();
    this.RoleData = data;
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    this.roleform.patchValue({
      username:data.username,              
    });
  }

  downloaddocumentFile(fileid,filename){
    this.userService.downloaddocumentFile({id:fileid,user_id:this.userData.id})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }

  downloadFile(fileid,filename){
    this.userService.downloadFile({id:fileid,user_id:this.userData.id})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }

  usernameErrors:any= '';
  downloadUserFile(filename,filetype){
    this.userService.downloadUserFile({id:this.id,filetype,user_id:this.userData.id})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }

  downloadacademicFile(fileid,filename){
    this.userService.downloadAcademicFile({id:fileid,user_id:this.userData.id})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }

  showApprovedStdDetails(content,row_id)
  {
    this.stdData = this.userData.standard_approved[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedStdDetails(content,row_id)
  {
    this.stdData = this.userData.standard_rejected[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showApprovedStdHistoryDetails(content,row_id)
  {
    this.stdhistoryData = this.userData.standard_approved[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }
  
  showRejectedStdHistoryDetails(content,row_id)
  {
    this.stdhistoryData = this.userData.standard_rejected[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showApprovedDecHistoryDetails(content,row_id)
  {
    this.dechistoryData = this.userData.declaration_approved[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedDecHistoryDetails(content,row_id)
  {
    this.dechistoryData = this.userData.declaration_rejected[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showApprovedDeclarationDetails(content,row_id)
  {
    this.decData = this.userData.declaration_approved[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedDeclarationDetails(content,row_id)
  {
    this.decData = this.userData.declaration_rejected[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  gpcoderow:any;
  showApprovedbgroupDetails(content,row_id,coderow)
  {
    this.gpcoderow= coderow;
    this.bgroupData = this.userData.businessgroup_approved[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedbgroupDetails(content,row_id,coderow)
  {
    this.gpcoderow= coderow;
    this.bgroupData = this.userData.businessgroup_rejected[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showApprovedbgroupHistoryDetails(content,row_id,datalist)
  {
    this.bgroupcodeData = datalist;//this.userData.businessgroup_approved[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedbgroupHistoryDetails(content,row_id,datalist)
  {
    this.bgroupcodeData = datalist;//this.userData.businessgroup_rejected[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  tcgpcoderow:any;
  showApprovedtebgroupDetails(content,row_id,coderow)
  {
    this.tcgpcoderow= coderow;
    this.bgroupData = this.userData.tebusinessgroup_approved[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedtebgroupDetails(content,row_id,coderow)
  {
    this.tcgpcoderow= coderow;
    this.bgroupData = this.userData.tebusinessgroup_rejected[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }
  showApprovedtebgroupHistoryDetails(content,row_id,datalist)
  {
    this.bgroupcodeData = datalist;//this.userData.businessgroup_approved[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedtebgroupHistoryDetails(content,row_id,datalist)
  {
    this.bgroupcodeData = datalist;//this.userData.businessgroup_rejected[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showApprovedRoleDetails(content,row_id)
  {
    this.roleData = this.userData.role_id_approved[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedRoleDetails(content,row_id)
  {
    this.roleData = this.userData.role_id_rejected[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  downloadstdFile(fileid='',filetype='',filename=''){
    this.userService.downloadStandardFile({id:fileid,filetype,user_id:this.userData.id})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }
  

  downloadbgroupFile(fileid,filetype,filename){
    this.userService.downloadBgroupFile({id:fileid,filetype,user_id:this.userData.id})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }

  downloadtebgroupFile(fileid,filetype,filename){
    this.userService.downloadteBgroupFile({id:fileid,filetype,user_id:this.userData.id})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }
  titlename = '';
  open(content,action,id,titlename,userEntry:any='') {
  // console.log(id);
  // console.log(action);
    if(action =='standard'){
      this.model.witness_date = '';
      this.model.witness_file = '';
      // this.model.witness_comment = '';
       this.model.valid_until = '';
       //this.model.valid_until = this.errorSummary.editDateFormat(this.userData.temp_valid_until);
      let stddetails = this.userData.standard_approvalwaiting.find(x=>x.id==id);
      //console.log(stddetails);
      if(stddetails.witness_date !=''){
        //console.log(this.errorSummary.editDateFormat(stddetails.witnessdate));
       // console.log(stddetails.witness_date);
        this.model.witness_date = this.errorSummary.editDateFormat(stddetails.witness_date);
        this.model.witness_file = stddetails.witness_file;

        let witness_date = this.errorSummary.editDateFormat(stddetails.witness_date);
        //console.log(witness_date);
         let newdate = new Date(witness_date.setFullYear(witness_date.getFullYear() + 3));
         newdate = new Date(newdate.setDate(newdate.getDate() - 1));
         this.model.valid_until = this.errorSummary.editDateFormat(newdate);
      }
    }else if(action =='role'){
		this.alertInfoMessage='';
		this.alertSuccessMessage='';
		this.alertErrorMessage='';
		this.form.patchValue({
			id:this.id,
			role_id:id,
            username:'',
            user_password:''              
        });
		this.model.user_role_type=userEntry.user_role_type;
	}    

    this.titlename = titlename;
    this.model.id = id;	
    this.model.action = action;	
    this.model.status = '';
    this.model.comment ='';
    this.resetBtn();
    
	if(action!='role')
	{	
		this.alertInfoMessage='Are you sure, do you want to Approve?';
	}
    
    
    this.modalss = this.modalService.open(content, this.modalOptions);
      //this.modalService.open(content, this.modalOptions).result.then((result) => {
    
    this.modalss.result.then((result) => {	
        //this.closeResult = `Closed with: ${result}`;	 
      }, (reason) => {
      this.model.id = null;  
      //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;	  
      });
  }

  resetBtn()
  {
    this.alertInfoMessage='';
    this.alertSuccessMessage='';
    this.alertErrorMessage='';
    this.cancelBtn=true;
    this.okBtn=true;
    this.popupbtnDisable= false;
  }

  minDate: Date;
  witnessdate_change(witness_date:any){
    
    if(witness_date !=''){
    let wtdate= new Date(witness_date);
     
      let newdate = new Date(wtdate.setFullYear(wtdate.getFullYear() + 3));
     
      newdate = new Date(newdate.setDate(newdate.getDate() - 1));
      
      this.model.valid_until = this.errorSummary.editDateFormat(newdate);
      
    }
  }
  status_error=false;
  comment_error = false;
  popupbtnDisable=false;
  witness_date_error = '';
  valid_until_error = '';
  witness_comment_error = '';
  roleApproveStatus = false;
  commonModalAction(approvaltype=''){
    
    let type = this.model.action;	
    let actionid = this.model.id;
    this.status_error = false;
    this.comment_error = false;
    
    if(this.model.status ==''){
      this.status_error =true;
    }
    if(this.model.comment =='' || this.model.comment.trim()=='' ){
      this.comment_error =true;
    }
    let stdError = false;
    this.witness_date_error = '';
    this.valid_until_error = '';
    this.witness_comment_error = '';
    this.witness_fileErrors = '';
    let datapost:any = {id:actionid,type:type,user_id:this.id,status:this.model.status,comment:this.model.comment};
    if(type=='standard' && this.model.status==2){
   
      if(this.model.witness_file ==''){
        this.witness_fileErrors ='Please upload file';
        stdError = true;
      }
      if(this.model.witness_date ==''){
        this.witness_date_error ='Please enter the date';
        stdError = true;
      }
      if(this.model.valid_until ==''){
        this.valid_until_error ='Please upload valid file';
        stdError = true;
      }
      /*if(this.model.witness_comment ==''){
        this.witness_comment_error ='Please enter the witness comment';
        stdError = true;
      }*/
      
      
      datapost.valid_until =  this.model.valid_until!=''?this.errorSummary.displayDateFormat(this.model.valid_until):'';
      //datapost.witness_comment = this.model.witness_comment;
      
    }
    datapost.witness_date = this.model.witness_date!=''?this.errorSummary.displayDateFormat(this.model.witness_date):'';
     


    this.stdformData.append('formvalues',JSON.stringify(datapost));
    if(this.comment_error || this.status_error || stdError){
      return false;
    }
    //{id:actionid,type:type,user_id:this.id,status:this.model.status,comment:this.model.comment}
    this.popupbtnDisable= true;
    this.userService.sendToApproveAndReject(this.stdformData).subscribe(res => {
      this.model.id = null;
      this.model.action = null;
      this.cancelBtn=false;
      this.okBtn=false;
      this.model.status = '';
      this.model.comment ='';
       this.popupbtnDisable= true;
      if(res.status){

        this.alertInfoMessage='';
        this.alertSuccessMessage = res.message;
        setTimeout(()=>{
          this.modalss.close('deactivate');
          this.alertSuccessMessage='';
          this.popupbtnDisable= false;
        },this.errorSummary.redirectTime);
      }else if(res.status == 0){			
        this.alertInfoMessage='';
        this.alertErrorMessage = res.message;	
        this.popupbtnDisable= false;
      }else{
        this.alertInfoMessage='';
        this.alertErrorMessage = res.message;
        this.popupbtnDisable= false;
      }	

      if(type=='role')
      {
       // this.userData.role_id_waiting_approval = res.data.role_id_waiting_approval;
       // this.userData.role_id_approved = res.data.role_id_approved;
       // this.userData.role_id_rejected = res.data.role_id_rejected;
        this.getUserData(type);
      }
      else if(type=='standard')
      {
        //this.userData.standard_approvalwaiting = res.data.standard_approvalwaiting;
        //this.userData.standard_approved = res.data.standard_approved;
        //this.userData.standard_rejected = res.data.standard_rejected;
        this.getUserData(type);
      }
      else if(type=='declaration')
      {
        //this.userData.declaration_approvalwaiting = res.data.declaration_approvalwaiting;
        //this.userData.declaration_approved = res.data.declaration_approved;
        //this.userData.declaration_rejected = res.data.declaration_rejected;
        this.getUserData(type);
      }
      else if(type=='businessgroup')
      {
        //this.userData.businessgroup_approvalwaiting = res.data.businessgroup_approvalwaiting;
        //this.userData.businessgroup_approved = res.data.businessgroup_approved;
        //this.userData.businessgroup_rejected = res.data.businessgroup_rejected;
        this.getUserData('business_group');
      }else if(type=='te_business_group')
      {
        this.getUserData(type);
      }
      
      
    },
    error => {
      this.error = {summary:error};
      this.modalss.close();
      this.popupbtnDisable= false;

    });
    
  }  


  getUserData(type)
  {
	  this.userService.getUserData({'id':this.id,'actiontype':type})
	  .pipe(first())
	  .subscribe(res => {
		   if(res.status){

				//this.success = {summary:res.message};
			//	this.buttonDisable = false;
				
        if(type=='mapuserrole')
        {
          this.userData.mapuserrole = res.data['mapuserrole'];
          
        }else if(type=='te_business_group'){
          this.userData.tebusinessgroup_new = res.data['tebusinessgroup_new'];
          this.userData.tebusinessgroup_approvalwaiting = res.data['tebusinessgroup_approvalwaiting'];
          this.userData.tebusinessgroup_approved = res.data['tebusinessgroup_approved'];
          this.userData.tebusinessgroup_rejected = res.data['tebusinessgroup_rejected'];
          /*
          $resultarr["tebusinessgroup_approvalwaiting"] = [];
          $resultarr["tebusinessgroup_approved"] = [];
          $resultarr["tebusinessgroup_rejected"] = [];
          this.teBusinessEntries=res.data['tebusinessgroup_new'];
          this.teBusiness_approvalwaitingEntries=res.data['tebusinessgroup_approvalwaiting'];
          this.teBusiness_approvedEntries=res.data['tebusinessgroup_approved'];
          this.teBusiness_rejectedEntries=res.data['tebusinessgroup_rejected'];
          */
        }else if(type=='declaration'){
          this.userData.declaration_approvalwaiting = res.data['declaration_approvalwaiting'];
          this.userData.declaration_approved = res.data['declaration_approved'];
          this.userData.declaration_rejected = res.data['declaration_rejected'];
        }else if(type=='role')
        {
          this.userData.role_id_waiting_approval = res.data['role_id_waiting_approval'];
          this.userData.role_id_approved = res.data['role_id_approved'];
          this.userData.role_id_rejected = res.data['role_id_rejected'];
          
        }
        else if(type=='standard')
        {
          this.userData.standard_approvalwaiting = res.data['standard_approvalwaiting'];
          this.userData.standard_approved = res.data['standard_approved'];
          this.userData.standard_rejected = res.data['standard_rejected'];
          //this.getUserData(type);
        }else if(type=='business_group' || type=='business_group_code')
        {
          this.userData.businessgroup_approvalwaiting = res.data['businessgroup_approvalwaiting'];
          this.userData.businessgroup_approved = res.data['businessgroup_approved'];
          this.userData.businessgroup_rejected = res.data['businessgroup_rejected'];
          //this.getUserData(type);
        }
        
			
		  }else if(res.status == 0){
			//this.submittedError =1;
			//this.error = this.errorSummary.getErrorSummary(res.message,this,this.expformData);	
		  }else{
			//this.submittedError =1;
			this.error = {summary:res};
		  }
		  //this.loadingArr['audexperience'] = false;
	},
	error => {
		  this.error = error;
		 // this.loadingArr['audexperience'] = false;
	});
  }


  stdformData:FormData = new FormData();
  witness_fileErrors = '';
  witness_file = '';
  witnessfileChange(element) {
    let files = element.target.files;
    this.witness_fileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.stdformData.append("witness_file", files[0], files[0].name);
      this.model.witness_file = files[0].name;
    }else{
      this.witness_fileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }
  removestd_witnessFiles(){
    this.model.witness_file = '';
    this.stdformData.delete('witness_file');
  }

  

  changeUserTab(arg)
  {
	  this.personnel_details_status=false;
	  this.role_status=false;
	  this.standards_business_sectors_status=false;
	  this.qualification_details_status=false;
	  this.working_experience_status=false;
	  this.inspection_audit_experience_status=false;
	  this.consultancy_experience_status=false;
	  this.certificate_details_status=false;
	  this.cpd_status=false; 
	  this.declaration_status=false; 
	  this.business_sectors_status=false;
	  this.user_qualification_status=false;
    this.te_business_sectors_status=false;
    this.map_group_user_role_status = false;

	  if(arg=='personnel_details'){
		   this.personnel_details_status=true;
	  }else if(arg=='role'){
		   this.role_status=true;
    }else if(arg=='standards_business_sectors'){
      this.standards_business_sectors_status=true;
    }else if(arg=='qualification_details'){
      this.qualification_details_status=true;
    }else if(arg=='working_experience'){
      this.working_experience_status=true;
    }else if(arg=='inspection_audit_experience'){
      this.inspection_audit_experience_status=true;
    }else if(arg=='consultancy_experience'){
      this.consultancy_experience_status=true;
    }else if(arg=='certificate_details'){
      this.certificate_details_status=true;
    }else if(arg=='cpd'){
      this.cpd_status=true;
    }else if(arg=='declaration'){
		  this.declaration_status=true;			
	  }else if(arg=='map_group_user_role'){
		  this.map_group_user_role_status=true;
	  }else if(arg=='business_sectors'){
		  this.business_sectors_status=true; 
	  }else if(arg=='te_business_sectors'){
		  this.te_business_sectors_status=true; 
	  }else if(arg=='user_qualification'){	  
		  this.user_qualification_status=true;
	  }
  }
  get f() { return this.form.controls; } 
  get rf() { return this.roleform.controls; } 
  
  
  changeUserRoleStatus(val)
  {
	  if(val==2)
	  {
		  this.roleApproveStatus=true;
	  }else{
		  this.roleApproveStatus=false;
	  }
	  
  }

  SaveCredentials()
  {
    this.rf.username.markAsTouched();
    this.rf.user_password.markAsTouched();
    
    if(this.roleform.valid)
    {
      let username = this.roleform.get('username').value;
      let user_password = this.roleform.get('user_password').value;

      let expobject:any={username:username,user_password:user_password,role_id:this.RoleData.id};

      this.loading  = true;
      let sameUsernameerror:any={};
      this.userService.changeCredential(expobject).pipe(first())
		  .subscribe(res => {
          if(res.already_exists)
          {
            sameUsernameerror.username = ['The Username has been taken already'];	
            this.error = this.errorSummary.getErrorSummary(sameUsernameerror,this,this.roleform); 					
            this.loading  = false;
            return false;				
          }else{
            this.alertSuccessMessage = res.message;	
            this.getUserData('role');				
            setTimeout(()=>{
              this.resetUserSel();						
            },this.errorSummary.redirectTime);
          }
        
      },
      error => {
        this.error = {summary:error};			
        this.loading  = false;
      });		
    }
  }
  
  checkUserSel()
  {
    this.f.status.markAsTouched();
    this.f.comment.markAsTouched();
    
    if(this.model.user_role_type)
    {
      if(this.form.get('status').value== 2)
      {
        //this.f.username.setValidators([Validators.required]);
        //username:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15),this.errorSummary.cannotContainSpaceValidator]],
        this.f.username.setValidators([Validators.required,Validators.pattern('^[a-zA-Z0-9\#$@%-]+$'),Validators.minLength(6),Validators.maxLength(25)]);
        this.f.username.updateValueAndValidity();
        this.f.username.markAsTouched();
        
        //this.f.user_password.setValidators([Validators.required]);
        //this.f.user_password.setValidators([Validators.required,Validators.pattern('^[a-zA-Z0-9\#$@%-]?$'),Validators.minLength(6),Validators.maxLength(25)]);
        this.f.user_password.setValidators([Validators.required,Validators.pattern('^[a-zA-Z0-9\#$@%-]+$'),Validators.minLength(6),Validators.maxLength(25)]);
        this.f.user_password.updateValueAndValidity();
        this.f.user_password.markAsTouched();	
      }	
    }else{
      this.f.username.setValidators([]);
      this.f.username.updateValueAndValidity();
      this.f.username.markAsTouched();
      
      this.f.user_password.setValidators([]);
      this.f.user_password.updateValueAndValidity();
      this.f.user_password.markAsTouched();
      
      this.form.patchValue({
        username:'',
        user_password:''              
      });
    }
    	
      
    if (this.form.valid) 
    {
      let status = this.form.get('status').value;
      let extension_date = '';
      let comment = this.form.get('comment').value;
      
      if(this.model.user_role_type)
      {
        let username = '';
        let user_password = '';
        if(this.form.get('status').value== 3)
        {
          username = this.form.get('username').value;
          user_password = this.form.get('user_password').value;
        }
      }	
      
      this.loading  = true;
      
      let sameUsernameerror:any={};
      this.userService.userRoleApproval(this.form.value).pipe(first())
      .subscribe(res => {
        if(this.model.user_role_type)
        {
          if(res.already_exists)
          {
            sameUsernameerror.username = ['The Username has been taken already'];	
            this.error = this.errorSummary.getErrorSummary(sameUsernameerror,this,this.form); 					
            this.loading  = false;
            return false;				
          }else{
            this.alertSuccessMessage = res.message;	
            this.getUserData('role');				
            setTimeout(()=>{
              this.resetUserSel();						
            },this.errorSummary.redirectTime);
          }
        }else{
          this.alertSuccessMessage = res.message;	
          this.getUserData('role');				
          setTimeout(()=>{
            this.resetUserSel();
          },this.errorSummary.redirectTime);
        }
      },
      error => {
        this.error = {summary:error};			
        this.loading  = false;
      });		
    }
   
  }
  
  resetUserSel()
  {
    this.modalss.close('');
    this.alertSuccessMessage = '';						
    this.loading  = false;
    
    this.form.patchValue({
      id:'',
      role_id:'',
      status:'',
      comment:'',
      username:'',
      user_password:''              
    });
  }
}
