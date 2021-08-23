import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { CountryService } from '@app/services/country.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import { UserService } from '@app/services/master/user/user.service';
import { StandardService } from '@app/services/master/standard/standard.service';
import { ProcessService } from '@app/services/master/process/process.service';
import { UserRoleService } from '@app/services/master/userrole/userrole.service';
import { BusinessSectorService } from '@app/services/master/business-sector/business-sector.service';

import { Country } from '@app/models/master/country';
import { UserRole } from '@app/models/master/userrole';
import { Process } from '@app/models/master/process';
import { BusinessSector } from '@app/models/master/business-sector';
import { BusinessSectorGroup } from '@app/models/master/business-sector-group';
import { User } from '@app/models/master/user';

import { State } from '@app/services/state';
import { Standard } from '@app/services/standard';
import { first,takeUntil } from 'rxjs/operators';
import { Subject,ReplaySubject } from 'rxjs';
import { AuthenticationService } from '@app/services/authentication.service';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import { Relation } from '@app/models/master/relation';

@Component({
  selector: 'app-add-user',
  templateUrl: './add-user.component.html',
  styleUrls: ['./add-user.component.scss']
})
export class AddUserComponent implements OnInit {

  constructor( private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private countryservice: CountryService,private standardService: StandardService,private processService: ProcessService, public userService:UserService,
    private authservice:AuthenticationService, private userRoleService:UserRoleService,private BusinessSectorService: BusinessSectorService,public errorSummary: ErrorSummaryService) { }

    hasRoles = 0;
    userData:any;
  title = 'Add User';
  btnLabel = 'Save';
  countryList:Country[];
  processEntries:any=[];
  stateList:State[];
  standardList:Standard[];
  standardNewList:any;
  processList:Process[];
  roleList:UserRole[];
  bsectorList:BusinessSector[];
  bsectorgroupList:BusinessSectorGroup[];

  bgsectorList:BusinessSector[];
  bgsectorgroupList:BusinessSectorGroup[];

  franchiseList:any;
  relationEntries:Relation[]=[];
  rejrelationEntries:Relation[]=[];
  selectedOrderIds:any = {};
  standardsLength:number=0;
  submitted:number = 0;
  submittedSuccess:number = 0;
  submittedError:number = 0;
  loading = false;
  buttonDisable = false;
  error:any;
  success:any;
  maxDate = new Date();
  qualificationEntries:any=[];
  qualificationErrors=''; 
  universityErrors=''; 
  subjectErrors=''; 
  passingyearErrors=''; 
  percentageErrors='';
  closeRelError='';
  editrelation:boolean=false;
  
  experienceEntries:any=[];
  experienceErrors='';
  
  auditexperienceEntries:any=[];
  auditexperienceErrors='';

  consultancyexperienceEntries:any=[];
  consultancyexperienceErrors='';
  
  trainingEntries:any=[];
  trainingErrors=''; 
  
  certificateEntries:any=[];
  certificateErrors=''; 
  upload_certificateErrors='';
  academic_certificateErrors='';
  businessEntries:any=[];
  declarationEntries:any=[];
  declarationErrors='';
  businessErrors:any;
  formData:FormData = new FormData();
  
  stdData:any=[];
  decData:any=[];
  roleData:any=[];
  bgroupData:any=[];

  range:Array<any> = [];
  endYearRange:Array<any> = [];
  academicEndYearRange:Array<any> = [];

  customerForm : FormGroup;
  declarationApprovedForm:FormGroup;
  userloginForm: FormGroup;
  cpdForm: FormGroup;
  certificateForm: FormGroup;
  experienceForm: FormGroup;
  qualificationForm: FormGroup;
  standardForm: FormGroup;
  stdfileform: FormGroup;
  declarationForm: FormGroup;
  declarationRejectForm: FormGroup;
  conExpForm: FormGroup;
  auditExpForm: FormGroup;
  businessForm: FormGroup;
  rejbusinessForm: FormGroup;
  personnel_details_status=true;
  role_status=false;
  standards_business_sectors_status=false;
  qualification_details_status=false;
  working_experience_status=false;
  inspection_audit_experience_status=false;
  consultancy_experience_status=false;
  certificate_details_status=false;
  cpd_status=false; 
  declaration_status=false;
  business_sectors_status = false;
  map_group_user_role_status = false;
  technical_expert_business_group_status = false;
  mapUserRoleForm: FormGroup;
  technicalExpertBsForm:FormGroup;
  bgroupfileform: FormGroup;
  
  loadingArr = [];
  private mapToCheckboxArrayGroup(data: string[]): FormArray {
      return this.fb.array(data.map((i) => {
        return this.fb.group({
          name: i,
          selected: false
        });
      }));
  }
  downloadstdFile(fileid,filetype,filename){}
  getSelectedValue(type,val)
  {
    if(type=='standard'){
      return this.standardList.find(x=> x.id==val).name;
    }else if(type=='role_id'){
      return this.roleList.find(x=> x.id==val).role_name;
    }else if(type=='process'){
      return this.processList.find(x=> x.id==val).name;
    }else if(type=='business_sector_id'){
      return this.bsectorList.find(x=> x.id==val).name;
    }else if(type=='business_sector_group_id'){
      return this.bsectorgroupList.find(x=> x.id==val).group_code;
    }
  }
  private _onDestroy = new Subject<void>();

  userType:number;
  userdetails:any;
  userdecoded:any;

  minDate: Date;
  form : FormGroup;
  roleApproveStatus = false;
  ngOnInit() {

    this.authservice.currentUser.subscribe(x => {
      if(x){
        
         
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        
      }else{
        this.userdecoded=null;
      }
    });
    this.bgroupdateform = this.fb.group({
      approved_approval_date:['',[Validators.required]],
    });
    this.countryservice.getCountry().subscribe(res => {
      this.countryList = res['countries'];
    });
    this.standardService.getStandardList().subscribe(res => {
      this.standardList = res['standards'];
    });
    this.processService.getProcessList().subscribe(res => {
      this.processList = res['processes'];
      this.filteredprocessMulti.next(this.processList.slice());
    });
    this.userRoleService.getAllRoles().subscribe(res => {
      this.roleList = res['userroles'];
    });
    
    this.technicalExpertBsForm = this.fb.group({
      id:[''],  
      role_id:['',[Validators.required]],
      business_sector_id:['',[Validators.required]],
      business_sector_group_id:['',[Validators.required]]
    });
    this.rejTEbusinessForm = this.fb.group({
      id:[''],
      academic_qualification:['',[Validators.required]],
      exam_file:[''],
      technical_interview_file:['']        
    });  
    this.technicalExpertApprovedBsForm = this.fb.group({
      id:[''], 
      role_id:['',[Validators.required]],
      business_sector_id:['',[Validators.required]],
      business_sector_group_id:['',[Validators.required]],
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
    this.customerForm = this.fb.group({

      first_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],
      last_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],
      email:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.email,Validators.maxLength(255)]],
      telephone:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.pattern("^[0-9\-]*$"), Validators.minLength(8), Validators.maxLength(15)]],
	    country_id:['',[Validators.required]],
      state_id:['',[Validators.required]]
     
     /* standard:['',[Validators.required]], 
      role_id:['',[Validators.required]], 
      process:['',[Validators.required]], 
      business_sector_id:['',[Validators.required]],
      business_sector_group_id:['',[Validators.required]],
      processFilterCtrl:[''],
      qualification:[''],
      university:[''],
      subject:[''],
      passingyear:[''],
      percentage:[''],
      experience:[''],
      responsibility:[''],
      exp_from_date:[''],
      exp_to_date:[''],      
      training_subject:[''],
      training_hours:[''],
      training_date:[''],
      certificate_name:[''],
      completed_date:[''],
      upload_certificate:['']
      */

    });
    this.declarationApprovedForm = this.fb.group({
      id:[''],  
      declaration_company:['',[Validators.required,this.errorSummary.noWhitespaceValidator]], 
      declaration_contract:['',[Validators.required]],
      declaration_interest:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
      declaration_start_year:['',[Validators.required,Validators.pattern("^[0-9\-]*$")]],
      declaration_end_year:['',[Validators.required,Validators.pattern("^[0-9\-]*$")]],
      sel_close:['',[Validators.required]],
      sel_close2:['',[Validators.required]],
      spouse_work:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
      relation_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
      declaration_relation:['',[Validators.required]]
    });
    this.bgroupfileform = this.fb.group({
      approved_examfilename:[''],
      approved_technicalfilename:[''],
      approved_approval_date:['',[Validators.required]],
    });
    this.userloginForm = this.fb.group({
      //username:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$"),this.errorSummary.cannotContainSpaceValidator]],
      //user_password:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      role_id:['',[Validators.required]],
      franchise_id:['',[Validators.required]]
	  });

    this.cpdForm = this.fb.group({
      training_subject:[''],
      training_hours:[''],
      training_date:['']
    });
    

    this.certificateForm = this.fb.group({
      certificate_name:[''],
	  training_hours:[''],
      completed_date:[''],
      upload_certificate:['']
    });
    this.experienceForm = this.fb.group({
      experience:[''],
      job_title:[''],
      responsibility:[''],
      exp_from_date:[''],
      exp_to_date:['']
    });
    this.auditExpForm = this.fb.group({
      standard:['',[Validators.required]], 
      year:['',[Validators.required]],
      business_sector:['',[Validators.required]],
      audit_role:['',[Validators.required]],
      company:['',[Validators.required]],
      cb:['',[Validators.required]],
      days:['',[Validators.required,Validators.pattern("^[0-9\-]*$")]],
      process:['',[Validators.required]],
      processFilterCtrl:['']
    });
    this.conExpForm = this.fb.group({
      standard:['',[Validators.required]], 
      year:['',[Validators.required]],
      company:['',[Validators.required]],
      days:['',[Validators.required,Validators.pattern("^[0-9\-]*$")]],
      process:['',[Validators.required]],
      processFilterCtrl:['']
    });
    this.declarationForm = this.fb.group({
      declaration_company:['',[Validators.required]], 
      declaration_contract:['',[Validators.required]],
      declaration_interest:['',[Validators.required]],
      declaration_start_year:['',[Validators.required]],
      declaration_end_year:['',[Validators.required]],
      sel_close: ['', [Validators.required]],
      relation_name: ['', [Validators.required, this.errorSummary.noWhitespaceValidator]],
      declaration_relation: ['', [Validators.required]]
    });
    this.qualificationForm = this.fb.group({
      qualification:[''],
      university:[''],
      subject:[''],
      start_year:[''],
      end_year:['']
    });
    this.standardForm = this.fb.group({
      standard:['',[Validators.required]], 
      role_id:['',[Validators.required]], 
      // process:['',[Validators.required]], 
      // business_sector_id:['',[Validators.required]],
      // business_sector_group_id:['',[Validators.required]],
      qualification_exam:[''],
      pre_qualification:['',[Validators.required]],
       
      std_exam_date:['',[Validators.required]],
      std_exam:['',[Validators.required]],
      recycle_exam_date:['',[Validators.required]],
      recycle_exam:['',[Validators.required]],
      social_exam_date:['',[Validators.required]],
      social_exam:['',[Validators.required]],

      processFilterCtrl:['']
    });
    
    this.customerForm.patchValue({user_type:1});
    
    let year = new Date().getFullYear();
    let startyear = year - 50;
    this.range.push(year);
    for (let i = 1; i <= 50; i++) {
        this.range.push(year-i);
    }

    this.sf.processFilterCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterProcess();
    });
	
	this.declarationForm = this.fb.group({
	  declaration_company:['',[Validators.required]],
	  declaration_contract:['',[Validators.required]],
	  declaration_interest:['',[Validators.required]],
	  declaration_start_year:['',[Validators.required]],
	  declaration_end_year:['',[Validators.required]]
	});

    this.userService.getAllUser({type:3}).pipe(first())
    .subscribe(res => {
      this.franchiseList = res.users;
    },
    error => {
        this.error = {summary:error};
    });
  }

  addRelation(){
  }
  removeProcess(Id:number) {
  }
  addRejectRelation(){}
  removeRejRelation(Id:number) {
  }
  chkLoginRequired(){
  }
  public filteredfranchiseMulti: ReplaySubject<User[]> = new ReplaySubject<User[]>(1);
  userListEntriesWaitingApproval:any;
  userListEntriesApproved:any;
  userListEntriesRejected:any;
  std_exam_file:any;
  removestd_examFiles(){}
  std_examfileChange(element){}


  removequa_examFiles(){}
  qua_examfileErrors='';
  qua_exam_file='';

  qua_examfileChange(element){}

  witness_fileErrors = '';
  witness_file = '';
  witnessfileChange(element) {
    
  }
  removestd_witnessFiles(){
   
  }


  recycle_examFileErr = '';
  recycle_exam_file = '';
  recycle_examfileChange(element) {}
  removerecycle_examFiles(){}
  social_examFileErr = '';
  social_exam_file = '';
  social_examfileChange(element) {}
  removeresocial_examFiles(){}
  academicFileErr = '';
  academic_file = '';
  uploadedacademicFileNames=[];
  standard_approvalwaitingEntries:any=[];
  standard_approvedEntries:any=[];
  standard_rejectedEntries:any=[];
  setStdDisplay(stdid){}
  
  business_approvalwaitingEntries:any=[];
  business_approvedEntries:any=[];
  business_rejectedEntries:any=[];
  businessGroupErrors=''; 
  upload_businessGroupErrors='';
  standardFormDetails:any=[];
   userrolefulledit=false;
  declaration_approvalwaitingEntries:any=[];
  declaration_approvedEntries:any=[];
  declaration_rejectedEntries:any=[];
  
  downloadbgroupFile(fileid,filetype,filename){}
  getUserRoleList(value){	
  }
  downloadUserFile(filename,filetype){}
  modalss:any;
  openmodal(content,arg='') {}
  sendApprovalValue='';
  open(content,arg='') {}
  sendForApproval(data){}
  getBsectorList(value){
    let standardvals=this.standardForm.controls.standard.value;
    let processvals=this.standardForm.controls.process.value;
    if(standardvals.length>0 && processvals.length>0)
    {
      this.BusinessSectorService.getBusinessSectors({standardvals,processvals}).subscribe(res => {
        this.bsectorList = res['bsectors'];
        this.standardForm.patchValue({business_sector_id:''});
      });	
    }else{		
      this.bsectorList = [];
      this.standardForm.patchValue({business_sector_id:''});		
    }
  }

  showApprovedStdHistoryDetails(content,row_id)
  {}
  showRejectedStdHistoryDetails(content,row_id){}

  showApprovedbgroupHistoryDetails(content,row_id,datalist)
  {}
  editRelation(index:number){}
  updateRelation(){}

  showApprovedDecHistoryDetails(content,row_id){}
  getBsectorgroupList(value){
    let standardvals=this.standardForm.controls.standard.value;
    let processvals=this.standardForm.controls.process.value;
    let bsectorvals=value;
    if(standardvals.length>0 && processvals.length>0 && bsectorvals.length>0)
    {
      this.BusinessSectorService.getBusinessSectorGroups({standardvals,processvals,bsectorvals}).subscribe(res => {
        this.bsectorgroupList = res['bsectorgroups'];
        this.standardForm.patchValue({business_sector_group_id:''});
      });	
    }else{		
      this.bsectorgroupList = [];
      this.standardForm.patchValue({business_sector_group_id:''});		
    }
  }
  getBgsectorList(value,empty=false){}
  getBgsectorgroupList(value,empty=false){}

  ngOnDestroy() {
    this._onDestroy.next();
    this._onDestroy.complete();
  }
  public filteredprocessMulti: ReplaySubject<Process[]> = new ReplaySubject<Process[]>(1);
  private filterProcess() {
    if (!this.processList) {
      return;
    }
    // get the search keyword
    let search = this.sf.processFilterCtrl.value;
    if (!search) {
      this.filteredprocessMulti.next(this.processList.slice());
      return;
    } else {
      search = search.toLowerCase();
    }
    // filter the banks
    this.filteredprocessMulti.next(
      this.processList.filter(p => p.name.toLowerCase().indexOf(search) > -1)
    );
  }
  standardRejectionForm: FormGroup;
  get f() { return this.customerForm.controls; } 
  get asf() { return this.stdfileform.controls; }
  get sf() { return this.standardForm.controls; } 
  get srf() { return this.standardRejectionForm.controls; } 
  get qf() { return this.qualificationForm.controls; } 
  get ef() { return this.experienceForm.controls; } 
  get cf() { return this.cpdForm.controls; } 
  get uf() { return this.userloginForm.controls; } 
  get cerf() { return this.certificateForm.controls; } 

  get df() { return this.declarationForm.controls; }
  get aef() { return this.auditExpForm.controls; }
  get cef() { return this.conExpForm.controls; }
  get bf() { return this.businessForm.controls; }
  get brf() { return this.rejbusinessForm.controls; }
  get drf() { return this.declarationRejectForm.controls; } 
  get daf() { return this.declarationApprovedForm.controls; }

  getStateList(id:number,stateUpdate){
    if(id>0)
    {
      this.countryservice.getStates(id).subscribe(res => {
        this.stateList = res['data'];
        this.customerForm.patchValue({state_id:''});
      });	
    }else{		
      this.stateList = [];
      this.customerForm.patchValue({state_id:''});		
    }
  }

  waitingexamFileNames=[];
  waitingtechnicalInterviewFileNames=[];
  approvedexamFileNames=[];
  approvedtechnicalInterviewFileNames=[];
  
  rejshowRecycle=false;
  rejshowSocial=false;
  cbList:any=[];
  removesocial_examFiles(){}
  std_examfileErrors = '';
  recycle_examfileErrors = '';
  social_exam_fileErrors = '';
  showRecycle=false;
  showSocial=false;
  passportFileErr = '';
  passport_file = '';
  passportfileChange(element) {
    let files = element.target.files;
    this.passportFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.formData.append("passport_file", files[0], files[0].name);
      this.passport_file = files[0].name;
    }else{
      this.passportFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }
  removepassportFile(){
    this.passport_file = '';
    this.formData.delete('passport_file');
  }
  removeacademicFile(){
    this.academic_file = '';
    this.formData.delete('academic_file');
  }

  academicfileChange(element) {
    /* 
       let experienceValIndex=0;
       if(this.experienceIndex >=0 && this.experienceIndex !==null){
         experienceValIndex = this.experienceIndex;
       }else{
         experienceValIndex = this.experienceEntries.length;
       }
       */
     let files = element.target.files;
     
     for (let i = 0; i < files.length; i++) {
      
       let fileextension = files[i].name.split('.').pop();
       if(this.errorSummary.checkValidDocs(fileextension))
       {
         //console.log(this.certificateIndex);
         this.uploadedacademicFileNames[this.certificateIndex]= {name:files[i].name,added:1,deleted:0,valIndex:this.certificateIndex};
       }else{
         this.upload_certificateErrors='Please upload valid files';
         element.target.value = '';
         return false;
       }
     }
     for (let i = 0; i < files.length; i++) {
       this.formData.append("uploads["+this.certificateIndex+"]", files[i], files[i].name);
     }
     element.target.value = '';
     this.upload_certificateErrors = '';
     //console.log(this.formData);
   }
  academicfilterFile(experienceValIndex){
    if(experienceValIndex!==null && this.uploadedacademicFileNames.length>0){
      return this.uploadedacademicFileNames[experienceValIndex];
    }else{
      return null;
    }
  }
  removeacademicFiles(){
    let certValIndex=0;
    if(this.certificateIndex >=0 && this.certificateIndex !==null){
      certValIndex = this.certificateIndex;
    }else{
      certValIndex = this.experienceEntries.length;
    }
    this.uploadedacademicFileNames.splice(certValIndex, 1);
    this.upload_certificateErrors = '';
  }

  contractFileErr = '';
  contract_file = '';
  contractfileChange(element) {
    let files = element.target.files;
    this.contractFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.formData.append("contract_file", files[0], files[0].name);
      this.contract_file = files[0].name;
      
    }else{
      this.contractFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }
  removecontractFile(){
    this.contract_file = '';
    this.formData.delete('contract_file');
  }





  get filterRejectedUser(){
    return this.userListEntriesRejected.filter(x=>x.deleted!=1);
  }
  get filterUser(){
    return this.userListEntries.filter(x=>x.deleted==0);
  }

  removeUser(index:number) {
    //let index= this.qualificationEntries.findIndex(s => s.id ==  productId);
    if(index != -1)
      this.userListEntries[index].deleted =1;
    
    this.userIndex=this.userListEntries.length;
  }
   removeRejectUser(i) {}
  userListEntries = [];
  userIndex:number=0;
  userErrors = '';
  usernameErrors = '';
  role_idErrors = '';
  franchise_idErrors = '';
  addUser(){

    this.userErrors ='';
    
    this.uf.username.markAsTouched();
    this.uf.user_password.markAsTouched();
    this.uf.role_id.markAsTouched();
    this.uf.franchise_id.markAsTouched();

    if(this.userloginForm.valid){
      let username = this.userloginForm.get('username').value;
      let user_password = this.userloginForm.get('user_password').value;
      
      let role_id = this.userloginForm.get('role_id').value;
      let franchise_id = this.userloginForm.get('franchise_id').value;

      let franchise_name= this.franchiseList.find(s => s.id ==  franchise_id).display_company_name;
      let role_name= this.roleList.find(s => s.id ==  role_id).role_name;
      
      let expobject:any=[];
      //expobject["id"] = selproduct.id;
      expobject["username"] = username;
      expobject["user_password"] = user_password;
      expobject["role_name"] = role_name;
      expobject["franchise_name"] = franchise_name;

      expobject["role_id"] = role_id;
      expobject["franchise_id"] = franchise_id;
      expobject["deleted"] = 0;
      expobject["editable"] = 1;
      
      
      
      if(this.userIndex!==null){
      this.userListEntries[this.userIndex] = expobject;
      }else{
        this.userListEntries.push(expobject);
      }
      this.userloginForm.reset();
      this.userIndex=this.userListEntries.length;   
    } 
  }
  editUser(index:number){
    this.userIndex= index;
	  let qual = this.userListEntries[index];
    this.userloginForm.patchValue({
      username: qual.username,
      user_password: qual.user_password,
      role_id: qual.role_id,
      franchise_id: qual.franchise_id
    });
  }

  onRoleSubmit(){

  }
  hideError(){
  }
  onStandardSubmit(){

  }
  rejonRoleSubmit(){}
  onQualificationSubmit(){}
  onExperienceSubmit(){}
  onAudExperienceSubmit(){}
  onConExperienceSubmit(){}
  onCertificateSubmit(){}
  onCpdSubmit(){}
  onDeclarationSubmit(){}
  id:any;

  
  removeAcademic(index:number) {
    //let index= this.qualificationEntries.findIndex(s => s.id ==  productId);
    if(index != -1)
      this.qualificationEntries.splice(index,1);
    
    this.qualificationIndex=this.qualificationEntries.length;
  }
  
  qualificationStatus=true;
  qualificationIndex=0;
  addApprovedDeclaration(){ }
  addAcademic(){
    this.qualificationErrors ='';
    this.qualificationStatus=true;

    this.qf.qualification.markAsTouched();
    this.qf.university.markAsTouched();
    this.qf.subject.markAsTouched();
    this.qf.start_year.markAsTouched();
    this.qf.end_year.markAsTouched();


    let qualification = this.qualificationForm.get('qualification').value;
    let university = this.qualificationForm.get('university').value;
    let subject = this.qualificationForm.get('subject').value;
    let start_year = this.qualificationForm.get('start_year').value;
    let end_year = this.qualificationForm.get('end_year').value;
    let academic_certificate = this.qualificationForm.get('academic_certificate').value;

    
    
    if(this.uploadedacademicFileNames[this.certificateIndex] == undefined || this.uploadedFileNames[this.certificateIndex]==''){
      this.academic_certificateErrors = 'Please upload the Certificate';
      this.qualificationStatus=false;
    }else{
      this.academic_certificateErrors = '';
    }
	    
    //let entry= this.qualificationEntries.find(s => s.id ==  productId);
    if(this.qualificationForm.valid){
      let expobject:any=[];
      expobject["qualification"] = qualification;
      expobject["university"] = university;
      expobject["subject"] = subject;
      expobject["start_year "] = start_year ;
      expobject["end_year"] = end_year;
      let academic_certificate = this.uploadedacademicFileNames[this.certificateIndex].name;
      expobject["academic_certificate"] = academic_certificate;
      
      if(this.qualificationIndex!==null){
      this.qualificationEntries[this.qualificationIndex] = expobject;
      }else{
        this.qualificationEntries.push(expobject);
      }
      this.qualificationForm.reset();
      /* this.f.qualification.setValidators([]);
      this.f.university.setValidators([]);
      this.f.subject.setValidators([]);
      this.f.passingyear.setValidators([]);
      this.f.percentage.setValidators([]);

      this.f.qualification.updateValueAndValidity();
      this.f.university.updateValueAndValidity();
      this.f.subject.updateValueAndValidity();
      this.f.passingyear.updateValueAndValidity();
      this.f.percentage.updateValueAndValidity();
      */

      this.qualificationForm.patchValue({
        qualification: '',
        university: '',
        subject: '',
        start_year: '',
        end_year: ''
      });
      //}
      this.qualificationIndex=this.qualificationEntries.length;
    }
  }
  editAcademic(index:number){
    // let prd= this.qualificationEntries.find(s => s.id ==  productId);
    this.qualificationIndex= index;
	  let qual = this.qualificationEntries[index];
    this.qualificationForm.patchValue({
      qualification: qual.qualification,
      university: qual.university,
      subject: qual.subject,
      start_year: qual.start_year,
      end_year: qual.end_year,
      academic_certificate: qual.academic_certificate 
    });
  }
  
  
  removeExperience(index:number) {
    //let index= this.experienceEntries.findIndex(s => s.id ==  productId);
    if(index != -1)
      this.experienceEntries.splice(index,1);

    this.experienceIndex=this.experienceEntries.length;
  }
  removeaudExperience(index:number) {
    if(index != -1)
      this.auditexperienceEntries.splice(index,1);

    this.auditexperienceIndex=this.auditexperienceEntries.length;
  }
  removeconExperience(index:number) {
    if(index != -1)
      this.consultancyexperienceEntries.splice(index,1);

    this.consultancyexperienceIndex=this.consultancyexperienceEntries.length;
  }
  checkExpDate(datetype){
    this.experienceForm.patchValue({
		  exp_to_date: ''
    });
  }
  experienceStatus=true;
  experienceIndex=0;
  addExperience(){
    this.experienceErrors ='';

    this.experienceStatus=true;  
    let experience = this.experienceForm.get('experience').value;
    let job_title = this.experienceForm.get('job_title').value;
    let responsibility = this.experienceForm.get('responsibility').value;
    let exp_from_date = this.experienceForm.get('exp_from_date').value;
	  let exp_to_date = this.experienceForm.get('exp_to_date').value;
    
    this.ef.experience.markAsTouched();
    this.ef.job_title.markAsTouched();
    this.ef.responsibility.markAsTouched();
    this.ef.exp_from_date.markAsTouched();
    this.ef.exp_to_date.markAsTouched();
    
    /*
    this.f.experience.setValidators([Validators.required]);
    this.f.responsibility.setValidators([Validators.required]);
    this.f.exp_from_date.setValidators([Validators.required]);
	  this.f.exp_to_date.setValidators([Validators.required]);
	
    this.f.experience.markAsTouched();
    this.f.responsibility.markAsTouched();
    this.f.exp_from_date.markAsTouched();
	  this.f.exp_to_date.markAsTouched();

    this.f.experience.updateValueAndValidity();
    this.f.responsibility.updateValueAndValidity();
    this.f.exp_from_date.updateValueAndValidity();
	  this.f.exp_to_date.updateValueAndValidity();

    if(experience.trim()=='' || this.f.experience.errors){
      this.experienceStatus=false;
    }
    if(responsibility.trim()=='' || this.f.responsibility.errors){
      this.experienceStatus=false;
    }
    
    if(exp_from_date=='' || this.f.exp_from_date.errors){
      this.experienceStatus=false;
    }
	
	  if(exp_to_date=='' || this.f.exp_to_date.errors){
      this.experienceStatus=false;
    }
      
    if(!this.experienceStatus)
    {
      return false;
    }
    */
        
    //let entry= this.experienceEntries.find(s => s.id ==  productId);
    let expobject:any=[];
    //expobject["id"] = selproduct.id;
    expobject["experience"] = experience;
    expobject["job_title"] = job_title;
    expobject["responsibility"] = responsibility;
    expobject["exp_from_date"] = this.errorSummary.displayDateFormat(exp_from_date);
	  expobject["exp_to_date"] = this.errorSummary.displayDateFormat(exp_to_date);//this.getDate(exp_to_date);
		          
    if(this.experienceIndex!==null){
      this.experienceEntries[this.experienceIndex] = expobject;
    }else{
      this.experienceEntries.push(expobject);
    }
    this.experienceForm.reset();
    /*this.f.experience.setValidators([]);
    this.f.responsibility.setValidators([]);
    this.f.exp_from_date.setValidators([]);
	  this.f.exp_to_date.setValidators([]);
	
    this.f.experience.updateValueAndValidity();
    this.f.responsibility.updateValueAndValidity();
    this.f.exp_from_date.updateValueAndValidity();
	  this.f.exp_to_date.updateValueAndValidity();
    
    this.experienceForm.patchValue({
      experience: '',
      responsibility:'',
	    exp_from_date: '',
		  exp_to_date: ''
    });
    */
	//}
	  this.experienceIndex=this.experienceEntries.length;
  }
  get urf() { return this.mapUserRoleForm.controls; } 
  downloadFile(fileid,filename){}
  consultancyEditStatus=false;
  cpdEditStatus=false;
  auditexperienceStatus=true;
  auditexperienceIndex=0;
  mapuserroleEntries:any = [];
  mapuserroleIndex:any = 0;
  teRoleListEntriesApproved:any = [];
  rejonteBusinessSubmitConfirm(content,groupid){}
  uniqueRoleListEntriesApproved = [];
  bgUsersectorList:any = [];
  bgUsersectorgroupList:any = [];
  displayPopBtn:any = true;
  mapUserformData:FormData = new FormData();
  onMapUserRoleSubmit(){}
  addaudExperience(){
    this.auditexperienceErrors ='';

    this.auditexperienceStatus=true;  
    let year = this.auditExpForm.get('year').value;
   
    let  bussinessSector = this.auditExpForm.get('business_sector').value;
    let  audit_role = this.auditExpForm.get('audit_role').value;
    let standard = this.auditExpForm.get('standard').value;
    let company = this.auditExpForm.get('company').value;
    let cb = this.auditExpForm.get('cb').value;
    let days = this.auditExpForm.get('days').value;
    let process = this.auditExpForm.get('process').value;
    
    this.aef.business_sector.markAsTouched();
    this.aef.audit_role.markAsTouched();
    this.aef.year.markAsTouched();
    this.aef.standard.markAsTouched();
    this.aef.company.markAsTouched();
    this.aef.cb.markAsTouched();
    this.aef.days.markAsTouched();
    this.aef.process.markAsTouched();
    
    let selstandard = this.standardList.find(s => s.id ==  standard);
        
    //let entry= this.experienceEntries.find(s => s.id ==  productId);
    let expobject:any=[];
    //expobject["id"] = selproduct.id;
    expobject["standard"] = selstandard.id;
    expobject["bussinessSector"] = bussinessSector;
    expobject["audit_role"] = audit_role;
    expobject["standard_name"] = selstandard.name;
    expobject["process"] = process;
    expobject["company"] = company;
    expobject["year"] = year;
    expobject["cb"] = cb;
	  expobject["days"] = days;//this.getDate(exp_to_date);
		          
    if(this.auditexperienceIndex!==null){
      this.auditexperienceEntries[this.auditexperienceIndex] = expobject;
    }else{
      this.auditexperienceEntries.push(expobject);
    }
    this.auditExpForm.reset();
    
	  this.auditexperienceIndex=this.auditexperienceEntries.length;
  }

  

  consultancyexperienceStatus=true;
  consultancyexperienceIndex=0;
  addconExperience(){
    this.consultancyexperienceErrors ='';

    this.auditexperienceStatus=true;  
    let year = this.conExpForm.get('year').value;
    let standard = this.conExpForm.get('standard').value;
    let company = this.conExpForm.get('company').value;
    let days = this.conExpForm.get('days').value;
    let process = this.conExpForm.get('process').value;
    
    
    this.cef.year.markAsTouched();
    this.cef.standard.markAsTouched();
    this.cef.company.markAsTouched();
    this.cef.days.markAsTouched();
    this.cef.process.markAsTouched();
    
    let selstandard = this.standardList.find(s => s.id ==  standard);
        
    //let entry= this.experienceEntries.find(s => s.id ==  productId);
    let expobject:any=[];
    //expobject["id"] = selproduct.id;
    expobject["standard"] = selstandard.id;
    expobject["standard_name"] = selstandard.name;
    expobject["process"] = process;
    expobject["company"] = company;
    expobject["year"] = year;
	  expobject["days"] = days;//this.getDate(exp_to_date);
		          
    if(this.consultancyexperienceIndex!==null){
      this.consultancyexperienceEntries[this.consultancyexperienceIndex] = expobject;
    }else{
      this.consultancyexperienceEntries.push(expobject);
    }
    this.conExpForm.reset();
    
	  this.consultancyexperienceIndex=this.consultancyexperienceEntries.length;
  }
  
  editExperience(index:number){
   // let prd= this.experienceEntries.find(s => s.id ==  productId);
   this.experienceIndex= index;
	  let qual = this.experienceEntries[index];
    this.experienceForm.patchValue({
      experience: qual.experience,
      job_title: qual.job_title,
      responsibility: qual.responsibility,
	    exp_from_date: this.errorSummary.editDateFormat(qual.exp_from_date),
		  exp_to_date: this.errorSummary.editDateFormat(qual.exp_to_date)
    });
  }

  editauditExperience(index:number){
    this.auditexperienceIndex = index;
     let audexp = this.auditexperienceEntries[index];
     this.auditExpForm.patchValue({
       year: audexp.year,
       standard: audexp.standard,
       company: audexp.company,
       cb: audexp.cb,
       audit_role: audexp.auditrolelist_id?audexp.auditrolelist_id:"",
       days: audexp.days,
       process: audexp.process
     });
  }

  editconsultancyExperience(index:number)
  {
    this.consultancyexperienceIndex = index;
    let conexp = this.consultancyexperienceEntries[index];
    this.conExpForm.patchValue({
      year: conexp.year,
      standard: conexp.standard,
      company: conexp.company,
      days: conexp.days,
      process: conexp.process
    });
  }

  
  
  // Certificate Details Code Start Here
  uploadedFileNames=[];
  cert_error = '';
  fileChange(element) {
   /* 
      let experienceValIndex=0;
      if(this.experienceIndex >=0 && this.experienceIndex !==null){
        experienceValIndex = this.experienceIndex;
      }else{
        experienceValIndex = this.experienceEntries.length;
      }
      */
    let files = element.target.files;
    
    for (let i = 0; i < files.length; i++) {
     
      let fileextension = files[i].name.split('.').pop();
      if(this.errorSummary.checkValidDocs(fileextension))
      {
        //console.log(this.certificateIndex);
        this.uploadedFileNames[this.certificateIndex]= {name:files[i].name,added:1,deleted:0,valIndex:this.certificateIndex};
        //experienceValIndex:experienceValIndex
        //this.uploadedFileNames.splice(this.certificateIndex,1,{name:files[i].name,added:1,deleted:0,valIndex:this.certificateIndex});
        //this.uploadedFileNames.push({name:files[i].name,added:1,deleted:0,experienceValIndex:experienceValIndex});
      }else{
        this.upload_certificateErrors='Please upload valid files';
        element.target.value = '';
        return false;
      }
    }
    for (let i = 0; i < files.length; i++) {
      this.formData.append("uploads["+this.certificateIndex+"]", files[i], files[i].name);
    }
    element.target.value = '';
    this.upload_certificateErrors = '';
    //console.log(this.formData);
  }
  filterFile(experienceValIndex){
    if(experienceValIndex!==null && this.uploadedFileNames.length>0){
      //return this.uploadedFileNames.find(x=>x.experienceValIndex ==experienceValIndex && x.deleted==0 );
      return this.uploadedFileNames[experienceValIndex];
    }else{
      return null;
    }
  }
  removeFiles(){
    let certValIndex=0;
    if(this.certificateIndex >=0 && this.certificateIndex !==null){
      certValIndex = this.certificateIndex;
    }else{
      certValIndex = this.experienceEntries.length;
    }
    this.uploadedFileNames.splice(certValIndex, 1);
    //this.uploadedFileNames[experienceValIndex];
    //let filenames =   this.uploadedFileNames[experienceValIndex];
    //this.uploadedFileNames = filenames;
    this.upload_certificateErrors = '';
  }

  removeCertificate(index:number) {
    if(index != -1)
      this.certificateEntries.splice(index,1);
    this.uploadedFileNames.splice(index, 1);
    this.certificateIndex= this.certificateEntries.length;
  }
  
  certificateStatus=true;
  certificateIndex=0;
  addCertificate(){
    this.certificateErrors ='';
    this.cf.certificate_name.markAsTouched();
    this.cf.completed_date.markAsTouched();
	this.cf.training_hours.markAsTouched();
	
    /*this.cf.certificate_name.setValidators([Validators.required]);
    this.cf.completed_date.setValidators([Validators.required]);
    this.cf.certificate_name.markAsTouched();
    this.cf.completed_date.markAsTouched();

    this.cf.certificate_name.updateValueAndValidity();
    this.cf.completed_date.updateValueAndValidity();
    */

    this.certificateStatus=true;
    let certificate_name = this.certificateForm.get('certificate_name').value;
	let training_hours = this.certificateForm.get('training_hours').value;
	let completed_date = this.errorSummary.displayDateFormat(this.certificateForm.get('completed_date').value);//
    let upload_certificate = this.certificateForm.get('upload_certificate').value;
    /*
    if(certificate_name.trim()=='' || this.f.certificate_name.errors){
      //this.certificate_nameErrors = 'Please select the Certifficate Name';
      this.certificateStatus=false;
    }else{
      //this.certificate_nameErrors = '';
    }
    
    if(completed_date=='' || this.f.completed_date.errors){
          //this.completed_dateErrors = 'Please select the Completed Date';
      this.certificateStatus=false;
      }else{
      //this.completed_dateErrors = '';
    }
    */
   	if(this.uploadedFileNames[this.certificateIndex] == undefined || this.uploadedFileNames[this.certificateIndex]==''){
      this.upload_certificateErrors = 'Please upload the Certificate';
      this.certificateStatus=false;
    }else{
      this.upload_certificateErrors = '';
    }
    /*
    if(!this.certificateStatus)
    {
      return false;
    }
    */
        
      //let entry= this.certificateEntries.find(s => s.id ==  productId);
    let expobject:any=[];
    expobject["certificate_name"] = certificate_name;
	expobject["training_hours"] = training_hours;	
    expobject["completed_date"] = completed_date;
    let filename = this.uploadedFileNames[this.certificateIndex].name;
    expobject["filename"] = filename;
          
    if(this.certificateIndex!==null){
      this.certificateEntries[this.certificateIndex] = expobject;
    }else{
      this.certificateEntries.push(expobject);
    }
    this.certificateForm.reset();
    /*
    this.f.certificate_name.setValidators([]);
    this.f.completed_date.setValidators([]);
    this.f.certificate_name.updateValueAndValidity();
    this.f.completed_date.updateValueAndValidity();
    
      this.customerForm.patchValue({
        certificate_name: '',
        completed_date: '',
        //upload_certificate: ''
      });
    */
    this.certificateIndex= this.certificateEntries.length;
  }
  
  editCertificate(index:number){
    // let prd= this.certificateEntries.find(s => s.id ==  productId);
    this.certificateIndex= index;
	  let qual = this.certificateEntries[index];
    this.certificateForm.patchValue({
      certificate_name: qual.certificate_name,
	  training_hours : qual.training_hours,
      completed_date: this.errorSummary.editDateFormat(qual.completed_date),
      filename: qual.filename	 	  
    });
  }
  //Certificate Details Code End Here

  
  examFileNames=[];
  technicalInterviewFileNames=[];
  uploadexamErrors='';
  uploadtechnicalErrors='';
  
  businessFileChange(element,type) {}
  businessfilterFile(experienceValIndex,type){}
  businessremoveFiles(type){}
  removeBusiness(index:number) {}
  businessStatus=true;
  businessIndex=0;

  examfileStatus=false;
  technicalInterviewFileStatus=false;
  sameStandardError = '';
  addBusiness(){}
  editBusiness(index:number){}
  
  // Training Details Code Start Here
  removeTraining(index:number) {
    //let index= this.trainingEntries.findIndex(s => s.id ==  productId);
    if(index != -1)
      this.trainingEntries.splice(index,1);
    this.trainingIndex=this.trainingEntries.length;
  }
  
  trainingStatus=true;
  trainingIndex=0;

  addTraining(){
    this.trainingErrors ='';

    this.trainingStatus=true;
    let training_subject = this.cpdForm.get('training_subject').value;
    let training_date = this.cpdForm.get('training_date').value;
    let training_hours = this.cpdForm.get('training_hours').value;
    
    this.f.training_subject.markAsTouched();
    this.f.training_date.markAsTouched();
    this.f.training_hours.markAsTouched();

    /*
    this.f.training_subject.setValidators([Validators.required]);
    this.f.training_date.setValidators([Validators.required,Validators.pattern('^[0-9]{4}$'),Validators.min(1900)]);
    this.f.training_hours.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]);
    
    this.f.training_subject.markAsTouched();
    this.f.training_date.markAsTouched();
    this.f.training_hours.markAsTouched();
    this.f.training_subject.updateValueAndValidity();
    this.f.training_date.updateValueAndValidity();
    this.f.training_hours.updateValueAndValidity();


    if(training_subject.trim()=='' || this.f.training_subject.errors){
      //    this.training_subjectErrors = 'Please select the Subject';
      this.trainingStatus=false;
    }
    if(this.f.training_hours.errors || training_hours.trim()==''){
      this.trainingStatus=false;
    }
    if(this.f.training_date.errors || training_date.trim()==''){
      //    this.training_dateErrors = 'Please select the Date';
      this.trainingStatus=false;
    }
    
    if(!this.trainingStatus)
    {
      return false;
    }
      
    */
    //let entry= this.trainingEntries.find(s => s.id ==  productId);
    let expobject:any=[];
    //expobject["id"] = selproduct.id;
    expobject["training_subject"] = training_subject;
    expobject["training_hours"] = training_hours;
    expobject["training_date"] = training_date;
    	  
    if(this.trainingIndex!==null){
      this.trainingEntries[this.trainingIndex] = expobject;
    }else{
      this.trainingEntries.push(expobject);
    }
    this.cpdForm.reset();
    /*
    this.f.training_subject.setValidators([]);
    this.f.training_date.setValidators([]);
    this.f.training_hours.setValidators([]);
    this.f.training_subject.updateValueAndValidity();
    this.f.training_hours.updateValueAndValidity();
    this.f.training_date.updateValueAndValidity();

    this.customerForm.patchValue({
      training_subject: '',
      training_date: '',
      training_hours:''
    });
    */
	  //}
	  this.trainingIndex=this.trainingEntries.length;
  }
  
  editTraining(index:number){
    // let prd= this.trainingEntries.find(s => s.id ==  productId);
    this.trainingIndex= index;
	  let qual = this.trainingEntries[index];
    this.cpdForm.patchValue({
      training_subject: qual.training_subject,
      training_date: qual.training_date,
      training_hours: qual.training_hours
    });
  }
  //Training Details Code End Here
  
  
  //Declaration Details Code Start Here
  removeDeclaration(index:number) {
    if(index != -1)
      this.declarationEntries.splice(index,1);
    this.declarationIndex=this.declarationEntries.length;
  }
  
  declarationStatus=true;
  declarationIndex=0;

  addDeclaration(){
    this.declarationErrors ='';

    this.declarationStatus=true;
    let declaration_company = this.declarationForm.get('declaration_company').value;
    let declaration_contract = this.declarationForm.get('declaration_contract').value;
    let declaration_interest = this.declarationForm.get('declaration_interest').value;
	let declaration_start_year = this.declarationForm.get('declaration_start_year').value;
    let declaration_end_year = this.declarationForm.get('declaration_end_year').value;
    
    this.df.declaration_company.markAsTouched();
    this.df.declaration_contract.markAsTouched();
    this.df.declaration_interest.markAsTouched();
	this.df.declaration_start_year.markAsTouched();
	this.df.declaration_end_year.markAsTouched();
    
    let expobject:any=[];
    expobject["declaration_company"] = declaration_company;
    expobject["declaration_contract"] = declaration_contract;
    expobject["declaration_interest"] = declaration_interest;
	expobject["declaration_start_year"] = declaration_start_year;
	expobject["declaration_end_year"] = declaration_end_year;
    	  
    if(this.declarationIndex!==null){
      this.declarationEntries[this.declarationIndex] = expobject;
    }else{
      this.declarationEntries.push(expobject);
    }
    this.declarationForm.reset();
	
    this.declarationIndex=this.declarationEntries.length;
  }
  
  editDeclaration(index:number){
    this.declarationIndex= index;
	let qual = this.declarationEntries[index];
    this.declarationForm.patchValue({
		declaration_company: qual.declaration_company,
		declaration_contract: qual.declaration_contract,
		declaration_interest: qual.declaration_interest,
		declaration_start_year: qual.declaration_start_year,
		declaration_end_year: qual.declaration_end_year
    });
  }
  //Declaration Details Code End Here


  get filterRejectedDeclaration(){
    return this.declaration_rejectedEntries.filter(x=>x.deleted!=1);
  }
  //Reject Declaration Details Code Start Here
  removeRejectDeclaration(index:any) {
 
  }
  
  //declarationStatus=true;
  declarationRejectIndex='';

  addRejectDeclaration(){
  
  }
  showdecForm = false;
  showrelation =true;
  showspwork=true;
  editRejectDeclaration(index:any){
    
  }
  //Reject Declaration Details Code End Here



  onChange(id: number, isChecked: boolean) {
    const standardsFormArray = <FormArray>this.customerForm.get('standardsChk');

    if (isChecked) {
      standardsFormArray.push(new FormControl(id));
    } else {
      let index = standardsFormArray.controls.findIndex(x => x.value == id);
      standardsFormArray.removeAt(index);
    }
    this.standardsLength = this.customerForm.get('standardsChk').value.length;
  }

  onPersonnelSubmit(){
    this.passportFileErr = '';
    this.contractFileErr = '';
    if(this.passport_file ==''){
      this.passportFileErr = 'Please upload Passport/ID';
    }

    if(this.contract_file ==''){
      this.contractFileErr = 'Please upload Member Agreement/Contract';
    }
     
    if (this.customerForm.valid && this.passportFileErr =='' && this.contractFileErr =='') {
	 
      this.loadingArr['personnel'] = true;
	  
      
      /*let qualificationdatas = [];
      this.qualificationEntries.forEach((val)=>{
        qualificationdatas.push({qualification:val.qualification,board_university:val.university,subject:val.subject,passing_year:val.passingyear,percentage:val.percentage})
      });
      
      let experiencedatas = [];
      this.experienceEntries.forEach((val)=>{
        experiencedatas.push({experience:val.experience,responsibility:val.responsibility,from_date:val.exp_from_date,to_date:val.exp_to_date})
      });
          
      let certificationdatas = [];
        this.certificateEntries.forEach((val)=>{
          certificationdatas.push({certificate_name:val.certificate_name,completed_date:val.completed_date,filename:val.filename})
        });
            
      let trainingdatas = [];
        this.trainingEntries.forEach((val)=>{
          trainingdatas.push({subject:val.training_subject,training_hours:val.training_hours,training_date:val.training_date})
        });   
      
      let formvalue = this.customerForm.value;
      formvalue.qualifications = [];
      formvalue.experience = [];
      formvalue.certifications = [];
      formvalue.training_info = [];
      
      formvalue.qualifications = qualificationdatas;
      formvalue.experience = experiencedatas;
      formvalue.certifications = certificationdatas;
      formvalue.training_info = trainingdatas;
      */
      let formvalue = this.customerForm.value;
      formvalue.actiontype = 'personnel';
      this.formData.append('formvalues',JSON.stringify(formvalue));
      

      this.userService.addData(this.formData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){

			      this.success = {summary:res.message};
				    this.buttonDisable = true;
			      setTimeout(() => {
              //this.router.navigateByUrl('/master/user/list');
              //this.router.navigateByUrl('/master/user/view?id='+res.user_id)
              this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
            
            //this.submittedSuccess =1;
          }else if(res.status == 0){
            //this.submittedError =1;
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.customerForm);	
          }else{
            //this.submittedError =1;
            this.error = {summary:res};
          }
          this.loadingArr['personnel'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['personnel'] = false;
      });
      //console.log('sdfsdfdf');
    } else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.customerForm); 
    }
  }
  bgroupcodeData:any=[];
  stdhistoryData:any=[];
  dechistoryData:any=[];
  panelOpenState = true;
  declarationformData:FormData = new FormData();
  onRejectDeclarationSubmit(){}
  declarationStarYearChange(element) {}
  downloadacademicFile(fileid,filename){}
  academicStarYearChange(element) {}
  showApprovedStdDetails(content,row_id)
  {
    this.stdData = this.userData.standard_approved[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }
  showRejectedDecHistoryDetails(content,row_id){}
  showRejectedbgroupHistoryDetails(content,row_id,datalist){}
  showRejectedStdDetails(content,row_id)
  {
    this.stdData = this.userData.standard_rejected[row_id];
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
    this.bgroupData = this.userData.businessgroup_approved[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedbgroupDetails(content,row_id,coderow)
  {
    this.bgroupData = this.userData.businessgroup_rejected[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showApprovedRoleDetails(content,row_id)
  {
    this.roleData = this.userData.role_id_approved[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedRoleDetails(content,row_id)
  {
    this.roleData = this.userData.role_id_approved[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  stdfileData:any=[];
  editStandardFiles(content,row_id)
  {}

  bgroupfileData:any=[];
  editBgroupDetails(content,row_id)
  {}

  approved_examfileErrors = '';
  approved_examfilename = '';
  bgroupfileformData:FormData = new FormData();
  approved_examfilenameChange(element) 
  {}
  removeapproved_examfilename(){}

  approved_technicalFileErrors = '';
  approved_technicalfilename = '';
  approved_technicalfilenameChange(element) 
  {}
  removeapproved_technicalfilename(){}


  approved_std_examfileErrors = '';
  approved_std_exam_file = '';
  approved_qua_exam_file='';
  stdfileformData:FormData = new FormData();
  approved_std_examfileChange(element) {}
  removeapproved_std_examFiles(){}

  approved_recycle_examFileErrors = '';
  approved_recycle_exam_file = '';
  approved_recycle_examfileChange(element) {}
  removeapproved_recycle_examFiles(){}

  approved_witness_fileErrors = '';
  approved_witness_file = '';
  approved_witnessfileChange(element) {}
  removeapproved_std_witnessFiles(){}


  approved_social_examFileErrors = '';
  approved_social_exam_file = '';
  approved_social_examfileChange(element) {}
  removeapproved_social_examFiles(){}

  SaveStdFileChanges(){}

  SaveBgroupFileChanges(){}

  

  onSubmit(){
    
    this.f.certificate_name.setValidators([]);
	this.f.training_hours.setValidators([]);
    this.f.completed_date.setValidators([]);
    this.f.certificate_name.updateValueAndValidity();
    this.f.completed_date.updateValueAndValidity();
	
	/*
    this.f.training_subject.setValidators([]);
    this.f.training_date.setValidators([]);
    this.f.training_hours.setValidators([]);
    this.f.training_subject.updateValueAndValidity();
    this.f.training_date.updateValueAndValidity();
    this.f.training_hours.updateValueAndValidity();
	*/
    
    this.f.experience.setValidators([]);
    this.f.job_title.setValidators([]);
    this.f.responsibility.setValidators([]);
    this.f.exp_from_date.setValidators([]);
	this.f.exp_to_date.setValidators([]);
	
    this.f.experience.updateValueAndValidity();
    this.f.job_title.updateValueAndValidity();
    this.f.responsibility.updateValueAndValidity();
    this.f.exp_from_date.updateValueAndValidity();
    this.f.exp_to_date.updateValueAndValidity();
    
    this.f.standard.setValidators([]);
    this.f.year.setValidators([]);
    this.f.company.setValidators([]);
    this.f.process.setValidators([]);
    this.f.cb.setValidators([]);
    this.f.audit_role.setValidators([]),
    this.f.days.setValidators([]);
	
    this.f.standard.updateValueAndValidity();
    this.f.year.updateValueAndValidity();
    this.f.company.updateValueAndValidity();
    this.f.process.updateValueAndValidity();
    this.f.cb.updateValueAndValidity();
    this.f.audit_role.updateValueAndValidity();
    this.f.days.updateValueAndValidity();
    
    this.f.qualification.setValidators([]);
    this.f.university.setValidators([]);
    this.f.subject.setValidators([]);
    this.f.passingyear.setValidators([]);
    this.f.percentage.setValidators([]);

    this.f.qualification.updateValueAndValidity();
    this.f.university.updateValueAndValidity();
    this.f.subject.updateValueAndValidity();
    this.f.passingyear.updateValueAndValidity();
    this.f.percentage.updateValueAndValidity();

    this.qualificationErrors ='';
    this.experienceErrors ='';
    this.certificateErrors ='';
    this.auditexperienceErrors ='';
    this.trainingErrors ='';

    if(this.qualificationEntries.length<=0){
      this.qualificationErrors ='true';
    }
    if(this.experienceEntries.length<=0){
      this.experienceErrors ='true';
    }
    if(this.auditexperienceEntries.length<=0){
      this.auditexperienceErrors ='true';
    }
    if(this.certificateEntries.length<=0){
      this.certificateErrors ='true';
    }
    if(this.trainingEntries.length<=0){
      this.trainingErrors ='true';
    }
    //console.log(this.f.standard.value);
    //return false;
    //if (this.customerForm.valid) {
    if (this.customerForm.valid && this.qualificationEntries.length>0 && this.experienceEntries.length>0 && this.certificateEntries.length>0
        && this.trainingEntries.length>0) {
	 
      this.loading = true;
	  
	  //qualificationEntries experienceEntries certificateEntries trainingEntries
	  
	  let qualificationdatas = [];
    this.qualificationEntries.forEach((val)=>{
      qualificationdatas.push({qualification:val.qualification,board_university:val.university,subject:val.subject,passing_year:val.passingyear,percentage:val.percentage})
    });
	  
	  let experiencedatas = [];
    this.experienceEntries.forEach((val)=>{
      experiencedatas.push({experience:val.experience,job_title:val.job_title,responsibility:val.responsibility,from_date:val.exp_from_date,to_date:val.exp_to_date})
    });

    let audexperiencedatas = [];
    this.auditexperienceEntries.forEach((val)=>{
      audexperiencedatas.push({standard:val.standard,year:val.year,company:val.company,cb:val.cb,audit_role:val.audit_role,days:val.days,process:val.process})
    });
	  	  
	  let certificationdatas = [];
      this.certificateEntries.forEach((val)=>{
        certificationdatas.push({certificate_name:val.certificate_name,training_hours:val.training_hours,completed_date:val.completed_date,filename:val.filename})
      });
	  	 	  
	  let trainingdatas = [];
      this.trainingEntries.forEach((val)=>{
        trainingdatas.push({subject:val.training_subject,training_hours:val.training_hours,training_date:val.training_date})
      });   
	  
	  let formvalue = this.customerForm.value;
	  formvalue.qualifications = [];
    formvalue.experience = [];
    formvalue.audit_experience = [];
	  formvalue.certifications = [];
	  formvalue.training_info = [];
	  
    formvalue.qualifications = qualificationdatas;
    formvalue.experience = experiencedatas;
    formvalue.audit_experience = audexperiencedatas;
	  formvalue.certifications = certificationdatas;
    formvalue.training_info = trainingdatas;
	  
	  this.formData.append('formvalues',JSON.stringify(formvalue));
		
    this.userService.addData(this.formData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){

			      this.success = {summary:res.message};
				    this.buttonDisable = true;
			      setTimeout(() => {
              //this.router.navigateByUrl('/master/user/list');
              this.router.navigateByUrl('/master/user/view?id='+res.user_id)
            }, this.errorSummary.redirectTime);
            
            //this.submittedSuccess =1;
          }else if(res.status == 0){
            //this.submittedError =1;
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.customerForm);	
          }else{
            //this.submittedError =1;
            this.error = {summary:res};
          }
          this.loading = false;
      },
      error => {
          this.error = error;
          this.loading = false;
      });
      //console.log('sdfsdfdf');
    } else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.customerForm); 
      
    }
  }
  businessformData:FormData = new FormData();
  onBusinessSubmit(){}
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
	  }		
  }	  



  //Standard Code Starts Here
  removeRejectedStandard(index:number) {
   
  }
  
  
  //onStandardSubmit
  rejstdformData:FormData = new FormData();
  rejstd_examFileErr = '';
  rejstd_exam_file = '';
  rejstd_examfileChange(element) {
    
  }
  rejremovestd_examFiles(){
    
  }

  rejqua_examfileErrors='';
  rejqua_exam_file='';

 

  
  rejqua_examFileErr = '';
  
  rejqua_examfileChange(element) {
    
  }
  rejremovequa_examFiles(){
    
  }
  rejwitness_fileErrors = '';
  rejwitness_file = '';
  rejwitnessfileChange(element) {
    
  }
  rejremovestd_witnessFiles(){
    
  }
  
  removeapproved_qua_examFiles(){

  }

  rejrecycle_examFileErr = '';
  rejrecycle_exam_file = '';
  rejrecycle_examfileChange(element) {
    
  }
  rejremoverecycle_examFiles(){
    
  }
  rejremovesocial_examFiles(){
    
  }

  approved_qua_exam_fileErrors='';
  approved_qua_examfileChange(element){

  }
  
  rejsocial_examFileErr = '';
  rejsocial_exam_file = '';
  rejsocial_examfileChange(element) {
    
  }
  rejremoveresocial_examFiles(){
    
  }

  rejstd_examfileErrors='';
  rejrecycle_examfileErrors = '';
  rejsocial_exam_fileErrors='';
  onRejectionStandardSubmit(){
    
  }
  rejStandardIndex:any;
  stdrejdetails:any;
  editStandardRejection(index:number){
    
  }
  //Standard Code Ends Here







  // Rejected Business Sector Details Code Start Here
  rejexamFileNames=[];
  rejtechnicalInterviewFileNames=[];
  rejuploadexamErrors='';
  rejuploadtechnicalErrors='';
  rejbusinessIndex:any;
  rejbusinessFileChange(element,type) {
  }
  rejbusinessfilterFile(experienceValIndex,type){
  }
  rejbusinessremoveFiles(type){
    
  }




  
  rejremoveBusiness(index:number) {
    
  }
  
  rejbusinessStatus=true;
  

  rejexamfileStatus=false;
  rejtechnicalInterviewFileStatus=false;
  rejsameStandardError = '';

  businessEditStatus=false;
  experienceEditStatus=false;
  declarationEditStatus=false;
  teBusinessGroupEditStatus=false;
  roleEditStatus=false;
  qualificationEditStatus=false;
  
  mapbusinessEditStatus:any=false;
  technicalExpertBgSectorList:BusinessSector[];
  technicalExpertBgSectorgroupList:BusinessSectorGroup[];

  editMapBusiness(index:number){
  }
  rejaddBusiness(){

    
  }
  showappdecForm = false;
  showapprelation =true;
  showappspwork=true;
  editApprovedDeclaration(index:any){} 

  brejdetail:any='';
  rejeditBusiness(index:number){
    
  }

  rejbusinessformData:FormData = new FormData();
  rejonBusinessSubmit(){
    
  }

  teBusinessformData:FormData = new FormData();
  onTeBusinessSubmit(){}
  audexperienceEditStatus=false;
  tebgroupData:any=[];
  tebgroupcodeData:any=[];

  model: any = {id:null,action:null};
  alertInfoMessage:any = '';
  modalOptions:NgbModalOptions;
  cancelBtn:any= true;
  okBtn:any = true;
  alertSuccessMessage:any = '';
  alertErrorMessage:any = '';
  rejonteBusinessSubmit(){}
  getUserBgsectorList(value,empty=false){}
  getUserBgsectorgroupList(value:any=0,empty=false){}
  //Rejected Business sector Details Code End Here
  rejtebusinessformData:FormData = new FormData();
  declarationapprovedformData:FormData = new FormData();
  regroupid:any='';
  roleButtonLoad = false;
  openConfirm(content,action,id,actiontype,bscodeid='') {}
  resetUserForm(type)
  {}
  commonModalAction(){}
  teBusinessIndex:any = 0;
  editTeBusiness(index:number){}
  getTeBgsectorgroupList(value='',empty=false,editid:any=0){}

  get tebsf() { return this.technicalExpertBsForm.controls; } 

  teBusinessEntries:any=[];
  teBusiness_approvalwaitingEntries:any=[];
  teBusiness_approvedEntries:any=[];
  teBusiness_rejectedEntries:any=[];
  tegpcoderow:any;
  showApprovedtebgroupDetails(content,row_id,coderow)
  {
    this.tegpcoderow= coderow;
    this.tebgroupData = this.teBusiness_approvedEntries[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedtebgroupDetails(content,row_id,coderow)
  {
    this.tegpcoderow= coderow;
    this.tebgroupData = this.teBusiness_rejectedEntries[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showApprovedtebgroupHistoryDetails(content,row_id,datalist)
  {
    this.tebgroupcodeData = datalist; //this.business_approvedEntries[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedtebgroupHistoryDetails(content,row_id,datalist)
  {
    this.tebgroupcodeData = datalist; //this.business_rejectedEntries[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  rejuser_role_id:any='';
  commonformdata:FormData = new FormData();
  dataprocessing:any = 0;
  approved_business_group_status=true;
  new_business_group_status=false;
  technicalExpertApprovedBsForm:FormGroup;
  TEexamFileNames:any='';
  TEtechnicalInterviewFileNames:any='';
  uploadTEexamErrors='';
  uploadTEtechnicalErrors='';
  rejTEexamfileStatus=false;
  rejTEtechnicalInterviewFileStatus=false;
  tebrejdetail:any='';
  terejexamFileNames:any='';
  technicalExpertApprovedBgSectorList:BusinessSector[];
  technicalExpertApprovedBgSectorgroupList:BusinessSectorGroup[];
  approvedteBusinessGroupEditStatus = false;
  rejTEbusinessForm: FormGroup;

  rejTEexamFileNames='';
  rejTEtechnicalInterviewFileNames='';
  rejTEuploadexamErrors='';
  rejTEuploadtechnicalErrors='';
  rejTEbusinessIndex:any;

  terejeditBusiness(index:number){}
  get teapprovedbsf() { return this.technicalExpertApprovedBsForm.controls; } 
  get terf() { return this.rejTEbusinessForm.controls; }
  rejRoleSubmitConfirm(content,user_role_id){}
  rejRoleSubmit(){}
  downloadtebgroupFile(fileid,filetype,filename){}
  changeTEUserTab(arg){}

  TebusinessremoveFiles(type){}
  rejTEaddBusiness(){}
  getTeApprovedBgsectorgroupList(value='',empty=false,editid:any=0){}
  onApprovedTeBusinessSubmit(){}
  TebusinessFileChange(element,type) {}
  rejTEbusinessremoveFiles(type){}
  rejTEbusinessFileChange(element,type) {}
  
  titlename = '';
  openreviewmodel(content,action,id,titlename,userEntry:any='') 
  {}
  get raf() { return this.form.controls; } 
  changeUserRoleStatus(val)
  {
	  if(val==2)
	  {
		  this.roleApproveStatus=true;
	  }else{
		  this.roleApproveStatus=false;
	  }
	  
  }

  status_error=false;
  comment_error = false;
  witness_date_error = '';
  valid_until_error = '';
  witness_comment_error = '';
  commonReviewAction(approvaltype=''){
  }
  checkUserSel()
  {}

  popupbtnDisable=false;
  resetBtn()
  {}

  witnessdate_change(witness_date:any){
    
    if(witness_date !=''){
    let wtdate= new Date(witness_date);
     
      let newdate = new Date(wtdate.setFullYear(wtdate.getFullYear() + 3));
     
      newdate = new Date(newdate.setDate(newdate.getDate() - 1));
      
      this.model.valid_until = this.errorSummary.editDateFormat(newdate);
      
    }
  }

  documentFileErr = '';
  documents:any='';
  documentChange(element) 
  {
  }

  documentfilterFile(ValIndex)
  {
    if(ValIndex!==null && this.documents.length>0){
      return this.documents[ValIndex];
    }else{
      return null;
    }
  }
  
  documentremoveFile()
  {
    this.documents = '';
    this.documentFileErr = '';
  }
  downloaddocumentFile(fileid,filename){
  }
  witnesspopup_fileErrors = '';
  removepopup_std_witnessFiles(){}
  witnessfileChange_popup(element) {}

  get bcsf() { return this.bgroupfileform.controls; }

  bsectorgroupcodeid:number;
  editBgroupDateDetails(content,row_id,codeid,date)
  {
  }
  bgroupdateform: FormGroup;
  get bsf() { return this.bgroupdateform.controls; }
  SaveBgroupDateChanges()
  {}
}
