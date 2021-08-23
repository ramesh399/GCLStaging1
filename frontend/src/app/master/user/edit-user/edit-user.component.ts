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
import { CbService } from '@app/services/master/cb/cb.service';

//import { Country } from '@app/services/country';
import { Country } from '@app/models/master/country';
import { UserRole } from '@app/models/master/userrole';
import { User } from '@app/models/master/user';
import { Process } from '@app/models/master/process';
import { BusinessSector } from '@app/models/master/business-sector';
import { BusinessSectorGroup } from '@app/models/master/business-sector-group';

import { State } from '@app/services/state';
import { Standard } from '@app/services/standard';
import { first,tap,takeUntil } from 'rxjs/operators';
import { Subject,ReplaySubject } from 'rxjs';
import { AuthenticationService } from '@app/services/authentication.service';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import { Relation } from '@app/models/master/relation';

@Component({
  selector: 'app-edit-user',
  templateUrl: '../add-user/add-user.component.html',
  styleUrls: ['./edit-user.component.scss']
})
export class EditUserComponent implements OnInit {
  title = 'Edit User';
  btnLabel = 'Update';
  countryList:Country[];
  stateList:State[];
  standardList:Standard[];
  cbList:any=[];
  processList:Process[];
  bsectorList:BusinessSector[];
  bsectorgroupList:BusinessSectorGroup[];

  bgsectorList:BusinessSector[];
  bgsectorgroupList:BusinessSectorGroup[];
  
  technicalExpertBgSectorList:BusinessSector[];
  technicalExpertBgSectorgroupList:BusinessSectorGroup[];

  technicalExpertApprovedBgSectorList:BusinessSector[];
  technicalExpertApprovedBgSectorgroupList:BusinessSectorGroup[];
  

  bgUsersectorList:any = [];
  bgUsersectorgroupList:any = [];

  relationEntries:Relation[]=[];
  rejrelationEntries:Relation[]=[];
  roleList:UserRole[]=[];
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
  
  stdData:any=[];
  decData:any=[];
  roleData:any=[];
  bgroupData:any=[];
  bgroupcodeData:any=[];

  tebgroupData:any=[];
  tebgroupcodeData:any=[];

  stdhistoryData:any=[];
  dechistoryData:any=[];
  businessEntries:any=[];
  business_approvalwaitingEntries:any=[];
  business_approvedEntries:any=[];
  business_rejectedEntries:any=[];
  businessGroupErrors=''; 
  upload_businessGroupErrors='';

  teBusinessEntries:any=[];
  teBusiness_approvalwaitingEntries:any=[];
  teBusiness_approvedEntries:any=[];
  teBusiness_rejectedEntries:any=[];

  businessErrors:any;
  declarationEntries:any=[];
  declaration_approvalwaitingEntries:any=[];
  declaration_approvedEntries:any=[];
  declaration_rejectedEntries:any=[];
  declarationErrors='';

  standard_approvalwaitingEntries:any=[];
  standard_approvedEntries:any=[];
  standard_rejectedEntries:any=[];
  
  formData:FormData = new FormData();
  
  range:Array<any> = [];
  endYearRange:Array<any> = [];
  academicEndYearRange:Array<any> = [];
  
  id:number;
  userData:any;
  type:any;

  form : FormGroup;
  customerForm : FormGroup;
  userloginForm: FormGroup;
  cpdForm: FormGroup;
  certificateForm: FormGroup;
  experienceForm: FormGroup;
  qualificationForm: FormGroup;
  standardForm: FormGroup;
  stdfileform: FormGroup;
  bgroupfileform: FormGroup;
  bgroupdateform: FormGroup;
  standardRejectionForm: FormGroup;
  declarationForm: FormGroup;
  declarationRejectForm: FormGroup;
  declarationApprovedForm:FormGroup;
  
  auditExpForm: FormGroup;
  conExpForm: FormGroup;
  businessForm: FormGroup;
  rejbusinessForm: FormGroup;
  rejTEbusinessForm: FormGroup;
  mapUserRoleForm: FormGroup;
  technicalExpertBsForm:FormGroup;
  technicalExpertApprovedBsForm:FormGroup;

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
  
  approved_business_group_status=true;
  new_business_group_status=false;

  loadingArr = [];

  franchiseList:any;
  pre_qualification: string;
  closeRelError: string;
  editrelation: boolean;

  private mapToCheckboxArrayGroup(data: string[]): FormArray {
      return this.fb.array(data.map((i) => {
        return this.fb.group({
          name: i,
          selected: false
        });
      }));
  }  
  
  constructor( private modalService: NgbModal, private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private countryservice: CountryService, private CbService:CbService,
    private authservice:AuthenticationService,private standardService: StandardService,private processService: ProcessService,private BusinessSectorService: BusinessSectorService, public userService:UserService, private userRoleService:UserRoleService,public errorSummary: ErrorSummaryService) { }
  
  getSelectedValue(type,val)
  {
    if(type=='standard'){
      return this.standardList.find(x=> x.id==val).name;
    }else if(type=='role_id'){
      return this.roleList.find(x=> x.id==val).role_name;
    }else if(type=='process'){
      return this.processList.find(x=> x.id==val).name;
    }else if(type=='business_sector_id'){
      if(this.bsectorList !== undefined){
        return this.bsectorList.find(x=> x.id==val).name;
      }
      return '';
    }else if(type=='business_sector_group_id'){
      
      if(this.bsectorgroupList !== undefined){
        return this.bsectorgroupList.find(x=> x.id==val).group_code;
      }
      return '';
      
    }else if(type=='business_sector_group_id_group'){
     
      if(this.bgsectorgroupList !== undefined){
        let gplistcode = this.bgsectorgroupList.find(x=> x.id==val[0]);
        if(gplistcode !== undefined){
          return this.bgsectorgroupList.find(x=> x.id==val[0]).group_code;
        }
        return '';
        
      }
      return '';
      
    }else if(type=='approvedte_business_sector_group_id_group'){
     
      if(this.technicalExpertApprovedBgSectorgroupList !== undefined){
        let gplistcode = this.technicalExpertApprovedBgSectorgroupList.find(x=> x.id==val[0]);
        if(gplistcode !== undefined){
          return this.technicalExpertApprovedBgSectorgroupList.find(x=> x.id==val[0]).group_code;
        }
        return '';
        
      }
      return '';
      
    }else if(type=='te_business_sector_group_id_group'){
     
      if(this.technicalExpertBgSectorgroupList !== undefined){
        let gplistcode = this.technicalExpertBgSectorgroupList.find(x=> x.id==val[0]);
        if(gplistcode !== undefined){
          return this.technicalExpertBgSectorgroupList.find(x=> x.id==val[0]).group_code;
        }
        return '';
        
      }
      return '';
      
    }else if(type=='role_business_sector_group_id_group'){
     
      if(this.bgUsersectorgroupList !== undefined){
        let gplistcode = this.bgUsersectorgroupList.find(x=> x.id==val[0]);
        if(gplistcode !== undefined){
          return this.bgUsersectorgroupList.find(x=> x.id==val[0]).group_code;
        }
        return '';
        
      }
      return '';
      
    }
  }
  private _onDestroy = new Subject<void>();


  userType:number;
  userdetails:any;
  userdecoded:any;
  standardFormDetails:any=[];
  hasRoles = 0;
  userrolefulledit=false;

  showRecycle=false;
  showSocial=false;

  rejshowRecycle=false;
  rejshowSocial=false;

  waitingexamFileNames=[];
  waitingtechnicalInterviewFileNames=[];
  approvedexamFileNames=[];
  approvedtechnicalInterviewFileNames=[];
  minDate: Date;
  teRoleListEntriesApproved:any = [];
  mapuserroleEntries:any = [];
  mapuserroleIndex:any = 0;

  ngOnInit() {
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
    
	this.id = this.activatedRoute.snapshot.queryParams.id;
	
	this.type = this.activatedRoute.snapshot.queryParams.type;
	
	if(this.type=='standard')
	{
		this.personnel_details_status=false;
		this.standards_business_sectors_status=true;
	}
	
    this.userService.getBusinessSectors({user_id:this.id}).subscribe(res => {
		this.technicalExpertBgSectorList = res['bsectors'];
    });	
    this.userService.getTeRoles({user_id:this.id}).subscribe(res => {
		this.teRoleListEntriesApproved = res['rolelist'];
    });	

    this.userService.getBusinessSectors({user_id:this.id,type:'approved'}).subscribe(res => {
      this.technicalExpertApprovedBgSectorList = res['bsectors'];
    });	

    

    if(this.id && (this.userdetails.resource_access==1 || this.userdetails.rules.includes('edit_user_roles'))){
		this.userrolefulledit = true;
    }

	this.countryservice.getCountry().subscribe(res => {
		this.countryList = res['countries'];
	});
	this.standardService.getStandardList().subscribe(res => {
		this.standardList = res['standards'];
    });
	
    this.CbService.getCbList().subscribe(res => {
		this.cbList = res['cbs'];
    });
    /*
    this.processService.getProcessList().subscribe(res => {
      this.processList = res['processes'];
      this.filteredprocessMulti.next(this.processList.slice());
    });
    */
	
    /*
      this.userRoleService.getAllRoles().subscribe(res => {
        this.roleList = res['userroles'];
      });
    */

	  this.customerForm = this.fb.group({
	    id:[''],
      first_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],
	    last_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],
	    email:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.email,Validators.maxLength(255)]],
	    telephone:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.pattern("^[0-9\-\+]*$"), Validators.minLength(8), Validators.maxLength(15)]],
	    country_id:['',[Validators.required]],
      state_id:['',[Validators.required]], 
      passport_file:[],
      contract_file:[]
     
     /* standard:['',[Validators.required]], 
		  role:['',[Validators.required]], 
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
     /*
      qualification:['',[Validators.required]],
		  university:['',[Validators.required]],
		  subject:['',[Validators.required]],
		  passingyear:['',[Validators.required]],
		  percentage:['',[Validators.required,Validators.max(100),Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$')]],
		  experience:['',[Validators.required]],
		  exp_years:['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1})?$')]],
		  training_subject:['',[Validators.required]],
		  training_date:['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1})?$')]],
		  certificate_name:['',[Validators.required]],
		  completed_date:['',[Validators.required]],
     */

      //
        
      this.userloginForm = this.fb.group({
		    id:[''],
        //username:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15),this.errorSummary.cannotContainSpaceValidator]],
        //user_password:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15),this.errorSummary.cannotContainSpaceValidator]],
        role_id:['',[Validators.required]],
        franchiseFilterCtrl:[''],
        franchise_id:['',[Validators.required]]
      });

      this.uf.franchiseFilterCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterProcess();
      });

      this.cpdForm = this.fb.group({
		    id:[''],  
        training_subject:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        training_date:['',[Validators.required,Validators.pattern('^[0-9]{4}$'),Validators.min(1900)]],
        training_hours:['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]]
      });
      

      this.certificateForm = this.fb.group({
		    id:[''],  
        certificate_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
		    training_hours:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$')]],		
        completed_date:['',[Validators.required]],
        upload_certificate:['']
      });
      this.experienceForm = this.fb.group({
		    id:[''],
        experience:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        job_title:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        responsibility:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        exp_from_date:['',[Validators.required]],
        exp_to_date:['',[Validators.required]]
      });
      this.auditExpForm = this.fb.group({
		    id:[''],
        standard:['',[Validators.required]], 
        business_sector:['',[Validators.required]],
        year:['',[Validators.required]],
        company:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        cb:['',[Validators.required]],
        audit_role:['',[Validators.required]],
        days:['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$')]],
        //process:['',[Validators.required]],
        processFilterCtrl:['']
      });
      //process:['',[Validators.required]],
      this.conExpForm = this.fb.group({
		    id:[''],
        standard:['',[Validators.required]], 
        year:['',[Validators.required]],
        company:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        days:['',[Validators.required,Validators.pattern("^[0-9\-]*$")]],
        
        processFilterCtrl:['']
      });
      this.declarationForm = this.fb.group({
		    id:[''],
        declaration_company:['',[Validators.required,this.errorSummary.noWhitespaceValidator]], 
        declaration_contract:['',[Validators.required]],
        declaration_interest:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        declaration_start_year:['',[Validators.required,Validators.pattern("^[0-9\-]*$")]],
        declaration_end_year:['',[Validators.required,Validators.pattern("^[0-9\-]*$")]],
        rel_declaration_company:['',[Validators.required,this.errorSummary.noWhitespaceValidator]], 
        rel_declaration_contract:['',[Validators.required]],
        rel_declaration_interest:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        rel_declaration_start_year:['',[Validators.required,Validators.pattern("^[0-9\-]*$")]],
        rel_declaration_end_year:['',[Validators.required,Validators.pattern("^[0-9\-]*$")]],
        sel_close:['',[Validators.required]],
        sel_close2:['',[Validators.required]],
        spouse_work:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        relation_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        declaration_relation:['',[Validators.required]],
        rel_index:[''],
      });
      this.declarationRejectForm = this.fb.group({
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
      
      

      
      this.qualificationForm = this.fb.group({
		    id:[''], 
        qualification:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        university:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        subject:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        start_year:['',[Validators.required]],
        end_year:['',[Validators.required]]
      });
      this.standardForm = this.fb.group({
		    id:[''],
        standard:['',[Validators.required]], 
        //role_id:['',[Validators.required]], 
        // process:['',[Validators.required]], 
        // business_sector_id:['',[Validators.required]],
        // business_sector_group_id:['',[Validators.required]],

        std_exam_date:['',[Validators.required]],
        std_exam:[''],
        pre_qualification:['',[Validators.required]],
        recycle_exam_date:['',[Validators.required]],
        recycle_exam:[''],
        social_exam_date:['',[Validators.required]],
        social_exam:[''],
        qua_exam_file:[''],
        witness_date:['',[]],
        witness_file:[''],
        witness_valid_until:[''],
        witness_comment:[''],

        processFilterCtrl:['']
      });

      this.stdfileform = this.fb.group({
        approved_std_exam:[''],
        approved_recycle_exam:[''],
        approved_social_exam:[''],
        approved_witness_date:['',[Validators.required]],
        approved_witness_file:[''],
        approved_approval_date:['',[Validators.required]],
        approved_valid_until:['',[Validators.required]],
        approved_pre_qualification :['',[Validators.required]],
        approved_qua_exam_file :['']
      });

      this.bgroupfileform = this.fb.group({
        approved_examfilename:[''],
        approved_technicalfilename:[''],
      });

      this.bgroupdateform = this.fb.group({
        approved_approval_date:['',[Validators.required]],
      });

      this.standardRejectionForm = this.fb.group({
        id:[''],
        std_exam_date:['',[Validators.required]],
        std_exam:[''],
        recycle_exam_date:['',[Validators.required]],
        recycle_exam:[''],
        pre_qualification:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],
        social_exam_date:['',[Validators.required]],
        social_exam:[''],
        qua_exam_file:[''],
        witness_date:['',[]],
        witness_file:[''],
        witness_valid_until:[''],
        witness_comment:[''],

        processFilterCtrl:['']
      });

      
      this.businessForm = this.fb.group({
		    id:[''],  
        standard_id:['',[Validators.required]], 
        business_sector_id:['',[Validators.required]],
        business_sector_group_id:['',[Validators.required]],
        academic_qualification:['',[Validators.required]],
        exam_file:[''],
        technical_interview_file:['']
        
      });

      this.mapUserRoleForm = this.fb.group({
		    id:[''],  
        role_id:['',[Validators.required]], 
        standard_id:['',[Validators.required]], 
        business_sector_id:['',[Validators.required]],
        business_sector_group_id:['',[Validators.required]],
        document:['']
      });
	
	  this.technicalExpertBsForm = this.fb.group({
        id:[''], 			
        role_id:['',[Validators.required]],
        business_sector_id:['',[Validators.required]],
        business_sector_group_id:['',[Validators.required]],
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
	  
	  
      
      this.rejbusinessForm = this.fb.group({
        id:[''],
        academic_qualification:['',[Validators.required]],
        exam_file:[''],
        technical_interview_file:['']
        
      });
	  
	    this.rejTEbusinessForm = this.fb.group({
        id:[''],
        academic_qualification:['',[Validators.required]],
        exam_file:[''],
        technical_interview_file:['']        
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

      
	   this.customerForm.patchValue({user_type:1});
     
		
	   let year = new Date().getFullYear();
	   let startyear = year - 50;
	   this.range.push(year);
	   for (let i = 1; i <= 50; i++) {
		  this.range.push(year-i);
	   }
	   
	   
	  this.userService.getUserInformation(this.id).pipe(first(),
	    tap(res=>{
        if(res.data.country_id){
          this.countryservice.getStates(res.data.country_id).subscribe(res => {
              this.stateList = res['data'];
          });
        }
        
        // && res.data.process!=''
        if(res.data.standard!='')
        {
          let standardvals=res.data.standard;
        
          /*
          this.BusinessSectorService.getBusinessSectors({standardvals}).subscribe(res => {
            this.bsectorList = res['bsectors'];
          });
          */
        }
        
        // && res.data.process!=''
        if(res.data.standard!='' && res.data.business_sector_id!='')
        {
          let standardvals=res.data.standard;
          //let bsectorvals=res.data.business_sector_id;
          //processvals,
          /*
          this.BusinessSectorService.getBusinessSectorGroups({standardvals,bsectorvals}).subscribe(res => {
            this.bsectorgroupList = res['bsectorgroups'];
          });
          */
        }
        
		
      })
	  )
    .subscribe(res => {
      this.userData = res.data;
      
      this.standardNewList = res.data['standardNewList'];

      this.customerForm.patchValue(this.userData);
      this.customerForm.patchValue({experience:''});
      this.passport_file = this.userData['passport_file'];
      this.contract_file = this.userData['contract_file'];
	  
	  //this.title = 'Edit '+this.userData.first_name +' '+this.userData.last_name +'\'s Details';
	  
      let process=[];
      let role_id=[];
      let standard='';
      let business_sector=[];
      let business_sector_group=[];
      /*
      if(this.userData.process !=null){
        process = [...this.userData.process].map(String);
      }
      */
      if(this.userData.role_id !=null){
        role_id = [...this.userData.role_id].map(String);
      }
      //let standardFormDetails = '';
      if(this.userData.standard !=null && this.userData.standard.length>0){
        //standard = [...this.userData.standard].map(String);
        standard = this.userData.standard[0].standard;
        this.standardFormDetails = this.userData.standard[0];


        this.std_exam_file = this.standardFormDetails.standard_exam_file;

        if(this.standardFormDetails.standard_code == 'GRS' || this.standardFormDetails.standard_code == 'RCS'){
          this.recycle_exam_file = this.standardFormDetails.recycle_exam_file;
          this.showRecycle = true;
        }

        if(this.standardFormDetails.standard_code == 'GRS' || this.standardFormDetails.standard_code == 'GOTS'){
          this.social_exam_file = this.standardFormDetails.social_course_exam_file;
          this.showSocial = true;
        }

        if(this.standardFormDetails.witness_date != ''){
          this.witness_file = this.standardFormDetails.witness_file;
        }
        this.qua_exam_file = this.standardFormDetails.qua_exam_file;

      }

      if(this.userData.business_sector_id !=null){
        business_sector = [...this.userData.business_sector_id].map(String);
      }



      if(this.userData.business_sector_group_id !=null){
        business_sector_group = [...this.userData.business_sector_group_id].map(String);
      }
      
      this.standardForm.patchValue({
        //process:process,
        standard:standard,
        pre_qualification:this.standardFormDetails.pre_qualification?this.standardFormDetails.pre_qualification:'',
        std_exam_date:this.standardFormDetails.standard_exam_date?this.errorSummary.editDateFormat(this.standardFormDetails.standard_exam_date):'',
        recycle_exam_date:this.standardFormDetails.recycle_exam_date?this.errorSummary.editDateFormat(this.standardFormDetails.recycle_exam_date):'',
        social_exam_date:this.standardFormDetails.social_course_exam_date?this.errorSummary.editDateFormat(this.standardFormDetails.social_course_exam_date):'',
        witness_date:this.standardFormDetails.witness_date?this.errorSummary.editDateFormat(this.standardFormDetails.witness_date):''
        // business_sector_id:business_sector,
        // business_sector_group_id:business_sector_group
      });
      //this.customerForm.patchValue({process:process});
      //this.customerForm.patchValue({role:role});
      //this.customerForm.patchValue({standard:standard});
      //this.customerForm.patchValue({business_sector_id:business_sector});
      //this.customerForm.patchValue({business_sector_group_id:business_sector_group});
      this.userListEntries = this.userData.role_id?this.userData.role_id:[];
      this.userListEntriesWaitingApproval = this.userData.role_id_waiting_approval?this.userData.role_id_waiting_approval:[];
      this.userListEntriesApproved = this.userData.role_id_approved?this.userData.role_id_approved:[];
      this.userListEntriesRejected = this.userData.role_id_rejected?this.userData.role_id_rejected:[];

      this.uniqueRoleListEntriesApproved = this.userData.role_id_map_user?this.userData.role_id_map_user:[];
      
      this.mapuserroleEntries = this.userData.mapuserrole?this.userData.mapuserrole:[];

      this.standard_approvalwaitingEntries=this.userData.standard_approvalwaiting?this.userData.standard_approvalwaiting:[];
      this.standard_approvedEntries=this.userData.standard_approved?this.userData.standard_approved:[];
      this.standard_rejectedEntries=this.userData.standard_rejected?this.userData.standard_rejected:[];
      this.qualificationEntries=this.userData.qualifications?this.userData.qualifications:[];
      this.experienceEntries=this.userData.experience?this.userData.experience:[];
      this.auditexperienceEntries=this.userData.audit_experience?this.userData.audit_experience:[];
      this.consultancyexperienceEntries=this.userData.consultancy_experience?this.userData.consultancy_experience:[];
      this.declarationEntries=this.userData.declaration_new?this.userData.declaration_new:[];
      this.declaration_approvalwaitingEntries=this.userData.declaration_approvalwaiting?this.userData.declaration_approvalwaiting:[];
      this.declaration_approvedEntries=this.userData.declaration_approved?this.userData.declaration_approved:[];
      this.declaration_rejectedEntries=this.userData.declaration_rejected?this.userData.declaration_rejected:[];

      this.teBusinessEntries=this.userData.tebusinessgroup_new?this.userData.tebusinessgroup_new:[];
      this.teBusiness_approvalwaitingEntries=this.userData.tebusinessgroup_approvalwaiting?this.userData.tebusinessgroup_approvalwaiting:[];
      this.teBusiness_approvedEntries=this.userData.tebusinessgroup_approved?this.userData.tebusinessgroup_approved:[];
      this.teBusiness_rejectedEntries=this.userData.tebusinessgroup_rejected?this.userData.tebusinessgroup_rejected:[];

      this.certificateEntries=this.userData.certifications?this.userData.certifications:[];
      /*
      this.certificateEntries.forEach((x,index)=>{
        this.uploadedFileNames[index]= {name:x.filename,added:0,deleted:0,valIndex:index};
      });
      */
      this.businessEntries=this.userData.businessgroup_new?this.userData.businessgroup_new:[];
      this.business_approvalwaitingEntries=this.userData.businessgroup_approvalwaiting?this.userData.businessgroup_approvalwaiting:[];
      this.business_approvedEntries=this.userData.businessgroup_approved?this.userData.businessgroup_approved:[];
      this.business_rejectedEntries=this.userData.businessgroup_rejected?this.userData.businessgroup_rejected:[];

      /*
      this.businessEntries.forEach((x,index)=>{
        
        if(x.technicalfilename){
          this.technicalInterviewFileNames[index]= {name:x.technicalfilename,added:0,deleted:0,valIndex:index};
          
        }else{
          this.technicalInterviewFileNames[index]= '';
        }
        if(x.academic_qualification ==2){
          
          this.examFileNames[index]= {name:x.examfilename,added:0,deleted:0,valIndex:index};
          
        }else{
          this.examFileNames[index] = '';
          
        }
      });
      */
      /*
      this.qualificationEntries.forEach((x,index)=>{
       
        if(x.academic_certificate){
          this.uploadedacademicFileNames[index]= {name:x.academic_certificate,added:0,deleted:0,valIndex:index};
        }else{
          this.uploadedacademicFileNames[index]= '';
        }
      });
      */
      
      
      /*
      this.business_approvalwaitingEntries.forEach((x,index)=>{
        if(x.technicalfilename){
          this.waitingtechnicalInterviewFileNames[index]= {name:x.technicalfilename,added:0,deleted:0,valIndex:index};
          
        }else{
          this.waitingtechnicalInterviewFileNames[index]= '';
        }

        if(x.academic_qualification ==2){
          
          this.waitingexamFileNames[index]= {name:x.examfilename,added:0,deleted:0,valIndex:index};
          //this.waitingtechnicalInterviewFileNames[index]= {name:x.technicalfilename,added:0,deleted:0,valIndex:index};
        }else{
          this.waitingexamFileNames[index] = '';
          //this.waitingtechnicalInterviewFileNames[index]= '';
        }
      });
      */
      
      /*
      this.business_approvedEntries.forEach((x,index)=>{
        if(x.technicalfilename){
          this.approvedtechnicalInterviewFileNames[index]= {name:x.technicalfilename,added:0,deleted:0,valIndex:index};
          
        }else{
          this.approvedtechnicalInterviewFileNames[index]= '';
        }
        if(x.academic_qualification ==2){
          
          this.approvedexamFileNames[index]= {name:x.examfilename,added:0,deleted:0,valIndex:index};
          //this.approvedtechnicalInterviewFileNames[index]= {name:x.technicalfilename,added:0,deleted:0,valIndex:index};
        }else{
          this.approvedexamFileNames[index] = '';
          //this.approvedtechnicalInterviewFileNames[index]= '';
        }
      });
      */
      
      
      /*
      this.business_rejectedEntries.forEach((x,index)=>{
        if(x.technicalfilename){
          this.rejtechnicalInterviewFileNames[index]= {name:x.technicalfilename,added:0,deleted:0,valIndex:index};
          
        }else{
          this.rejtechnicalInterviewFileNames[index]= '';
        }
        if(x.academic_qualification ==2){
          
          this.rejexamFileNames[index]= {name:x.examfilename,added:0,deleted:0,valIndex:index};
          //this.rejtechnicalInterviewFileNames[index]= {name:x.technicalfilename,added:0,deleted:0,valIndex:index};
        }else{
          this.rejexamFileNames[index] = '';
          //this.rejtechnicalInterviewFileNames[index]= '';
        }
      });
      */
      //console.log(this.uploadedFileNames);
      this.trainingEntries=this.userData.training_info?this.userData.training_info:[];
      
      this.qualificationIndex=this.qualificationEntries.length;
      this.experienceIndex = this.experienceEntries.length;
      this.auditexperienceIndex = this.auditexperienceEntries.length;
      this.consultancyexperienceIndex = this.consultancyexperienceEntries.length;
      this.declarationIndex = this.declarationEntries.length;
      this.trainingIndex=this.trainingEntries.length;
      this.certificateIndex=this.certificateEntries.length;
      this.userIndex = this.userListEntries.length;
      this.businessIndex = this.businessEntries.length;

      if(this.userIndex > 0){
        this.hasRoles = 1;
      }
    },
    error => {
        this.error = {summary:error};
        this.loading = false;
    });  
    
    this.sf.processFilterCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterProcess();
    });

    this.userService.getAllUser({type:3}).pipe(first())
    .subscribe(res => {
    
      if(this.userType == 3 && this.userdetails.resource_access ==5){
        this.franchiseList = res.users.filter(x=>x.id==this.userdetails.franchiseid); 
        
        this.userloginForm.patchValue({
          'franchise_id':this.userdetails.franchiseid,
          'username':'',
          'user_password':''
        });
        this.getUserRoleList(this.userdetails.uid);
      }else if(this.userType == 3 ){
        this.franchiseList = res.users.filter(x=>x.id==this.userdetails.uid); 
        
        this.userloginForm.patchValue({
          'franchise_id':this.userdetails.uid,
          'username':'',
          'user_password':''
        });
        this.getUserRoleList(this.userdetails.uid);
      }else{
        this.franchiseList = res.users;
      }
      this.filteredfranchiseMulti.next(this.franchiseList.slice());
    },
    error => {
        this.error = {summary:error};
    });
  }
  removeProcess(Id:number) {
    
      this.relationEntries.splice(Id,1);
  }
  removeRejRelation(Id:number) {
    
    this.rejrelationEntries.splice(Id,1);
  }
  addRelation(){
    let relationName = this.declarationForm.get('relation_name').value;
    let relationType = this.declarationForm.get('declaration_relation').value;
    let rel_declaration_company = this.declarationForm.get('rel_declaration_company').value;
    let rel_declaration_contract = this.declarationForm.get('rel_declaration_contract').value;
    let rel_declaration_interest = this.declarationForm.get('rel_declaration_interest').value;
	  let rel_declaration_start_year = this.declarationForm.get('rel_declaration_start_year').value;
    let rel_declaration_end_year = this.declarationForm.get('rel_declaration_end_year').value;

    let relationTypeName = this.userData.relationList[relationType];
    let contractName =this.userData.declaration_contract[rel_declaration_contract];

    this.df.relation_name.setValidators([Validators.required]);
    this.df.declaration_relation.setValidators([Validators.required]);
    this.df.rel_declaration_company.setValidators([Validators.required]), 
    this.df.rel_declaration_contract.setValidators([Validators.required]),
    this.df.rel_declaration_interest.setValidators([Validators.required]),
    this.df.rel_declaration_start_year.setValidators([Validators.required,Validators.pattern("^[0-9\-]*$")]),
    this.df.rel_declaration_end_year.setValidators([Validators.required,Validators.pattern("^[0-9\-]*$")]),
    this.df.relation_name.updateValueAndValidity();
    this.df.declaration_relation.updateValueAndValidity();
    this.df.rel_declaration_company.updateValueAndValidity();
    this.df.rel_declaration_contract.updateValueAndValidity();
    this.df.rel_declaration_interest.updateValueAndValidity();
    this.df.rel_declaration_start_year.updateValueAndValidity();
    this.df.rel_declaration_end_year.updateValueAndValidity();
    

    this.df.relation_name.markAsTouched();
    this.df.declaration_relation.markAsTouched();
    this.df.rel_declaration_company.markAsTouched();
    this.df.rel_declaration_contract.markAsTouched();
    this.df.rel_declaration_interest.markAsTouched();
    this.df.rel_declaration_start_year.markAsTouched();
    this.df.rel_declaration_end_year.markAsTouched();


    /*
    if(processId==''){
      this.processErrors = 'Please select the Process';
      return false;
    }
    */
    if(this.df.relation_name.errors || this.df.declaration_relation.errors || this.df.rel_declaration_company.errors || this.df.rel_declaration_contract.errors || this.df.rel_declaration_interest.errors || this.df.rel_declaration_start_year.errors || this.df.rel_declaration_end_year.errors){
      return false;
    }
    
    
    
    
      let expobject:any=[];
      expobject["name"] = relationName;
      expobject["type_name"] = relationTypeName;
      expobject["type_name_id"]= relationType;
      expobject["rel_declaration_company"] = rel_declaration_company;
      expobject["rel_declaration_contract"] = rel_declaration_contract;
      expobject["rel_declaration_contract_name"] = contractName;
      expobject["rel_declaration_interest"] = rel_declaration_interest;
      expobject["rel_declaration_start_year"] = rel_declaration_start_year;
      expobject["rel_declaration_end_year"] = rel_declaration_end_year;
      this.relationEntries.push(expobject);
    
    this.df.relation_name.setValidators(null);
    this.df.declaration_relation.setValidators(null);
    this.df.rel_declaration_company.setValidators(null);
    this.df.rel_declaration_contract.setValidators(null);
    this.df.rel_declaration_interest.setValidators(null);
    this.df.rel_declaration_start_year.setValidators(null);
    this.df.rel_declaration_end_year.setValidators(null);
    this.df.relation_name.updateValueAndValidity();
    this.df.declaration_relation.updateValueAndValidity();
    this.df.rel_declaration_company.updateValueAndValidity();
    this.df.rel_declaration_contract.updateValueAndValidity();
    this.df.rel_declaration_interest.updateValueAndValidity();
    this.df.rel_declaration_start_year.updateValueAndValidity();
    this.df.rel_declaration_end_year.updateValueAndValidity();
    
    this.declarationForm.patchValue({
      relation_name: '',
      declaration_relation:'',
      rel_declaration_company:'',
      rel_declaration_contract:'',
      rel_declaration_interest:'',
      rel_declaration_start_year:'',
      rel_declaration_end_year:''
    });
  }

  addRejectRelation(){
    let relationName = this.declarationRejectForm.get('relation_name').value;
    let relationType = this.declarationRejectForm.get('declaration_relation').value;
    let relationTypeName = this.userData.relationList[relationType];

    this.drf.relation_name.setValidators([Validators.required]);
    this.drf.declaration_relation.setValidators([Validators.required]);
    this.drf.relation_name.updateValueAndValidity();
    this.drf.declaration_relation.updateValueAndValidity();

    this.drf.relation_name.markAsTouched();
    this.drf.declaration_relation.markAsTouched();
    /*
    if(processId==''){
      this.processErrors = 'Please select the Process';
      return false;
    }
    */
    if(this.drf.relation_name.errors || this.drf.declaration_relation.errors){
      return false;
    }
    
    
    
    
      let expobject:any=[];
      expobject["name"] = relationName;
      expobject["type_name"] = relationTypeName;
      this.rejrelationEntries.push(expobject);
    
    this.drf.relation_name.setValidators(null);
    this.drf.declaration_relation.setValidators(null);
    this.drf.relation_name.updateValueAndValidity();
    this.drf.declaration_relation.updateValueAndValidity();
    
    this.declarationRejectForm.patchValue({
      relation_name: '',
      declaration_relation:''
    });
  }
  standardNewList:any;
  public filteredfranchiseMulti: ReplaySubject<User[]> = new ReplaySubject<User[]>(1);
  private filterFranchise() {
    if (!this.franchiseList) {
      return;
    }
    // get the search keyword
    let search = this.uf.franchiseFilterCtrl.value;
    if (!search) {
      this.filteredfranchiseMulti.next(this.franchiseList.slice());
      return;
    } else {
      search = search.toLowerCase();
    }
    // filter the banks
    this.filteredfranchiseMulti.next(
      this.franchiseList.filter(p => p.name.toLowerCase().indexOf(search) > -1)
    );

    
  }

  //---------------------- ******************* - Waiting for Approval codes starts- ******************* ---------------------

  titlename = '';
  openreviewmodel(content,action,id,titlename,userEntry:any='') 
  {
    // console.log(id);
    // console.log(action);
      if(action =='standard'){
        this.model.witness_date = '';
        this.model.popup_witness_file = '';
        // this.model.witness_comment = '';
         this.model.valid_until = '';
         //this.model.valid_until = this.errorSummary.editDateFormat(this.userData.temp_valid_until);
        //let stddetails = this.userData.standard_approvalwaiting.find(x=>x.id==id);
        let stddetails = this.standard_approvalwaitingEntries.find(x=>x.id==id);
        
        //console.log(stddetails);
        if(stddetails && stddetails.witness_date !=''){
          //console.log(this.errorSummary.editDateFormat(stddetails.witnessdate));
         // console.log(stddetails.witness_date);
          this.model.witness_date = this.errorSummary.editDateFormat(stddetails.witness_date);
          this.model.popup_witness_file = stddetails.witness_file;
  
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
        this.form.reset();
        this.form.patchValue({
          id:this.id,
          role_id:id,
          status:'',
          username:'',
          user_password:'',
          comment:''            
        });

        //this.form.reset();
          
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
        this.model.user_role_type = '';
        //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;	  
        });
  }

  popupbtnDisable=false;
  resetBtn()
  {
    this.alertInfoMessage='';
    this.alertSuccessMessage='';
    this.alertErrorMessage='';
    this.cancelBtn=true;
    this.okBtn=true;
    this.popupbtnDisable = false;
  }

  changeUserRoleStatus(val)
  {
	  if(val==2)
	  {
		  this.roleApproveStatus=true;
	  }else{
		  this.roleApproveStatus=false;
	  }
	  
  }

  checkUserSel()
  {
    this.raf.status.markAsTouched();
    this.raf.comment.markAsTouched();
    
    if(this.model.user_role_type)
    {
      if(this.form.get('status').value== 2)
      {
        //this.raf.username.setValidators([Validators.required]);
        //username:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15),this.errorSummary.cannotContainSpaceValidator]],
        this.raf.username.setValidators([Validators.required,Validators.pattern('^[a-zA-Z0-9\#$@%-]+$'),Validators.minLength(6),Validators.maxLength(25)]);
        this.raf.username.updateValueAndValidity();
        this.raf.username.markAsTouched();
        
        //this.raf.user_password.setValidators([Validators.required]);
        //this.raf.user_password.setValidators([Validators.required,Validators.pattern('^[a-zA-Z0-9\#$@%-]?$'),Validators.minLength(6),Validators.maxLength(25)]);
        this.raf.user_password.setValidators([Validators.required,Validators.pattern('^[a-zA-Z0-9\#$@%-]+$'),Validators.minLength(6),Validators.maxLength(25)]);
        this.raf.user_password.updateValueAndValidity();
        this.raf.user_password.markAsTouched();		
      }
    }else{
      
      this.raf.username.setValidators([]);
      this.raf.username.updateValueAndValidity();
      this.raf.username.markAsTouched();
      
      this.raf.user_password.setValidators([]);
      this.raf.user_password.updateValueAndValidity();
      this.raf.user_password.markAsTouched();
      
      this.form.patchValue({
        username:'',
        user_password:''              
      });
    }
    	
      //console.log(this.form.valid+':roletype'+this.model.user_role_type);
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
    this.form.reset();
  }

  status_error=false;
  comment_error = false;
  witness_date_error = '';
  valid_until_error = '';
  witness_comment_error = '';
  roleApproveStatus = false;
  commonReviewAction(approvaltype=''){
    
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
    this.witnesspopup_fileErrors = '';
    let datapost:any = {id:actionid,type:type,user_id:this.id,status:this.model.status,comment:this.model.comment};
    if(type=='standard' && this.model.status==2){
   
      if(this.model.popup_witness_file ==''){
        this.witnesspopup_fileErrors ='Please upload file';
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
     


    this.witnessfileformData.append('formvalues',JSON.stringify(datapost));
    if(this.comment_error || this.status_error || stdError){
      return false;
    }
    //{id:actionid,type:type,user_id:this.id,status:this.model.status,comment:this.model.comment}
    this.popupbtnDisable= true;
    this.userService.sendToApproveAndReject(this.witnessfileformData).subscribe(res => {
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

  //---------------------- ******************* - Waiting for Approval codes ends- ******************* ---------------------

  getTeBgsectorgroupList(value='',empty=false,editid:any=0){
    //this.bgsectorgroupList = [];
    //let standardvals=this.technicalExpertBsForm.controls.standard_id.value;
    let role_id=this.technicalExpertBsForm.controls.role_id.value;
    let bsectorvals:any = value;
    if(value==''){
      bsectorvals=this.technicalExpertBsForm.controls.business_sector_id.value;
    }
    
    //let bsectorvals=value;
    if(bsectorvals>0)
    {
      this.loadingArr['tebsgroup'] = true;
      this.userService.getBusinessSectorsGroup({role_id:role_id,id:editid,user_id:this.id,business_sector_id:bsectorvals}).subscribe(res => {
        this.technicalExpertBgSectorgroupList = res['bsectorgroups'];
        this.loadingArr['tebsgroup'] = false;
        if(!empty){
          this.technicalExpertBsForm.patchValue({business_sector_group_id:''});
        }
      });	
    }else{		
      this.technicalExpertBgSectorgroupList = [];
      this.technicalExpertBsForm.patchValue({business_sector_group_id:''});		
    }
  }

  getTeApprovedBgsectorgroupList(value='',empty=false,editid:any=0){
    let role_id=this.technicalExpertApprovedBsForm.controls.role_id.value;
    let bsectorvals:any = value;
    if(value==''){
      bsectorvals=this.technicalExpertApprovedBsForm.controls.business_sector_id.value;
    }
    
    //let bsectorvals=value;
    if(bsectorvals>0)
    {
      this.loadingArr['approvedtebsgroup'] = true;
      this.userService.getBusinessSectorsGroup({type:'approved', role_id:role_id,id:editid,user_id:this.id,business_sector_id:bsectorvals}).subscribe(res => {
        this.technicalExpertApprovedBgSectorgroupList = res['bsectorgroups'];
        this.loadingArr['approvedtebsgroup'] = false;
        if(!empty){
          this.technicalExpertApprovedBsForm.patchValue({business_sector_group_id:''});
        }
      });	
    }else{		
      this.technicalExpertApprovedBgSectorgroupList = [];
      this.technicalExpertApprovedBsForm.patchValue({business_sector_group_id:''});		
    }
  }
  
  getUserRoleList(value){
	this.userRoleService.getFranchiseBasedUserRole({'franchise_id':value}).subscribe(res => {
		this.roleList = [];
		if(res.status==1)
		{
			this.roleList = res['userroles'];
		}
    });
  }

  showApprovedStdDetails(content,row_id)
  {
    this.stdData = this.standard_approvedEntries[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }
  panelOpenState = true;
  showRejectedStdDetails(content,row_id)
  {
    this.stdData = this.standard_rejectedEntries[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showApprovedStdHistoryDetails(content,row_id)
  {
    this.stdhistoryData = this.standard_approvedEntries[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedStdHistoryDetails(content,row_id)
  {
    this.stdhistoryData = this.standard_rejectedEntries[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showApprovedDecHistoryDetails(content,row_id)
  {
    this.dechistoryData = this.declaration_approvedEntries[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedDecHistoryDetails(content,row_id)
  {
    this.dechistoryData = this.declaration_rejectedEntries[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showApprovedDeclarationDetails(content,row_id)
  {
    this.decData = this.declaration_approvedEntries[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedDeclarationDetails(content,row_id)
  {
    this.decData = this.declaration_rejectedEntries.find(x=>x.id==row_id);
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }
  gpcoderow:any;
  showApprovedbgroupDetails(content,row_id,coderow)
  {
    this.gpcoderow= coderow;
    this.bgroupData = this.business_approvedEntries[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedbgroupDetails(content,row_id,coderow)
  {
    this.gpcoderow= coderow;
    this.bgroupData = this.business_rejectedEntries[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showApprovedbgroupHistoryDetails(content,row_id,datalist)
  {
    this.bgroupcodeData = datalist; //this.business_approvedEntries[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedbgroupHistoryDetails(content,row_id,datalist)
  {
    this.bgroupcodeData = datalist; //this.business_rejectedEntries[row_id].rejected_history;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }


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

  showApprovedRoleDetails(content,row_id)
  {
    this.roleData = this.userListEntriesApproved[row_id];
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  showRejectedRoleDetails(content,row_id)
  {
    this.roleData = this.userListEntriesRejected.find(x=>x.user_role_id == row_id);
    //console.log(this.roleData);
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  downloadFile(fileid,filename){
    this.userService.downloadFile({id:fileid,user_id:this.id})
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

  downloadstdFile(fileid,filetype,filename){
    this.userService.downloadStandardFile({id:fileid,filetype,user_id:this.id})
    .subscribe(res => {
      this.modalss.close();
      
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }

  downloadacademicFile(fileid,filename){
    this.userService.downloadAcademicFile({id:fileid,user_id:this.id})
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
    this.userService.downloadBgroupFile({id:fileid,filetype,user_id:this.id})
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
    this.userService.downloadteBgroupFile({id:fileid,filetype,user_id:this.id})
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

  downloaddocumentFile(fileid,filename){
    this.userService.downloaddocumentFile({id:fileid,user_id:this.id})
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


  

  modalss:any;
  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  sendApprovalValue='';
  open(content,arg='') {
    if(arg=='declaration'){
      if(!this.declarationEntries || this.declarationEntries.length<=0){
        this.error = {summary:'Please add declaration and send for approval'};
        return false;
      }
      this.sendApprovalValue = 'Declaration(s)';
      //unitstatus = this.auditPlanData.arrUnitEnumStatus['review_in_process'];
    }else if(arg=='businessgroup'){
      if(!this.businessEntries || this.businessEntries.length<=0){
        this.error = {summary:'Please add business group and send for approval'};
        return false;
      }
      this.sendApprovalValue = 'Business Group(s)';
    }else if(arg=='standard'){
      //this.standardForm.reset();
      if(!this.standardFormDetails || this.standardFormDetails.standard==undefined){
        this.error = {summary:'Please add standard and send for approval'};
        return false;
      }
      this.sendApprovalValue = 'Standard';
    }else if(arg=='role'){
      if(!this.userListEntries || this.userListEntries.length<=0){
        this.error = {summary:'Please add user role and send for approval'};
        return false;
      }
      this.sendApprovalValue = 'Role(s)';
    }else if(arg=='te_business_group'){
      if(!this.teBusinessEntries || this.teBusinessEntries.length<=0){
        this.error = {summary:'Please add business group for roles and send for approval'};
        return false;
      }
    }

    
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => { 
      if(arg=='declaration'){
        this.sendForApproval({type:'declaration',id:this.id});
      }else if(arg=='declarationreject'){
        this.sendForApproval({type:'declarationreject',id:this.id});
      }else if(arg=='businessgroup'){
        this.sendForApproval({type:'businessgroup',id:this.id});
      }else if(arg=='te_business_group'){
        this.sendForApproval({type:'te_business_group',id:this.id});
      }else if(arg=='rejbusinessgroup'){
        this.sendForApproval({type:'rejbusinessgroup',id:this.id});
      }else if(arg=='standard'){
        this.sendForApproval({type:'standard',id:this.id});
      }else if(arg=='role'){
        this.sendForApproval({type:'role',id:this.id});
      }else if(arg=='rejrole'){
        this.sendForApproval({type:'rejrole',id:this.id});
      }


    }, (reason) => {
      
    });
  }

  sendForApproval(data){
    if(data.type =='declaration'){
      this.loadingArr['declaration'] = true;
    }else if(data.type =='businessgroup'){
      this.loadingArr['businessForm'] = true;
    }else if(data.type =='standard'){
      this.loadingArr['standardForm'] = true;
    }else if(data.type =='role'){
      this.loadingArr['userloginForm'] = true;
    }else if(data.type =='te_business_group'){
      this.loadingArr['technicalExpertBsForm'] = true;
    }
    this.userService.sendForApproval(data)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            
            this.success = {summary:res.message};
            if(data.type =='declaration'){
              //this.declaration_approvalwaitingEntries = res['declaration_approvalwaiting'];
              this.getUserData('declaration');
              this.declarationEntries = [];
              this.loadingArr['declaration'] = false;
              this.declarationIndex=this.declarationEntries.length;
            }else if(data.type =='declarationreject'){
              //this.declaration_approvalwaitingEntries = res['declaration_approvalwaiting'];
              this.getUserData('declaration');
              this.declarationEntries = [];
              this.loadingArr['declaration'] = false;
              this.declaration_rejectedEntries=[];
            }else if(data.type =='businessgroup'){
              //this.business_approvalwaitingEntries = res['businessgroup_approvalwaiting'];

              this.getUserData('business_group');
              this.businessEntries = [];
              this.loadingArr['businessForm'] = false;
              this.businessIndex= this.businessEntries.length;
              this.technicalInterviewFileNames = '';
              this.examFileNames = '';
              this.businessformData = new FormData();
            }else if(data.type =='te_business_group'){
              this.teBusinessIndex = this.teBusinessEntries.length;
              this.teBusinessformData = new FormData();
              this.getUserData('te_business_group');
              this.loadingArr['technicalExpertBsForm'] = false;
            }else if(data.type =='rejbusinessgroup'){
              //this.business_approvalwaitingEntries = res['businessgroup_approvalwaiting'];

              this.getUserData('business_group');
              this.business_rejectedEntries = [];
              this.loadingArr['businessForm'] = false;
              this.rejbusinessformData = new FormData();
            }else if(data.type =='standard'){
              this.getUserData('standard');
              this.stdformData = new FormData();
              //this.standard_approvalwaitingEntries = res['standard_approvalwaiting'];
              this.standardForm.reset();
              //this.standard = [];
              //this.standardFormDetails = this.userData.standard[0];

              //this.standardNewList = res['standardNewList'];

              this.standardFormDetails = [];
              this.std_exam_file = '';
              this.recycle_exam_file = '';
              this.social_exam_file = '';
              this.pre_qualification='';
              this.qua_exam_file='';
              this.witness_file = '';
              
              this.showRecycle = false;
              this.showSocial = false;
        
              this.standardForm.patchValue({standard:''});
              this.loadingArr['standardForm'] = false;
              //this.businessIndex= this.businessEntries.length;
            }else if(data.type =='role'){
              //this.userListEntriesWaitingApproval = res['userListEntriesWaitingApproval'];
              this.getUserData('role');
              this.userListEntries = [];
              this.loadingArr['userloginForm'] = false;
              this.userIndex= 0;//this.userListEntries.length;
            }else if(data.type =='rejrole'){
              this.getUserData('role');
              //this.userListEntriesWaitingApproval = res['userListEntriesWaitingApproval'];
              this.userListEntriesRejected = [];
              this.loadingArr['userloginForm'] = false;
              
            }
				    this.buttonDisable = false;
			      setTimeout(() => {
              //this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
            
            //this.submittedSuccess =1;
          }else if(res.status == 0){
            //this.submittedError =1;
            this.error = {summary:res.message};
            //this.errorSummary.getErrorSummary(res.message,this,this.customerForm);	
            this.scrollToBottom();
          }else{
            //this.submittedError =1;
            this.error = {summary:res};
            this.scrollToBottom();
          }
          this.loadingArr['businessForm'] = false;
          this.loadingArr['userloginForm'] = false;
          this.loadingArr['standardForm'] = false;
          this.loadingArr['declaration'] = false;

          
          
      },
      error => {
          this.error = error;
          if(data.type =='declaration'){
            this.loadingArr['declaration'] = false;
          }else if(data.type =='businessgroup'){
            this.loadingArr['businessForm'] = false;
          }
      });
  }

  scrollToBottom()
  {
    window.scroll({ 
      top: document.body.scrollHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }

  downloadUserFile(filename,filetype){
	console.log(this.id);
    this.userService.downloadUserFile({id:this.id,filetype,user_id:this.id})
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

 

  getBsectorList(value){
    let standardvals=this.standardForm.controls.standard.value;
   // let processvals=this.standardForm.controls.process.value;
   // && processvals.length>0
    if(standardvals.length>0)
    {
      //processvals
      this.BusinessSectorService.getBusinessSectors({standardvals}).subscribe(res => {
        this.bsectorList = res['bsectors'];
        this.standardForm.patchValue({business_sector_id:''});
      });	
    }else{		
      this.bsectorList = [];
      this.standardForm.patchValue({business_sector_id:''});		
    }
  }
  getBsectorgroupList(value){
    let standardvals=this.standardForm.controls.standard.value;
    //let processvals=this.standardForm.controls.process.value;
    let bsectorvals=value;
    //&& processvals.length>0
    if(standardvals.length>0  && bsectorvals.length>0)
    {
      //processvals,
      this.BusinessSectorService.getBusinessSectorGroups({standardvals,bsectorvals}).subscribe(res => {
        this.bsectorgroupList = res['bsectorgroups'];
        this.standardForm.patchValue({business_sector_group_id:''});
      });	
    }else{		
      this.bsectorgroupList = [];
      this.standardForm.patchValue({business_sector_group_id:''});		
    }
  }


  getBgsectorList(value,empty=false){
    let standardvals=this.businessForm.controls.standard_id.value;
    //let processvals=this.standardForm.controls.process.value;
    this.bgsectorgroupList = [];
    this.bgsectorList = [];
    if(standardvals>0)
    {
      /*
      this.BusinessSectorService.getBusinessSectorsbystds({standardvals}).subscribe(res => {
        this.bgsectorList = res['bsectors'];
        if(!empty){
          this.businessForm.patchValue({business_sector_id:''});
        }
      });	
      */
      this.loadingArr['bgSector'] = true;
      this.userService.getBusinessSectorsbystds({standardvals}).subscribe(res => {
        this.loadingArr['bgSector'] = false;
        this.bgsectorList = res['bsectors'];
        if(!empty){
          this.businessForm.patchValue({business_sector_id:''});
        }
      });	
    }else{		
      this.bgsectorList = [];
      this.businessForm.patchValue({business_sector_id:''});		
    }
  }
  getBgsectorgroupList(value,empty=false){
    this.bgsectorgroupList = [];
    let standardvals=this.businessForm.controls.standard_id.value;
    let id=this.businessForm.controls.id.value;
    //let processvals=this.standardForm.controls.process.value;
    let bsectorvals=value;
    if(standardvals>0 && bsectorvals>0)
    {
      /*
      this.BusinessSectorService.getBusinessSectorGroupsbystds({standardvals,bsectorvals}).subscribe(res => {
        this.bgsectorgroupList = res['bsectorgroups'];
        if(!empty){
          this.businessForm.patchValue({business_sector_group_id:''});
        }
      });	
      */
      this.loadingArr['bgSectorGroup'] = true;
      this.userService.getBusinessSectorGroupsbystds({id:id,user_id:this.id,standardvals,bsectorvals}).subscribe(res => {
        this.loadingArr['bgSectorGroup'] = false;
        this.bgsectorgroupList = res['bsectorgroups'];
        if(!empty){
          this.businessForm.patchValue({business_sector_group_id:''});
        }
      });	
    }else{		
      this.bgsectorgroupList = [];
      this.businessForm.patchValue({business_sector_group_id:''});		
    }
  }



  getUserBgsectorList(value,empty=false){
    let standardvals=this.mapUserRoleForm.controls.standard_id.value;
    //let processvals=this.standardForm.controls.process.value;
    this.bgUsersectorgroupList = [];
    this.bgUsersectorList = [];
    if(standardvals>0)
    {
      this.loadingArr['map_business_sector_id'] = true;
      this.userService.getuserBusinessSectors({standard_id:standardvals,user_id:this.id}).subscribe(res => {
        this.bgUsersectorList = res['data'];
        this.loadingArr['map_business_sector_id'] = false;
        if(!empty){
          this.mapUserRoleForm.patchValue({business_sector_id:''});
        }
      });	
    }else{		
      this.bgUsersectorList = [];
      this.mapUserRoleForm.patchValue({business_sector_id:''});		
    }
  }
  getUserBgsectorgroupList(value:any=0,empty=false){
    this.bgUsersectorgroupList = [];
    let standardvals=this.mapUserRoleForm.controls.standard_id.value;
    let role_id=this.mapUserRoleForm.controls.role_id.value;
    let id=this.mapUserRoleForm.controls.id.value;
    //let processvals=this.standardForm.controls.process.value;
    let bsectorvals:any=value;
    if(value==0 || value==''){
      bsectorvals  = this.mapUserRoleForm.controls.business_sector_id.value;
    }
    
    if(standardvals>0 && bsectorvals>0)
    {
      this.loadingArr['map_business_sector_group_id'] = true;
      this.userService.getUserBusinessSectorGroups({id:id,role_id:role_id,standard_id:standardvals,business_group_id:bsectorvals,user_id:this.id}).subscribe(res => {
        this.bgUsersectorgroupList = res['data'];
        this.loadingArr['map_business_sector_group_id'] = false;
        if(!empty){
          this.mapUserRoleForm.patchValue({business_sector_group_id:''});
        }
      });	
    }else{		
      this.bgUsersectorgroupList = [];
      this.mapUserRoleForm.patchValue({business_sector_group_id:''});		
    }
  }
  mapUserformData:FormData = new FormData();
  onMapUserRoleSubmit(){
    
    this.auditexperienceStatus=true;  

    let role_id = this.mapUserRoleForm.get('role_id').value;
    let id = this.mapUserRoleForm.get('id').value;
    let standard_id = this.mapUserRoleForm.get('standard_id').value;
    let business_sector_id = this.mapUserRoleForm.get('business_sector_id').value;
    let business_sector_group_id = this.mapUserRoleForm.get('business_sector_group_id').value;
    //let process = this.conExpForm.get('process').value;
    
    
    this.urf.role_id.markAsTouched();
    this.urf.standard_id.markAsTouched();
    this.urf.business_sector_id.markAsTouched();
    this.urf.business_sector_group_id.markAsTouched();
    //this.urf.id.markAsTouched();

    if(this.mapUserRoleForm.valid){

    
      this.loadingArr['mapUserRoleForm'] = true;
	  
      let datas = [];

      let document_file = this.documents;
      
      datas.push({id:id,document_file:document_file,role_id:role_id,standard_id:standard_id,business_sector_id:business_sector_id,business_sector_group_id:business_sector_group_id});
      

      let formvalue:any={};
      formvalue.mapuserrole = datas;
      formvalue.actiontype = 'mapuserrole';
      formvalue.id = this.id;
      this.mapUserformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.mapUserformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){

            this.getUserData('mapuserrole'); 
            /*
            this.qualificationEntries = res['resultarr']['qualifications'];
            this.qualificationEntries.forEach((x,index)=>{
       
              if(x.academic_certificate){
                this.uploadedacademicFileNames[index]= {name:x.academic_certificate,added:0,deleted:0,valIndex:index};
              }else{
                this.uploadedacademicFileNames[index]= '';
              }
            });
            */
			      this.success = {summary:res.message};
				    this.buttonDisable = false;
			      setTimeout(() => {
              this.resetUserForm('mapuserrole');
              this.loadingArr['mapUserRoleForm'] = false;
              //this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
            
            //this.submittedSuccess =1;
          }else if(res.status == 0){
            //this.submittedError =1;
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.customerForm);	
          }else{
            //this.submittedError =1;
            this.error = {summary:res};
          }
          this.loadingArr['mapuserroleForm'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['mapuserroleForm'] = false;
      });
      
    }
  }





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
 
  get raf() { return this.form.controls; } 
  get f() { return this.customerForm.controls; } 
  get sf() { return this.standardForm.controls; } 
  get asf() { return this.stdfileform.controls; }
  get bcsf() { return this.bgroupfileform.controls; }
  get bsf() { return this.bgroupdateform.controls; }
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
  get terf() { return this.rejTEbusinessForm.controls; }
  get drf() { return this.declarationRejectForm.controls; } 
  get daf() { return this.declarationApprovedForm.controls; }
  get urf() { return this.mapUserRoleForm.controls; } 
  get tebsf() { return this.technicalExpertBsForm.controls; } 
  get teapprovedbsf() { return this.technicalExpertApprovedBsForm.controls; } 
  
  
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
  
  std_examfileErrors = '';
  recycle_examfileErrors = '';
  social_exam_fileErrors = '';
 

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


  std_examFileErr = '';
  std_exam_file = '';
  std_examfileChange(element) {
    let files = element.target.files;
    this.std_examfileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.stdformData.append("std_exam_file", files[0], files[0].name);
      this.std_exam_file = files[0].name;
    }else{
      this.std_examfileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }
  removestd_examFiles(){
    this.std_exam_file = '';
    this.stdformData.delete('std_exam_file');
  }


  stdfileData:any=[];
  editStandardFiles(content,row_id)
  {
    this.approved_std_examfileErrors = '';
    this.approved_recycle_examFileErrors = '';
    this.approved_social_examFileErrors = '';
    this.approved_witness_fileErrors = '';
    this.approved_qua_exam_fileErrors='';
    
    this.stdfileData = this.standard_approvedEntries[row_id];

    this.stdfileform.patchValue({
      'approved_witness_date':this.errorSummary.editDateFormat(this.stdfileData.witness_date),
      'approved_approval_date':this.errorSummary.editDateFormat(this.stdfileData.approval_date),
      'approved_valid_until':this.errorSummary.editDateFormat(this.stdfileData.witness_valid_until),
      'approved_pre_qualification' : this.stdfileData.pre_qualification
    });

    this.approved_std_exam_file = this.stdfileData.standard_exam_file;
    this.approved_recycle_exam_file = this.stdfileData.recycle_exam_file;
    this.approved_witness_file = this.stdfileData.witness_file;
    this.approved_qua_exam_file = this.stdfileData.qua_exam_file;
   
    this.approved_social_exam_file = this.stdfileData.social_course_exam_file;
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }


  bgroupfileData:any=[];
  editBgroupDetails(content,row_id)
  {
    this.approved_examfileErrors = '';
    this.approved_technicalFileErrors = '';

    this.bgroupfileData = this.business_approvedEntries[row_id];

    this.approved_examfilename = this.bgroupfileData.examfilename;
    this.approved_technicalfilename = this.bgroupfileData.technicalfilename;

    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  bsectorgroupcodeid:number;
  editBgroupDateDetails(content,row_id,codeid,date)
  {
    this.bsectorgroupcodeid = codeid;
    this.bgroupfileData = this.business_approvedEntries[row_id];
    
    this.bgroupdateform.patchValue({
      'approved_approval_date':this.errorSummary.editDateFormat(date),
    });

    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }  


  approved_examfileErrors = '';
  approved_examfilename = '';
  bgroupfileformData:FormData = new FormData();
  approved_examfilenameChange(element) 
  {
    let files = element.target.files;
    this.approved_examfileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.bgroupfileformData.append("examFileNames", files[0], files[0].name);
      this.approved_examfilename = files[0].name;
    }else{
      this.approved_examfileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }
  removeapproved_examfilename(){
    this.approved_examfilename = '';
    this.bgroupfileformData.delete('examFileNames');
  }


  approved_technicalFileErrors = '';
  approved_technicalfilename = '';
  approved_technicalfilenameChange(element) 
  {
    let files = element.target.files;
    this.approved_technicalFileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.bgroupfileformData.append("technicalInterviewFileNames", files[0], files[0].name);
      this.approved_technicalfilename = files[0].name;
    }else{
      this.approved_technicalFileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }
  removeapproved_technicalfilename(){
    this.approved_technicalfilename = '';
    this.bgroupfileformData.delete('technicalInterviewFileNames');
  }


  approved_std_examfileErrors = '';
  approved_std_exam_file = '';
  stdfileformData:FormData = new FormData();
  approved_std_examfileChange(element) {
    let files = element.target.files;
    this.approved_std_examfileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.stdfileformData.append("std_exam_file", files[0], files[0].name);
      this.approved_std_exam_file = files[0].name;
    }else{
      this.approved_std_examfileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }
  removeapproved_std_examFiles(){
    this.approved_std_exam_file = '';
    this.stdfileformData.delete('std_exam_file');
  }


  approved_recycle_examFileErrors = '';
  approved_recycle_exam_file = '';
  approved_recycle_examfileChange(element) {
    let files = element.target.files;
    this.approved_recycle_examFileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.stdfileformData.append("recycle_exam_file", files[0], files[0].name);
      this.approved_recycle_exam_file = files[0].name;
    }else{
      this.approved_recycle_examFileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }
  removeapproved_recycle_examFiles(){
    this.approved_recycle_exam_file = '';
    this.stdfileformData.delete('recycle_exam_file');
  }


  approved_witness_fileErrors = '';
  approved_witness_file = '';
  approved_witness_date = '';
  approved_approval_date = '';
  approved_witnessfileChange(element) {
    let files = element.target.files;
    this.approved_witness_fileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.stdfileformData.append("witness_file", files[0], files[0].name);
      this.approved_witness_file = files[0].name;
    }else{
      this.approved_witness_fileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }
  removeapproved_std_witnessFiles(){
    this.approved_witness_file = '';
    this.stdfileformData.delete('witness_file');
  }

  approved_qua_exam_file='';
  approved_qua_exam_fileErrors ='';
  approved_qua_examfileChange(element) {
    let files = element.target.files;
    this.approved_qua_exam_fileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.stdfileformData.append("qua_exam_file", files[0], files[0].name);
      this.approved_qua_exam_file = files[0].name;
    }else{
      this.approved_qua_exam_fileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }
  removeapproved_qua_examFiles(){
    this.approved_qua_exam_file = '';
    this.stdfileformData.delete('qua_exam_file');
  }

  approved_social_examFileErrors = '';
  approved_social_exam_file = '';
  approved_social_examfileChange(element) {
    let files = element.target.files;
    this.approved_social_examFileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.stdfileformData.append("social_exam_file", files[0], files[0].name);
      this.approved_social_exam_file = files[0].name;
    }else{
      this.approved_social_examFileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }
  removeapproved_social_examFiles(){
    this.approved_social_exam_file = '';
    this.stdfileformData.delete('social_exam_file');
  }

  qua_examfileErrors='';
  qua_exam_file='';

  qua_examfileChange(element){
    let files = element.target.files;
    this.qua_examfileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.stdformData.append("qua_exam_file", files[0], files[0].name);
      this.qua_exam_file = files[0].name;
    }else{
      this.qua_examfileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }




  witness_fileErrors = '';
  witness_file = '';
  witnessfileChange(element) {
    let files = element.target.files;
    this.witness_fileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.stdformData.append("witness_file", files[0], files[0].name);
      this.witness_file = files[0].name;
    }else{
      this.witness_fileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }

  removequa_examFiles(){
    this.qua_exam_file = '';
    this.stdformData.delete('qua_exam_file');
  }
  removestd_witnessFiles(){
    this.witness_file = '';
    this.stdformData.delete('witness_file');
  }

  witnessfileformData:FormData = new FormData();
  witnesspopup_fileErrors = '';
  witnessfileChange_popup(element) {
    let files = element.target.files;
    this.witnesspopup_fileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.witnessfileformData.append("witness_file", files[0], files[0].name);
      this.model.popup_witness_file = files[0].name;
    }else{
      this.witnesspopup_fileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }

  removepopup_std_witnessFiles(){
    this.model.popup_witness_file = '';
    this.witnessfileformData.delete('witness_file');
  }

  recycle_examFileErr = '';
  recycle_exam_file = '';
  recycle_examfileChange(element) {
    let files = element.target.files;
    this.recycle_examFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.stdformData.append("recycle_exam_file", files[0], files[0].name);
      this.recycle_exam_file = files[0].name;
    }else{
      this.recycle_examFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }
  removerecycle_examFiles(){
    this.recycle_exam_file = '';
    this.stdformData.delete('recycle_exam_file');
  }
  removesocial_examFiles(){
    this.social_exam_file = '';
    this.stdformData.delete('social_exam_file');
  }
  
  social_examFileErr = '';
  social_exam_file = '';
  social_examfileChange(element) {
    let files = element.target.files;
    this.social_examFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.stdformData.append("social_exam_file", files[0], files[0].name);
      this.social_exam_file = files[0].name;
    }else{
      this.social_examFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }
  removeresocial_examFiles(){
    this.social_exam_file = '';
    this.stdformData.delete('social_exam_file');
  }



  academicFileErr = '';
  academic_file = '';
  uploadedacademicFileNames:any='';
  removeacademicFile(){
    this.academic_file = '';
    this.formData.delete('academic_file');
  }

  academicfileChange(element) {
    
     let files = element.target.files;
     
     for (let i = 0; i < files.length; i++) {
      
       let fileextension = files[i].name.split('.').pop();
       if(this.errorSummary.checkValidDocs(fileextension))
       {
         this.uploadedacademicFileNames = files[i].name; //{name:files[i].name,added:1,deleted:0,valIndex:this.qualificationIndex};
       }else{
         this.academic_certificateErrors='Please upload valid files';
         element.target.value = '';
         return false;
       }
     }
     this.qformData.append("academicfiles", files[0], files[0].name);
     /*
     for (let i = 0; i < files.length; i++) {
       this.qformData.append("academicfiles["+this.qualificationIndex+"]", files[i], files[i].name);
     }
     */
     element.target.value = '';
     this.academic_certificateErrors = '';
   }
  academicfilterFile(experienceValIndex){
    if(experienceValIndex!==null && this.uploadedacademicFileNames.length>0){
      return this.uploadedacademicFileNames[experienceValIndex];
    }else{
      return null;
    }
  }
  removeacademicFiles(){
    /*let certValIndex=0;
    if(this.qualificationIndex >=0 && this.qualificationIndex !==null){
      certValIndex = this.qualificationIndex;
    }else{
      certValIndex = this.qualificationEntries.length;
    }
    this.uploadedacademicFileNames.splice(certValIndex, 1);
    */
    this.uploadedacademicFileNames = '';
    this.academic_certificateErrors = '';
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


  get filterUser(){
    return this.userListEntries.filter(x=>x.deleted!=1);
  }

  get filterRejectedUser(){
    return this.userListEntriesRejected.filter(x=>x.deleted!=1);
  }
  removeUser(i) {

    /*
    let index= this.userListEntries.findIndex(x => x.username ==  username);

    if(index != -1)
      this.userListEntries[index].deleted =1;
    */
    this.userListEntries[i].deleted =1;
    
    this.userIndex=this.userListEntries.length;
  }
  removeRejectUser(i) {

    this.userListEntriesRejected[i].deleted =1;
    
    //this.userIndex=this.userListEntries.length;
  }
  

  chkLoginRequired(){
    let role_id = this.userloginForm.get('role_id').value;
    let roledata= this.roleList.find(s => s.id ==  role_id);
    if(roledata && (roledata.resource_access=="3" || roledata.resource_access=="4")){
      this.userloginForm.patchValue({
        'username':'',
        'user_password':''
      });
      this.userloginForm.controls['username'].disable();
      this.userloginForm.controls['user_password'].disable();
    }else{
      this.userloginForm.controls['username'].enable();
      this.userloginForm.controls['user_password'].enable();
    }
    
  }

  userListEntries = [];
  userListEntriesWaitingApproval = [];
  userListEntriesApproved = [];
  userListEntriesRejected = [];
  uniqueRoleListEntriesApproved = [];

  userIndex:number=null;
  userErrors = '';
  usernameErrors = '';
  role_idErrors = '';
  franchise_idErrors = '';

  roleEditStatus=false;
  roleButtonLoad = false;
  addUser(){
    //console.log(this.userrolefulledit);
    //console.log(this.userIndex);
    this.userErrors ='';
    //this.uf.username.markAsTouched();
    //this.uf.user_password.markAsTouched();
    this.uf.role_id.markAsTouched();
    this.uf.franchise_id.markAsTouched();
    this.usernameErrors = '';
    this.role_idErrors = '';
    this.franchise_idErrors = '';
    
    //console.log(this.userloginForm.valid);
    if(this.userloginForm.valid)
    {

      this.buttonDisable = true;
      //let username = this.userloginForm.get('username').value;
      //let user_password = this.userloginForm.get('user_password').value;
      
      let role_id = this.userloginForm.get('role_id').value;
      let franchise_id = this.userloginForm.get('franchise_id').value;

      let franchise_name= this.franchiseList.find(s => s.id ==  franchise_id).osp_details;
      let role= this.roleList.find(s => s.id ==  role_id);
      let role_name =role.role_name;
      let resource_access =role.resource_access;
      let expobject:any=[];
      //expobject["id"] = selproduct.id;
      //expobject["username"] = username;
      //expobject["user_password"] = user_password;
      expobject["role_name"] = role_name;
      expobject["resource_access"] = resource_access;
      expobject["franchise_name"] = franchise_name;

      expobject["role_id"] = role_id;
      expobject["franchise_id"] = franchise_id;
      expobject["deleted"] = 0;
      expobject["editable"] = 1;
      expobject["editable"] = 1;
      expobject["user_role_id"] = 0;
      

        let sameFranStderror:any={};
        let userListEntries = this.userListEntries.filter(x=>x.deleted==0);
        if(userListEntries && userListEntries.length>0){
          userListEntries.forEach((element,index)=>{
            if(element.franchise_id == franchise_id && element.role_id == role_id && index!=this.userIndex){
              sameFranStderror.role_id = ['The Combination of '+element.role_name+' with franchise has been taken already'];
            }
			/*
            if((resource_access == "2" || resource_access == "1" || resource_access == "5" ) && element.username == username && index!=this.userIndex){
              sameFranStderror.username = ['The Username has been taken already'];
            }
			*/
            
          })
        }

        let userListEntriesWaitingApproval = this.userListEntriesWaitingApproval;
        
        if(userListEntriesWaitingApproval && userListEntriesWaitingApproval.length>0){
          userListEntriesWaitingApproval.forEach((element,index)=>{
          
            if(element.franchise_id == franchise_id && element.role_id == role_id && index!=this.userIndex){
              sameFranStderror.role_id = ['The Combination of '+element.role_name+' with franchise has been taken already'];
            }
			/*
            if((resource_access == "2" || resource_access == "1" || resource_access == "5" ) && element.username == username && index!=this.userIndex){
              sameFranStderror.username = ['The Username has been taken already'];
            }
			*/
            
          })
        }

        let userListEntriesApproved = this.userListEntriesApproved;
        if(userListEntriesApproved && userListEntriesApproved.length>0){
          userListEntriesApproved.forEach((element,index)=>{

            if(element.franchise_id == franchise_id && element.role_id == role_id && index!=this.userIndex){
              sameFranStderror.role_id = ['The Combination of '+element.role_name+' with franchise has been taken already'];
            }
			
			/*
            if((resource_access == "2" || resource_access == "1" || resource_access == "5" ) && element.username == username && index!=this.userIndex){
              sameFranStderror.username = ['The Username has been taken already'];
            }
			*/
            
          })
        }
        let userListEntriesRejected = this.userListEntriesRejected;
        if(userListEntriesRejected && userListEntriesRejected.length>0){
          userListEntriesRejected.forEach((element,index)=>{
            if(element.franchise_id == franchise_id && element.role_id == role_id && index!=this.userIndex){
              sameFranStderror.role_id = ['The Combination of '+element.role_name+' with franchise has been taken already'];
            }
			
			/*
            if((resource_access == "2" || resource_access == "1" || resource_access == "5" ) && element.username == username && index!=this.userIndex){
              sameFranStderror.username = ['The Username has been taken already'];
            }
			*/
            
          })
        }
        
        //if(sameFranStderror.role_id!==undefined || sameFranStderror.username!==undefined)
			
		  if(sameFranStderror.role_id!==undefined)	
		  {          
        this.error = this.errorSummary.getErrorSummary(sameFranStderror,this,this.userloginForm); 
        this.buttonDisable = false;
        return false;
      }
      
      //console.log('--'+this.userListEntries.length);
      if(this.userIndex===null || this.userListEntries.length==0)
      {
        //if(this.userIndex===null){
          this.userIndex = this.userListEntries.length;
        //}

        let formvalue = this.userloginForm.value;
        formvalue.user_id = this.id;

        if(resource_access == "2" || resource_access == "1" || resource_access == "5" ){
          this.userService.checkUserName(formvalue)
          .pipe(first())
          .subscribe(res => {

              if(res.status == 0){
                this.error = this.errorSummary.getErrorSummary(res.message,this,this.userloginForm); 
              }else{

                //this.userListEntries[this.userIndex] = expobject;
                //this.userIndex=this.userListEntries.length;   
                //this.loginFormreset();
				this.addUserRole(expobject);

                // this.userListEntries.push(expobject);
                //this.userIndex=this.userListEntries.length;   
                //this.userloginForm.reset(); 
              } 
              this.buttonDisable = false;
          },
          error => {
              this.error = error;
              this.buttonDisable = false;
          });
        }else{
          //this.userListEntries[this.userIndex] = expobject;
          //this.userIndex=this.userListEntries.length;   
          //this.loginFormreset();
		    this.addUserRole(expobject);
          this.buttonDisable = false;
        }

        
        
      }
      else
      {
      /*
        let sameFranStderror:any={};
        let userListEntries = this.userListEntries.filter(x=>x.deleted==0);
        if(userListEntries.length>0){
          userListEntries.forEach((element,index)=>{
            if(element.franchise_id == franchise_id && element.role_id == role_id && index!=this.userIndex){
              sameFranStderror.role_id = ['The Combination of '+element.role_name+' with franchise has been taken already'];
            }
            if((resource_access == "2" || resource_access == "1") && element.username == username && index!=this.userIndex){
              sameFranStderror.username = ['The Username has been taken already'];
            }
            
          })
        }

        let userListEntriesWaitingApproval = this.userListEntriesWaitingApproval;
        if(userListEntriesWaitingApproval.length>0){
          userListEntriesWaitingApproval.forEach((element,index)=>{
            if(element.franchise_id == franchise_id && element.role_id == role_id && index!=this.userIndex){
              sameFranStderror.role_id = ['The Combination of '+element.role_name+' with franchise has been taken already'];
            }
            if((resource_access == "2" || resource_access == "1") && element.username == username && index!=this.userIndex){
              sameFranStderror.username = ['The Username has been taken already'];
            }
            
          })
        }

        let userListEntriesApproved = this.userListEntriesApproved;
        if(userListEntriesApproved.length>0){
          userListEntriesApproved.forEach((element,index)=>{
            if(element.franchise_id == franchise_id && element.role_id == role_id && index!=this.userIndex){
              sameFranStderror.role_id = ['The Combination of '+element.role_name+' with franchise has been taken already'];
            }
            if((resource_access == "2" || resource_access == "1") && element.username == username && index!=this.userIndex){
              sameFranStderror.username = ['The Username has been taken already'];
            }
            
          })
        }
        let userListEntriesRejected = this.userListEntriesRejected;
        if(userListEntriesRejected.length>0){
          userListEntriesRejected.forEach((element,index)=>{
            if(element.franchise_id == franchise_id && element.role_id == role_id && index!=this.userIndex){
              sameFranStderror.role_id = ['The Combination of '+element.role_name+' with franchise has been taken already'];
            }
            if((resource_access == "2" || resource_access == "1") && element.username == username && index!=this.userIndex){
              sameFranStderror.username = ['The Username has been taken already'];
            }
            
          })
        }



        //sameFranStderror = {"role_id":['The Combination of  ith franchise has been taken already']};
        //console.log(sameFranStderror);
        
        if(sameFranStderror.role_id!==undefined || sameFranStderror.username!==undefined){
          
          this.error = this.errorSummary.getErrorSummary(sameFranStderror,this,this.userloginForm); 
          this.buttonDisable = false;
        }else{
        */
          
          let formvalue = this.userloginForm.value;
          formvalue.user_id = this.id;

          if(resource_access == "2" || resource_access == "1" || resource_access == "5" ){
            this.userService.checkUserName(formvalue)
            .pipe(first())
            .subscribe(res => {

                if(res.status == 0){
                  this.error = this.errorSummary.getErrorSummary(res.message,this,this.userloginForm); 
                }else{
				  /*
                  if(this.userIndex!==undefined){
                    this.userListEntries[this.userIndex] = expobject;
                  }else{
                    this.userListEntries.push(expobject);
                  }
				  */
				  this.addUserRole(expobject);
                  // this.userListEntries.push(expobject);
                  this.userIndex=this.userListEntries.length;   
                  //this.loginFormreset();
                } 
                this.buttonDisable = false;
            },
            error => {
                this.error = error;
                this.buttonDisable = false;
            });
          }else{
			/*  
            if(this.userIndex!==undefined){
              this.userListEntries[this.userIndex] = expobject;
            }else{
              this.userListEntries.push(expobject);
            }
			*/
			
			this.addUserRole(expobject);
			
            this.userIndex=this.userListEntries.length;   
            //this.loginFormreset();
            this.buttonDisable = false; 
          }

          

          
        //}
      
      }
     // console.log(this.userIndex);
     
    } 
  }
  editUser(index:number){
	this.roleEditStatus=true;
    this.userIndex= index;
    let qual = this.userListEntries[index];
    /*
     username: qual.username,
      user_password: qual.user_password,*/
    this.userloginForm.patchValue({
      username: qual.username,
      user_password: qual.user_password,
      role_id: qual.role_id,
      franchise_id: qual.franchise_id
    });
    this.scrollToBottom();
  }

  loginFormreset(){
    this.userloginForm.reset();
    if(this.userType == 3){
      this.userloginForm.patchValue({
        'franchise_id':this.userdetails.uid
      });
    }
  }
  
  addUserRole(expobject)
  {
	  this.loadingArr['userloginForm'] = true;
	             
      let roledatas = [];
      roledatas.push({user_password:expobject["user_password"],username:expobject["username"],user_role_id:expobject["user_role_id"],role_id:expobject["role_id"],franchise_id:expobject["franchise_id"],deleted:expobject["deleted"],editable:expobject["editable"]});
          
      let formvalue:any={};
      formvalue.actiontype = 'role';
      formvalue.roles = roledatas;
      formvalue.id = this.id;
      this.roleformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.roleformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status)
		  {

            this.success = {summary:res.message};
            this.buttonDisable = false;
            //this.userListEntries = res.role;
			this.getUserData('role');            
            
            setTimeout(() => {
				//this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
				this.roleEditStatus=false;
				this.loginFormreset();
            }, this.errorSummary.redirectTime);
            
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.roleformData);	
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['userloginForm'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['userloginForm'] = false;
      });
  }


  

  addUserBK(){
    //console.log(this.userrolefulledit);
    //console.log(this.userIndex);
    this.userErrors ='';
    this.uf.username.markAsTouched();
    this.uf.user_password.markAsTouched();
    this.uf.role_id.markAsTouched();
    this.uf.franchise_id.markAsTouched();
    this.usernameErrors = '';
    this.role_idErrors = '';
    this.franchise_idErrors = '';
    
    //console.log(this.userloginForm.valid);
    if(this.userloginForm.valid)
    {

      this.buttonDisable = true;
      let username = this.userloginForm.get('username').value;
      let user_password = this.userloginForm.get('user_password').value;
      
      let role_id = this.userloginForm.get('role_id').value;
      let franchise_id = this.userloginForm.get('franchise_id').value;

      let franchise_name= this.franchiseList.find(s => s.id ==  franchise_id).osp_details;
      let role= this.roleList.find(s => s.id ==  role_id);
      let role_name =role.role_name;
      let resource_access =role.resource_access;
      let expobject:any=[];
      //expobject["id"] = selproduct.id;
      expobject["username"] = username;
      expobject["user_password"] = user_password;
      expobject["role_name"] = role_name;
      expobject["resource_access"] = resource_access;
      expobject["franchise_name"] = franchise_name;

      expobject["role_id"] = role_id;
      expobject["franchise_id"] = franchise_id;
      expobject["deleted"] = 0;
      expobject["editable"] = 1;
      expobject["editable"] = 1;
      expobject["user_role_id"] = 0;
      
      
      //console.log('--'+this.userListEntries.length);
      if(this.userIndex===null || this.userListEntries.length==0)
      {
        //if(this.userIndex===null){
          this.userIndex = this.userListEntries.length;
        //}

        let formvalue = this.userloginForm.value;
        formvalue.user_id = this.id;

        if(resource_access == "2" || resource_access == "1" ){
          this.userService.checkUserName(formvalue)
          .pipe(first())
          .subscribe(res => {

              if(res.status == 0){
                this.error = this.errorSummary.getErrorSummary(res.message,this,this.userloginForm); 
              }else{

                this.userListEntries[this.userIndex] = expobject;
                this.userIndex=this.userListEntries.length;   
                this.userloginForm.reset(); 

                // this.userListEntries.push(expobject);
                //this.userIndex=this.userListEntries.length;   
                //this.userloginForm.reset(); 
              } 
              this.buttonDisable = false;
          },
          error => {
              this.error = error;
              this.buttonDisable = false;
          });
        }else{
          this.userListEntries[this.userIndex] = expobject;
          this.userIndex=this.userListEntries.length;   
          this.userloginForm.reset(); 
          this.buttonDisable = false;
        }

        
        
      }
      else
      {
        let sameFranStderror:any={};
        let userListEntries = this.userListEntries.filter(x=>x.deleted==0);
        if(userListEntries.length>0){
          userListEntries.forEach((element,index)=>{
            if(element.franchise_id == franchise_id && element.role_id == role_id && index!=this.userIndex){
              sameFranStderror.role_id = ['The Combination of '+element.role_name+' with franchise has been taken already'];
            }
            if((resource_access == "2" || resource_access == "1") && element.username == username && index!=this.userIndex){
              sameFranStderror.username = ['The Username has been taken already'];
            }
            
          })
        }
        //sameFranStderror = {"role_id":['The Combination of  ith franchise has been taken already']};
        //console.log(sameFranStderror);
        
        if(sameFranStderror.role_id!==undefined || sameFranStderror.username!==undefined){
          
          this.error = this.errorSummary.getErrorSummary(sameFranStderror,this,this.userloginForm); 
          this.buttonDisable = false;
        }else{
          
          let formvalue = this.userloginForm.value;
          formvalue.user_id = this.id;

          if(resource_access == "2" || resource_access == "1" ){
            this.userService.checkUserName(formvalue)
            .pipe(first())
            .subscribe(res => {

                if(res.status == 0){
                  this.error = this.errorSummary.getErrorSummary(res.message,this,this.userloginForm); 
                }else{
                  if(this.userIndex!==undefined){
                    this.userListEntries[this.userIndex] = expobject;
                  }else{
                    this.userListEntries.push(expobject);
                  }
                  // this.userListEntries.push(expobject);
                  this.userIndex=this.userListEntries.length;   
                  this.userloginForm.reset(); 
                } 
                this.buttonDisable = false;
            },
            error => {
                this.error = error;
                this.buttonDisable = false;
            });
          }else{
            if(this.userIndex!==undefined){
              this.userListEntries[this.userIndex] = expobject;
            }else{
              this.userListEntries.push(expobject);
            }
            this.userIndex=this.userListEntries.length;   
            this.userloginForm.reset();
            this.buttonDisable = false; 
          }

          

          
        }
      
      }
     // console.log(this.userIndex);
     
    } 
  }
  editUserBk(index:number){
    this.userIndex= index;
	  let qual = this.userListEntries[index];
    this.userloginForm.patchValue({
      username: qual.username,
      user_password: qual.user_password,
      role_id: qual.role_id,
      franchise_id: qual.franchise_id
    });
  }






  removeAcademic(index:number) {
    //let index= this.qualificationEntries.findIndex(s => s.id ==  productId);
    if(index != -1)
      this.qualificationEntries.splice(index,1);
    
    this.qualificationIndex=this.qualificationEntries.length;
  }
  
  qualificationEditStatus=false;
  qualificationStatus=true;
  qualificationIndex=0;
  addAcademic(){
    this.qualificationErrors ='';
	
	  let id = this.qualificationForm.get('id').value;
    let qualification = this.qualificationForm.get('qualification').value;
    let university = this.qualificationForm.get('university').value;
    let subject = this.qualificationForm.get('subject').value;
    let start_year = this.qualificationForm.get('start_year').value;
    let end_year = this.qualificationForm.get('end_year').value;
    //let academic_certificate = this.qualificationForm.get('academic_certificate').value;

    this.qf.qualification.markAsTouched();
    this.qf.university.markAsTouched();
    this.qf.subject.markAsTouched();
    this.qf.start_year.markAsTouched();
    this.qf.end_year.markAsTouched();
    
    if(this.uploadedacademicFileNames == undefined || this.uploadedacademicFileNames ==''){
      this.academic_certificateErrors = 'Please upload the Certificate';
      this.certificateStatus=false;
    }else{
      this.academic_certificateErrors = '';
    }
	    
    //let entry= this.qualificationEntries.find(s => s.id ==  productId);
    if(this.qualificationForm.valid && this.academic_certificateErrors=='')
	{
      /*
	  let expobject:any=[];
      expobject["qualification"] = qualification;
      expobject["university"] = university;
      expobject["subject"] = subject;
      expobject["start_year"] = start_year ;
      expobject["end_year"] = end_year;
	  */
	  
      let academic_certificate = this.uploadedacademicFileNames;
      //expobject["academic_certificate"] = academic_certificate;
	  
      /*
      if(this.qualificationIndex!==null){
      this.qualificationEntries[this.qualificationIndex] = expobject;
      }else{
        this.qualificationEntries.push(expobject);
      }
	  */
	  
	  this.loadingArr['qualification'] = true;
	  
      let qualificationdatas = [];
      qualificationdatas.push({id:id,academic_certificate:academic_certificate,qualification:qualification,board_university:university,subject:subject,start_year:start_year,end_year:end_year});
            
      let formvalue:any={};
      formvalue.qualifications = qualificationdatas;
      formvalue.actiontype = 'qualification';
      formvalue.id = this.id;
      this.qformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.qformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status)
		  {
			/*
            this.qualificationEntries = res['resultarr']['qualifications'];
            this.qualificationEntries.forEach((x,index)=>{
                if(x.academic_certificate){
					this.uploadedacademicFileNames[index]= {name:x.academic_certificate,added:0,deleted:0,valIndex:index};
				}else{
					this.uploadedacademicFileNames[index]= '';
				}
            });
			*/
			
			this.success = {summary:res.message};
			this.buttonDisable = false;			
			this.getUserData('qualification');
			
			setTimeout(() => {
        this.qualificationForm.reset();
        this.uploadedacademicFileNames = '';
        this.qformData = new FormData();
				this.loadingArr['qualification'] = false;
				this.qualificationEditStatus=false;
				this.qualificationForm.patchValue({
					qualification: '',
					university: '',
					subject: '',
					start_year: '',
					end_year: ''
				});
			}, this.errorSummary.redirectTime);
            
            //this.submittedSuccess =1;
          }else if(res.status == 0){
            //this.submittedError =1;
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.customerForm);	
          }else{
            //this.submittedError =1;
            this.error = {summary:res};
          }
          this.loadingArr['qualification'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['qualification'] = false;
      });
	  
      
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
     
      //}
      this.qualificationIndex=this.qualificationEntries.length;
    }
  }

  editAcademic(index:number){
    // let prd= this.qualificationEntries.find(s => s.id ==  productId);
	
	  this.qualificationEditStatus=true;
    this.qualificationIndex= index;
    let qual = this.qualificationEntries[index];
	
	  this.academicEndYearChange(qual.start_year);
	
    /*
    ,
      academic_certificate: qual.academic_certificate 
    */
    this.uploadedacademicFileNames = qual.academic_certificate;
    this.qualificationForm.patchValue({
	  id: qual.id,
      qualification: qual.qualification,
      university: qual.university,
      subject: qual.subject,
      start_year: qual.start_year,
      end_year: qual.end_year
    });
    this.scrollToBottom();
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
  
  experienceEditStatus=false;
  experienceStatus=true;
  experienceIndex=0;
  addExperience(){
    this.experienceErrors ='';

    this.experienceStatus=true;  
	let id = this.experienceForm.get('id').value;
    let experience = this.experienceForm.get('experience').value;
    let job_title = this.experienceForm.get('job_title').value;
    let responsibility = this.experienceForm.get('responsibility').value;
    let exp_from_date = this.errorSummary.displayDateFormat(this.experienceForm.get('exp_from_date').value);
	let exp_to_date = this.errorSummary.displayDateFormat(this.experienceForm.get('exp_to_date').value);
    
    this.ef.experience.markAsTouched();
    this.ef.job_title.markAsTouched();
    this.ef.responsibility.markAsTouched();
    this.ef.exp_from_date.markAsTouched();
    this.ef.exp_to_date.markAsTouched();
    
    if(this.experienceForm.valid){
		/*
		//let entry= this.experienceEntries.find(s => s.id ==  productId);
		  let expobject:any=[];
		  //expobject["id"] = selproduct.id;
		  expobject["experience"] = experience;
		  expobject["job_title"] = job_title;
		  expobject["responsibility"] = responsibility;
		  if(exp_from_date!=='')
		  expobject["exp_from_date"] = this.errorSummary.displayDateFormat(exp_from_date);

		  if(exp_to_date!=='')
		  expobject["exp_to_date"] = this.errorSummary.displayDateFormat(exp_to_date);//this.getDate(exp_to_date);
					
		  if(this.experienceIndex!==null){
			this.experienceEntries[this.experienceIndex] = expobject;
		  }else{
			this.experienceEntries.push(expobject);
		  }
		  
		this.experienceForm.reset();
		*/
		
	   this.loadingArr['experience'] = true;
	  
      
      let experiencedatas = [];     
      experiencedatas.push({id:id,job_title:job_title,experience:experience,responsibility:responsibility,from_date:exp_from_date,to_date:exp_to_date});
           
      let formvalue:any={};
      formvalue.experience = experiencedatas;
      formvalue.actiontype = 'experience';
      formvalue.id = this.id;
      this.expformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.expformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){

			    this.success = {summary:res.message};
				this.buttonDisable = false;
				
				this.experienceForm.reset();
				this.getUserData('experience');
				this.experienceIndex=this.experienceEntries.length;
								
			    setTimeout(() => {
					this.experienceEditStatus=false;			
				}, this.errorSummary.redirectTime);
            
            //this.submittedSuccess =1;
          }else if(res.status == 0){
            //this.submittedError =1;
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.expformData);	
          }else{
            //this.submittedError =1;
            this.error = {summary:res};
          }
          this.loadingArr['experience'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['experience'] = false;
      });
    }   
  }

  audexperienceEditStatus=false;
  auditexperienceStatus=true;
  auditexperienceIndex=0;
  addaudExperience(){
    console.log(this.auditExpForm.value);
	let id = this.auditExpForm.get('id').value;
  let  bussinessSector = this.auditExpForm.get('business_sector').value;
    let year = this.auditExpForm.get('year').value;
    let standard = this.auditExpForm.get('standard').value;
    let company = this.auditExpForm.get('company').value;
    let cbval = this.auditExpForm.get('cb').value;
    let auditrolelist = this.auditExpForm.get('audit_role').value;
    let days = this.auditExpForm.get('days').value;
    //let process = this.auditExpForm.get('process').value;
    
    
    this.aef.business_sector.markAsTouched();
    this.aef.year.markAsTouched();
    this.aef.standard.markAsTouched();
    this.aef.company.markAsTouched();
    this.aef.cb.markAsTouched();
    this.aef.audit_role.markAsTouched();
    this.aef.days.markAsTouched();
    //this.aef.process.markAsTouched();

    if(this.auditExpForm.valid)
    {
      let selstandard = this.standardList.find(s => s.id ==  standard);
      let selcb = this.cbList.find(cb => cb.id ==  cbval);
	  
	  this.loadingArr['audexperience'] = true;
	        
      let audexperiencedatas = [];
      audexperiencedatas.push({id:id,standard:standard,businesssector:bussinessSector,year:year,company:company,cb:cbval,audit_role:auditrolelist,days:days});
          
      let formvalue:any={};
      formvalue.audit_experience = audexperiencedatas;
      formvalue.actiontype = 'audit_experience';
      formvalue.id = this.id;
      this.auditexpformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.auditexpformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status)
		  {
			this.success = {summary:res.message};
			this.buttonDisable = false;
			
			this.getUserData('audit_experience');
			setTimeout(() => {
				//this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
				this.auditExpForm.reset();      
				this.auditexperienceIndex=this.auditexperienceEntries.length;
				this.audexperienceEditStatus=false;
            }, this.errorSummary.redirectTime);
            
            //this.submittedSuccess =1;
          }else if(res.status == 0){
            //this.submittedError =1;
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.expformData);	
          }else{
            //this.submittedError =1;
            this.error = {summary:res};
          }
          this.loadingArr['audexperience'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['audexperience'] = false;
      });
       
	  /*
      //let entry= this.experienceEntries.find(s => s.id ==  productId);
      let expobject:any=[];
      //expobject["id"] = selproduct.id;
      expobject["year"] = year;
      expobject["standard"] = selstandard.id;
      expobject["standard_name"] = selstandard.name;
      //expobject["process"] = process;
      expobject["company"] = company;
      expobject["cb"] = selcb.id;
      expobject["cb_name"] = selcb.name;
      expobject["days"] = days;//this.getDate(exp_to_date);

      if(this.auditexperienceIndex!==null){
        this.auditexperienceEntries[this.auditexperienceIndex] = expobject;
      }else{
        this.auditexperienceEntries.push(expobject);
      }
      this.auditExpForm.reset();
      
      this.auditexperienceIndex=this.auditexperienceEntries.length;
	  */
    }
  }

  consultancyEditStatus=false;
  consultancyexperienceStatus=true;
  consultancyexperienceIndex=0;
  addconExperience(){
    this.consultancyexperienceErrors ='';

    this.auditexperienceStatus=true;  
	let id = this.conExpForm.get('id').value;
    let year = this.conExpForm.get('year').value;
    let standard = this.conExpForm.get('standard').value;
    let company = this.conExpForm.get('company').value;
    let days = this.conExpForm.get('days').value;
    //let process = this.conExpForm.get('process').value;
    
    
    this.cef.year.markAsTouched();
    this.cef.standard.markAsTouched();
    this.cef.company.markAsTouched();
    this.cef.days.markAsTouched();
    //this.cef.process.markAsTouched();
	if(this.conExpForm.valid)
    {
		
		let selstandard = this.standardList.find(s => s.id ==  standard);
		
		/*
		let selstandard = this.standardList.find(s => s.id ==  standard);
			
		//let entry= this.experienceEntries.find(s => s.id ==  productId);
		let expobject:any=[];
		//expobject["id"] = selproduct.id;
		expobject["standard"] = selstandard.id;
		expobject["standard_name"] = selstandard.name;
		//expobject["process"] = process;
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
		*/
	
		this.loadingArr['conexperience'] = true;
	  
      
		let conexperiencedatas = [];
		conexperiencedatas.push({id:id,standard:standard,year:year,company:company,days:days});
      
		let formvalue:any={};
		formvalue.consultancy_experience = conexperiencedatas;
		formvalue.actiontype = 'consultancy_experience';
		formvalue.id = this.id;
		this.consultancyexpformData.append('formvalues',JSON.stringify(formvalue));
      
		this.userService.updateUserData(this.consultancyexpformData)
		.pipe(first())
		.subscribe(res => {

          if(res.status){

			    this.success = {summary:res.message};
				this.buttonDisable = false;
					
				this.consultancyexperienceIndex=this.consultancyexperienceEntries.length;
				
				//this.conExpForm.reset();
				this.resetUserForm('consultancy_experience');
				this.getUserData('consultancy_experience');
			    setTimeout(() => {
					this.consultancyEditStatus=false;				
				 }, this.errorSummary.redirectTime);
            
            //this.submittedSuccess =1;
          }else if(res.status == 0){
            //this.submittedError =1;
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.expformData);	
          }else{
            //this.submittedError =1;
            this.error = {summary:res};
          }
          this.loadingArr['conexperience'] = false;
        },
        error => {
          this.error = error;
          this.loadingArr['conexperience'] = false;
        });
	}	
	  
	  /*
	
	this.loadingArr['audexperience'] = true;
	        
    let conexperiencedatas = [];
    conexperiencedatas.push({standard:selstandard.id,year:year,company:company,days:days});
      
    let formvalue:any={};
    formvalue.audit_experience = audexperiencedatas;
    formvalue.actiontype = 'audit_experience';
	formvalue.id = this.id;
	this.auditexpformData.append('formvalues',JSON.stringify(formvalue));
	  
	  this.userService.updateUserData(this.auditexpformData)
	  .pipe(first())
	  .subscribe(res => {

		   if(res.status){

				this.success = {summary:res.message};
				this.buttonDisable = false;
				setTimeout(() => {
					//this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
					this.getUserData('audit_experience');
				}, this.errorSummary.redirectTime);
			
			//this.submittedSuccess =1;
		  }else if(res.status == 0){
			//this.submittedError =1;
			this.error = this.errorSummary.getErrorSummary(res.message,this,this.expformData);	
		  }else{
			//this.submittedError =1;
			this.error = {summary:res};
		  }
		  this.loadingArr['audexperience'] = false;
	},
	error => {
		  this.error = error;
		  this.loadingArr['audexperience'] = false;
	});
	*/
	
	
  }
  
  getUserData(type)
  {
	this.userService.getUserData({'id':this.id,'actiontype':type})
	  .pipe(first())
	  .subscribe(res => {
		   if(res.status){

				//this.success = {summary:res.message};
				this.buttonDisable = false;
        
        

 

 
 
        //this.userIndex = this.userListEntries.length;
 
       
				if(type=='mapuserrole')
				{
          this.mapuserroleEntries = res.data['mapuserrole'];
          this.mapuserroleIndex = this.mapuserroleEntries.length;
				}else if(type=='consultancy_experience')
				{
          this.consultancyexperienceEntries=res.data['consultancy_experience'];
          this.consultancyexperienceIndex = this.consultancyexperienceEntries.length;
				}else if(type=='experience'){
          this.experienceEntries=res.data['experience'];
          this.experienceIndex = this.experienceEntries.length;
				}else if(type=='certificate'){
					this.certificateEntries=res.data['certifications'];
          this.certificateIndex= this.certificateEntries.length;
          this.uploadedFileNames = '';
          this.upload_certificateErrors = '';
          this.certformData = new FormData();
				}else if(type=='qualification'){
					this.qualificationEntries=res.data['qualifications'];
          this.qualificationIndex=this.qualificationEntries.length;	
          this.uploadedacademicFileNames = '';
          this.qformData = new FormData();
          
				}else if(type=='audit_experience'){	
          this.auditexperienceEntries=res.data['audit_experience'];
          this.auditexperienceIndex = this.auditexperienceEntries.length;
          this.loadingArr['audexperience'] = false;
				}else if(type=='userloginForm'){
									
				}else if(type=='te_business_group'){
					this.teBusinessEntries=res.data['tebusinessgroup_new'];
					this.teBusiness_approvalwaitingEntries=res.data['tebusinessgroup_approvalwaiting'];
					this.teBusiness_approvedEntries=res.data['tebusinessgroup_approved'];
          this.teBusiness_rejectedEntries=res.data['tebusinessgroup_rejected'];
          this.teBusinessIndex = this.teBusinessEntries.length;
				}else if(type=='declaration'){	
					//this.declarationEntries=res.data['declaration'];					
					this.declarationEntries=res.data['declaration_new']?res.data['declaration_new']:[];
					this.declaration_approvalwaitingEntries=res.data['declaration_approvalwaiting']?res.data['declaration_approvalwaiting']:[];
					this.declaration_approvedEntries=res.data['declaration_approved']?res.data['declaration_approved']:[];
          this.declaration_rejectedEntries=res.data['declaration_rejected']?res.data['declaration_rejected']:[];
          this.declarationIndex = this.declarationEntries.length;	
				}else if(type=='business_group' || type=='business_group_code'){
					this.businessEntries=res.data['businessgroup_new']?res.data['businessgroup_new']:[];
					this.business_approvalwaitingEntries=res.data['businessgroup_approvalwaiting']?res.data['businessgroup_approvalwaiting']:[];
    				this.business_approvedEntries=res.data['businessgroup_approved']?res.data['businessgroup_approved']:[];
					this.business_rejectedEntries=res.data['businessgroup_rejected']?res.data['businessgroup_rejected']:[];
					this.businessIndex = this.businessEntries.length;
				}else if(type=='standard'){
					this.standard_approvalwaitingEntries=res.data['standard_approvalwaiting'];
					this.standard_approvedEntries=res.data['standard_approved'];
					this.standard_rejectedEntries=res.data['standard_rejected'];
					this.standardNewList = res.data['standardNewList'];				
				}else if(type=='role'){
					
					this.userListEntries = res.data['role_id']?res.data['role_id']:[];
				    this.userListEntriesWaitingApproval = res.data['role_id_waiting_approval']?res.data['role_id_waiting_approval']:[];
				    this.userListEntriesApproved = res.data['role_id_approved']?res.data['role_id_approved']:[];
				    this.userListEntriesRejected = res.data['role_id_rejected']?res.data['role_id_rejected']:[];

				    this.uniqueRoleListEntriesApproved = res.data['role_id_map_user']?res.data['role_id_map_user']:[];
					
					//this.userListEntries = res.data['role']?res.data['role']:[];
					this.userData.is_auditor = res.data['is_auditor'];
					if(this.userListEntries.length >0 ){
						this.hasRoles = 1;
					}else{
						this.hasRoles = 0;
					}
					this.userIndex=this.userListEntries.length;
				}			 
				/*
				if(type=='audit_experience')
				{
					this.experienceEntries=res.data['audit_experience'];
				}
				*/
				
				/*
				setTimeout(() => {
					this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
				}, this.errorSummary.redirectTime);
				*/
			
			//this.submittedSuccess =1;
		  }else if(res.status == 0){
			//this.submittedError =1;
			this.error = this.errorSummary.getErrorSummary(res.message,this,this.expformData);	
		  }else{
			//this.submittedError =1;
			this.error = {summary:res};
		  }
		  //this.loadingArr['audexperience'] = false;
	},
	error => {
		  this.error = error;
		  //this.loadingArr['audexperience'] = false;
	});
  }
  
  teBusinessGroupEditStatus = false;
  approvedteBusinessGroupEditStatus = false;
  resetUserForm(type)
  {
    if(type=='rejbusiness_group')
	{
      this.brejdetail = '';
      this.rejbusinessformData = new FormData();
      this.rejbusinessForm.reset();
      this.rejtechnicalInterviewFileNames = '';
      this.rejexamFileNames = '';
      this.rejbusinessIndex=null;
    }else if(type=='rejtebusiness_group'){
      this.tebrejdetail = '';
      this.rejTEbusinessformData = new FormData();
      this.rejTEbusinessForm.reset();
      this.rejTEtechnicalInterviewFileNames = '';
      this.rejTEexamFileNames = '';
      this.rejTEbusinessIndex=null;
    }else if(type=='rejectionstandards'){
      this.rejStandardIndex= undefined;
      this.stdrejdetails = '';
      this.rejshowRecycle = false;
      this.rejshowSocial = false;
      this.rejstdformData = new FormData();
      this.rejrecycle_exam_file = '';
      this.rejrecycle_examfileErrors = '';

      this.rejsocial_exam_file  = '';
      this.rejsocial_exam_fileErrors  = '';

      this.rejwitness_file  = '';
      this.rejwitness_fileErrors  = '';
      this.standardRejectionForm.reset();
      this.rejstd_exam_file = '';
      this.rejstd_examfileErrors = '';
      this.rejqua_exam_file = '';
      this.rejqua_examfileErrors = '';
      this.standardRejectionForm.patchValue({
          std_exam_date:'',
          recycle_exam_date:'',
          social_exam_date:'',
          witness_date:'',
          pre_qualification:''
      });
    }else if(type=='userloginForm'){
      this.userloginForm.reset(); 
      this.userloginForm.patchValue({
        role_id: '',
        franchise_id: ''
      });
    }else if(type=='consultancy_experience'){
      this.consultancyEditStatus=false;
      this.consultancyexperienceIndex = this.consultancyexperienceEntries.length;
      this.conExpForm.reset(); 
      this.conExpForm.patchValue({
        year: ''
      });
	}else if(type=='experience'){
      this.experienceEditStatus=false;
      this.experienceForm.reset(); 
      this.experienceIndex=this.experienceEntries.length;
	}else if(type=='certificate'){
      this.cpdEditStatus=false;
      this.certificateIndex=this.certificateEntries.length;
      this.uploadedFileNames = '';
      this.upload_certificateErrors = '';
      this.certformData = new FormData();

      this.certificateForm.reset();	
	}else if(type=='te_business_group'){
      this.teBusinessGroupEditStatus=false;
      this.technicalExpertBsForm.reset();	
      this.TEtechnicalInterviewFileNames = '';
      this.TEexamFileNames = '';
      this.uploadTEtechnicalErrors = '';
      this.uploadTEexamErrors = '';
      this.teBusinessIndex = this.teBusinessEntries.length;
      this.teBusinessformData = new FormData(); 	
	}else if(type=='qualification'){
      this.qformData = new FormData();
      this.qualificationEditStatus=false;
      this.qualificationForm.reset();	
      this.qualificationForm.patchValue({
        qualification: '',
        university: '',
        subject: '',
        start_year: '',
        end_year: ''
      });
      this.uploadedacademicFileNames = '';
      this.qualificationIndex=this.qualificationEntries.length;
      this.academic_certificateErrors = '';
	}else if(type=='audit_experience'){
      this.audexperienceEditStatus=false;
      this.auditexperienceIndex = this.auditexperienceEntries.length;
      this.auditExpForm.reset();	
      
      this.auditExpForm.patchValue({
        year: '',
        standard: '',
        company: '',
        cb: '',
        audit_role:'',
        days: ''
      });				
    }else if(type=='declaration'){
      this.declarationIndex = this.declarationEntries.length;
      this.declarationForm.reset();
      this.declarationForm.patchValue({
        declaration_start_year: '',
        declaration_end_year: '',
        declaration_contract:''
      });
	  this.declarationEditStatus=false;		
	}else if(type=='business_group' || type=='businessgroup'){
      this.businessEditStatus=false;
      this.businessForm.reset();		
      this.technicalInterviewFileNames = '';
      this.examFileNames = '';
      this.uploadtechnicalErrors = '';
      this.uploadexamErrors = '';
      this.businessIndex= this.businessEntries.length;
      this.businessformData = new FormData(); 	
	  }else if(type=='mapuserrole'){
      this.mapbusinessEditStatus = false;
      this.documents = '';
      this.mapUserformData = new FormData();
      this.mapUserRoleForm.reset();		
      this.mapUserRoleForm.patchValue({
        role_id: '',
        standard_id: '',
        business_sector_id: '',
        business_sector_group_id: ''
      });	
      this.mapuserroleIndex= this.mapuserroleEntries.length; 	
	}else if(type=='approvedte_business_group'){
      //this.mapbusinessEditStatus = false;
      this.technicalExpertApprovedBsForm.reset();		
      this.technicalExpertApprovedBsForm.patchValue({
        role_id: '',
        business_sector_id: '',
        business_sector_group_id: ''
      });	      
	}	  
  }

  
  editExperience(index:number){
   // let prd= this.experienceEntries.find(s => s.id ==  productId);
   this.experienceEditStatus=true;
   this.experienceIndex=index;
	  let qual = this.experienceEntries[index];
    this.experienceForm.patchValue({
	  id: qual.id,
      experience: qual.experience,
      job_title: qual.job_title,
      responsibility: qual.responsibility,
	    exp_from_date: this.errorSummary.editDateFormat(qual.exp_from_date),
		  exp_to_date: this.errorSummary.editDateFormat(qual.exp_to_date)
    });
    this.scrollToBottom();
  }

  editauditExperience(index:number){
	this.audexperienceEditStatus=true;
    this.auditexperienceIndex = index;
     let audexp = this.auditexperienceEntries[index];
     //let process = [...audexp.process].map(String);
     
     // process: process
     this.auditExpForm.patchValue({
	   id: audexp.id,
       year: audexp.year,
       standard: audexp.standard_id,
       business_sector : audexp.sector,
       company: audexp.company,
       cb: audexp.cb,
       audit_role: audexp.auditrolelist_id?audexp.auditrolelist_id:"", 
       days: audexp.days
     });
     this.scrollToBottom();
   }

  editconsultancyExperience(index:number)
  {
	this.consultancyEditStatus=true;
    this.consultancyexperienceIndex = index;
    let conexp = this.consultancyexperienceEntries[index];
    //let process = [...conexp.process].map(String);
    //process: process
    this.conExpForm.patchValue({
	  id: conexp.id,
      year: conexp.year,
      standard: conexp.standard,
      company: conexp.company,
      days: conexp.days
      
    });
    this.scrollToBottom();
  }
  
  declarationStarYearChange(element) {
	this.declarationEndYearChange(element.target.value);	
  }
  
  declarationEndYearChange(val) {
	
	this.endYearRange.length = 0;
	
	this.declarationForm.patchValue({
      declaration_end_year: ''
    });
	
    let d_year;
    let d_start_year = val;
	let year = new Date().getFullYear();
	this.endYearRange.push(year);
	for (let i = 1; i <= 50; i++) {
		d_year=year-i;
		if(d_year>d_start_year)
		{
			this.endYearRange.push(d_year);
		}
	}	   
  }
  
  academicStarYearChange(element) {
	this.academicEndYearChange(element.target.value);	
  }
  
  academicEndYearChange(val) {
	
	this.academicEndYearRange.length = 0;
	
	this.qualificationForm.patchValue({
      end_year: ''
    });
	
    let d_year;
    let d_start_year = val;
	let year = new Date().getFullYear();
	this.academicEndYearRange.push(year);
	for (let i = 1; i <= 50; i++) {
		d_year=year-i;
		if(d_year>d_start_year)
		{
			this.academicEndYearRange.push(d_year);
		}
	}  
  }
  
  
  // Certificate Details Code Start Here
  uploadedFileNames:any='';
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
        //this.uploadedFileNames[this.certificateIndex]= {name:files[i].name,added:1,deleted:0,valIndex:this.certificateIndex};
        this.uploadedFileNames = files[i].name;
      }else{
        this.upload_certificateErrors='Please upload valid files';
        element.target.value = '';
        return false;
      }
    }
    this.certformData = new FormData();
    this.certformData.append("uploads", files[0], files[0].name);
    /*for (let i = 0; i < files.length; i++) {
      this.certformData.append("uploads["+this.certificateIndex+"]", files[i], files[i].name);
    }*/
    element.target.value = '';
    this.upload_certificateErrors = '';
    //console.log(this.formData);
  }
  filterFile(experienceValIndex){
    if(experienceValIndex!==null && this.uploadedFileNames.length>0){
      //return this.uploadedFileNames.find(x=>x.experienceValIndex ==experienceValIndex && x.deleted==0 );
      //return this.uploadedFileNames[experienceValIndex];
    }else{
      return null;
    }
  }
  removeFiles(){
    /*let certValIndex=0;
    if(this.certificateIndex >=0 && this.certificateIndex !==null){
      certValIndex = this.certificateIndex;
    }else{
      certValIndex = this.experienceEntries.length;
    }
    */
    this.uploadedFileNames = '';
    this.upload_certificateErrors = '';
    this.certformData = new FormData();
    //this.uploadedFileNames[experienceValIndex];
    //let filenames =   this.uploadedFileNames[experienceValIndex];
    //this.uploadedFileNames = filenames;
    
  }





  removeCertificate(index:number) {
    if(index != -1)
      this.certificateEntries.splice(index,1);
    //this.uploadedFileNames.splice(index, 1);
    this.certificateIndex= this.certificateEntries.length;
  }
  
  cpdEditStatus=false;
  certificateStatus=true;
  certificateIndex=0;
  addCertificate(){
    this.certificateErrors ='';
    this.cerf.certificate_name.markAsTouched();
	this.cerf.training_hours.markAsTouched();	
    this.cerf.completed_date.markAsTouched();
    

    this.certificateStatus=true;
	let id = this.certificateForm.get('id').value;
    let certificate_name = this.certificateForm.get('certificate_name').value;
	let training_hours = this.certificateForm.get('training_hours').value;
	
    let completed_date;
    if(this.certificateForm.get('completed_date').value != ''){
      completed_date = this.errorSummary.displayDateFormat(this.certificateForm.get('completed_date').value);//
    }
    //let upload_certificate = this.certificateForm.get('upload_certificate').value;
    
   	if(this.uploadedFileNames  === undefined || this.uploadedFileNames ==''){
      this.upload_certificateErrors = 'Please upload the Certificate';
      this.certificateStatus=false;
    }else{
      this.upload_certificateErrors = '';
    }
	
	if(!this.certificateForm.valid || !this.certificateStatus)
	{
		return false;
	}
    
	/*	
    //let entry= this.certificateEntries.find(s => s.id ==  productId);
    let expobject:any=[];
    expobject["certificate_name"] = certificate_name;
	expobject["training_hours"] = training_hours;	
    expobject["completed_date"] = completed_date;
	let filename;
	if(this.certificateIndex!==null && this.uploadedFileNames[this.certificateIndex]!==undefined)
	{
		filename = this.uploadedFileNames[this.certificateIndex].name;
	}
    expobject["filename"] = filename;
          
    if(this.certificateIndex!==null){
      this.certificateEntries[this.certificateIndex] = expobject;
    }else{
      this.certificateEntries.push(expobject);
    }
    this.certificateForm.reset();
	*/
	
	let filename;
	if(this.certificateIndex!==null && this.uploadedFileNames !==undefined)
	{
		filename = this.uploadedFileNames;
	}
	
	this.loadingArr['certificateForm'] = true;
        
        
	let certificationdatas = [];
	certificationdatas.push({id:id,certificate_name:certificate_name,training_hours:training_hours,completed_date:completed_date,filename:filename});
		
	let formvalue:any={};
	formvalue.actiontype = 'certificate';
	formvalue.certifications = certificationdatas;
	formvalue.id = this.id;
	this.certformData.append('formvalues',JSON.stringify(formvalue));
	
	this.userService.updateUserData(this.certformData)
	.pipe(first())
	.subscribe(res => {

		if(res.status){
		  /*
		  this.certificateEntries = res['resultarr']['certifications'];
		  this.certificateEntries.forEach((x,index)=>{
			this.uploadedFileNames[index]= {name:x.filename,added:0,deleted:0,valIndex:index};
		  });
		  */
		  this.success = {summary:res.message};
		  this.buttonDisable = false;
		  this.getUserData('certificate');		  		  
		  this.certificateForm.reset();	
		  setTimeout(() => {
			this.cpdEditStatus=false;			
		  }, this.errorSummary.redirectTime);
		  
		}else if(res.status == 0){
		  this.error = this.errorSummary.getErrorSummary(res.message,this,this.certformData);	
		}else{
		  this.error = {summary:res};
		}
		this.loadingArr['certificateForm'] = false;
	},
	error => {
		this.error = error;
		this.loadingArr['certificateForm'] = false;
	});
    
   
  }
  
  editCertificate(index:number){
    // let prd= this.certificateEntries.find(s => s.id ==  productId);
	this.cpdEditStatus=true;
    this.certificateIndex= index;
    let qual = this.certificateEntries[index];
    this.uploadedFileNames = qual.filename;
    this.certificateForm.patchValue({
	    id:qual.id,
      certificate_name: qual.certificate_name,
	    training_hours: qual.training_hours,	  
      completed_date: this.errorSummary.editDateFormat(qual.completed_date),
      filename: qual.filename	 	  
    });
    this.scrollToBottom();
  }
  //Certificate Details Code End Here








  // Business Sector Details Code Start Here
  examFileNames:any='';
  technicalInterviewFileNames:any='';
  uploadexamErrors='';
  uploadtechnicalErrors='';
  
  businessFileChange(element,type) {

    let files = element.target.files;
    for (let i = 0; i < files.length; i++) {
     
      let fileextension = files[i].name.split('.').pop().toLowerCase();
      //console.log('asdasd');
      if(type=='exam'){
        if(this.errorSummary.checkValidDocs(fileextension))
        {
          this.examFileNames = files[i].name;
         // console.log('asdasd');

        }else{
          this.uploadexamErrors='Please upload valid files';
          element.target.value = '';
          return false;
        }
        this.uploadexamErrors='';
        this.businessformData.append("examFileNames", files[i], files[i].name);
      }else{
        if(this.userService.technicalvalidDocs.includes(fileextension))
        {
          this.technicalInterviewFileNames =files[i].name;
          console.log(this.technicalInterviewFileNames);
        }else{
          this.uploadtechnicalErrors='Please upload valid files';
          element.target.value = '';
          return false;
        }
        this.uploadtechnicalErrors='';
        this.businessformData.append("technicalInterviewFileNames", files[i], files[i].name);
      }
    }
    //for (let i = 0; i < files.length; i++) {
      
    //}
    element.target.value = '';
    //this.upload_certificateErrors = '';
  }
 
  businessremoveFiles(type){
    let certValIndex=0;
    if(this.businessIndex >=0 && this.businessIndex !==null){
      certValIndex = this.businessIndex;
    }else{
      certValIndex = this.businessEntries.length;
    }
    if(type=='exam'){
      this.examFileNames = '';
    }else{
      this.technicalInterviewFileNames = '';
    }
    this.uploadtechnicalErrors = '';
    this.uploadexamErrors = '';
  }

  

  // Business Sector Details Code Start Here
  TEexamFileNames:any='';
  TEtechnicalInterviewFileNames:any='';
  uploadTEexamErrors='';
  uploadTEtechnicalErrors='';
  
  TebusinessFileChange(element,type) 
  {

    let files = element.target.files;
    for (let i = 0; i < files.length; i++) 
    {
     
      let fileextension = files[i].name.split('.').pop().toLowerCase();
      //console.log('asdasd');
      if(type=='exam')
      {
        if(this.errorSummary.checkValidDocs(fileextension))
        {
          this.TEexamFileNames = files[i].name;

        }else{
          this.uploadTEexamErrors='Please upload valid files';
          element.target.value = '';
          return false;
        }
        this.uploadTEexamErrors='';
        this.teBusinessformData.append("examFileNames", files[i], files[i].name);
      }
      else
      {
        if(this.userService.technicalvalidDocs.includes(fileextension))
        {
          this.TEtechnicalInterviewFileNames = files[i].name;
        }else{
          this.uploadTEtechnicalErrors='Please upload valid files';
          element.target.value = '';
          return false;
        }
        this.uploadTEtechnicalErrors='';
        this.teBusinessformData.append("technicalInterviewFileNames", files[i], files[i].name);
      }
    }
    //for (let i = 0; i < files.length; i++) {
      
    //}
    element.target.value = '';
    //this.upload_certificateErrors = '';
  }
 
  TebusinessremoveFiles(type)
  {
    let certValIndex=0;
    if(this.teBusinessIndex >=0 && this.teBusinessIndex !==null){
      certValIndex = this.teBusinessIndex;
    }else{
      certValIndex = this.teBusinessEntries.length;
    }
    if(type=='exam'){
      this.TEexamFileNames = '';
    }else{
      this.TEtechnicalInterviewFileNames = '';
    }
    this.uploadTEtechnicalErrors = '';
    this.uploadTEexamErrors = '';
  }



  removeTeBusiness(index:number) {
    if(index != -1)
      this.teBusinessEntries.splice(index,1);

    
    this.TEexamFileNames.splice(index, 1);
    this.TEtechnicalInterviewFileNames.splice(index, 1);
    

    this.teBusinessIndex= this.teBusinessEntries.length;
  }

  removeBusiness(index:number) {
    if(index != -1)
      this.businessEntries.splice(index,1);

    
    this.examFileNames.splice(index, 1);
    this.technicalInterviewFileNames.splice(index, 1);
    

    this.businessIndex= this.businessEntries.length;
  }
  
  businessStatus=true;
  businessIndex=0;

  businessEditStatus=false;
  examfileStatus=false;
  technicalInterviewFileStatus=false;
  sameStandardError = '';
  addBusiness(){

    this.certificateErrors ='';
    this.bf.standard_id.markAsTouched();
    this.bf.business_sector_id.markAsTouched();
    this.bf.business_sector_group_id.markAsTouched();
    this.bf.academic_qualification.markAsTouched();
    

    this.examfileStatus=true;
    this.technicalInterviewFileStatus=true;
    this.uploadexamErrors = '';
    this.uploadtechnicalErrors = '';
    this.sameStandardError = '';
    if(this.businessForm.valid){
      //if(this.businessEntries.fin)
      let standard = this.businessForm.get('standard_id').value;
      let business_sector_id = this.businessForm.get('business_sector_id').value;
      let business_sector_group_id = this.businessForm.get('business_sector_group_id').value;
      let academic_qualification = this.businessForm.get('academic_qualification').value;
      let businessEntries = [...this.businessEntries];
      let id = this.businessForm.get('id').value;

      if(this.businessIndex!==null && this.businessIndex>=0){
        
        if(businessEntries[this.businessIndex]!==undefined){
         
          businessEntries.splice(this.businessIndex,1);
        }
        
      }
      let bindex = businessEntries.filter(x=>x.standard_id == standard && x.business_sector_id == business_sector_id);
      if(bindex.length>0){
        let errorfound = 0;
        business_sector_group_id.forEach(x=>{

          bindex.forEach(y=>{
            if(y.business_sector_group_id_arr.includes(x)){
              errorfound=1;
            }
          });
          
          /*
          let gpindex = this.businessEntries[bindex].business_sector_group_id_arr.findIndex(y=>x==y);
          if(gpindex !==-1){
            errorfound=1;
          }
          */
        });
        if(errorfound){
          this.sameStandardError = 'Business Sector Group was already added';
          return false;
        }
        
      }




      let business_approvalwaitingEntries = [...this.business_approvalwaitingEntries];
      bindex = business_approvalwaitingEntries.filter(x=>x.standard_id == standard && x.business_sector_id == business_sector_id);
      if(bindex.length>0){
        let errorfound = 0;
        business_sector_group_id.forEach(x=>{

          bindex.forEach(y=>{
            if(y.business_sector_group_id_arr.includes(x)){
              errorfound=1;
            }
          });
        });
        if(errorfound){
          this.sameStandardError = 'Business Sector Group was already added';
          return false;
        }
        
      }


      let business_approvedEntries = [...this.business_approvedEntries];
      bindex = business_approvedEntries.filter(x=>x.standard_id == standard && x.business_sector_id == business_sector_id);
      if(bindex.length>0){
        let errorfound = 0;
        business_sector_group_id.forEach(x=>{

          bindex.forEach(y=>{
            if(y.business_sector_group_id_arr.includes(x)){
              errorfound=1;
            }
          });
        });
        if(errorfound){
          this.sameStandardError = 'Business Sector Group was already added';
          return false;
        }
        
      }
      /*
      bindex = this.business_approvalwaitingEntries.findIndex(x=>x.standard_id == standard && x.business_sector_id == business_sector_id);
      if(bindex !== -1){
        let errorfound = 0;
        business_sector_group_id.forEach(x=>{
          if(this.business_approvalwaitingEntries[bindex] && this.business_approvalwaitingEntries[bindex].business_sector_group_id_arr && this.business_approvalwaitingEntries[bindex].business_sector_group_id_arr.length>0){
         // console.log(this.business_approvalwaitingEntries[bindex].business_sector_group_id_arr);
            let gpindex = this.business_approvalwaitingEntries[bindex].business_sector_group_id_arr.findIndex(y=>x==y);
            if(gpindex !==-1){
              errorfound=1;
            }
          }
        });
        if(errorfound){
          this.sameStandardError = 'Same Standard with business sector was already added';
          return false;
        }
      }

      bindex = this.business_approvedEntries.findIndex(x=>x.standard_id == standard && x.business_sector_id == business_sector_id);
      //console.log(1);
      if(bindex !== -1){
        let errorfound = 0;
       // console.log(2);
        business_sector_group_id.forEach(x=>{
        console.log(3);
           if(this.business_approvedEntries[bindex] && this.business_approvedEntries[bindex].business_sector_group_id){
          // console.log(4);
            let gpindex = this.business_approvedEntries[bindex].business_sector_group_id_arr.findIndex(y=>x==y);
            console.log(gpindex);
            if(gpindex !==-1){
            //console.log(5);
              errorfound=1;
            }
          }
        });
        if(errorfound){
          this.sameStandardError = 'Same Standard with business sector was already added';
          return false;
        }
      }

     */
      if(academic_qualification =='2'){
        if(this.examFileNames  === undefined || this.examFileNames ==''){
          this.uploadexamErrors = 'Please upload the Exam File';
          this.examfileStatus=false;
        }else{
          this.uploadexamErrors = '';
        }

        
      }
      if(this.technicalInterviewFileNames  === undefined || this.technicalInterviewFileNames ==''){
        this.uploadtechnicalErrors = 'Please upload the Technical Interview File';
        this.technicalInterviewFileStatus=false;
      }else{
        this.uploadtechnicalErrors = '';
      }
      if(!this.examfileStatus || !this.technicalInterviewFileStatus)
      {
       
        return false;
      }
      
	  /*
      let standard_name = this.standardList.find(x=> x.id == standard).name;
      let business_sector_name = this.bgsectorList.find(x=> x.id == business_sector_id).name;
      let business_sector_group_nameList = this.bgsectorgroupList.filter(x=> business_sector_group_id.includes(x.id) );
      let business_sector_group_name = [];
      business_sector_group_nameList.forEach(element => {
        business_sector_group_name.push(element.group_code);
      });
      //console.log(business_sector_group_name); 

        //let entry= this.certificateEntries.find(s => s.id ==  productId);
      let expobject:any=[];
      expobject["standard_id"] = standard;
      expobject["business_sector_id"] = business_sector_id;
      expobject["business_sector_group_id"] = business_sector_group_id;
      expobject["business_sector_group_id_arr"] = business_sector_group_id;
      
      expobject["academic_qualification"] = academic_qualification;
      expobject["academic_qualification_name"] = academic_qualification==1?'Yes':'No';
      expobject["standard_name"] = standard_name;
      expobject["business_sector_name"] = business_sector_name;
      expobject["business_sector_group_name"] = business_sector_group_name.join(', ');
      expobject["business_sector_group_name_arr"] = business_sector_group_name;
      expobject["examfilename"] = '';
      expobject["technicalfilename"] = '';
      if(academic_qualification =='2'){
        let examfilename = this.examFileNames[this.businessIndex].name;
        expobject["examfilename"] = examfilename;
       
      }
      let technicalfilename = this.technicalInterviewFileNames[this.businessIndex].name;
      expobject["technicalfilename"] = technicalfilename;

      if(this.businessIndex!==null){
        this.businessEntries[this.businessIndex] = expobject;
      }else{
        this.businessEntries.push(expobject);
      } 
	  */
	  
	  let examfilename = '';
	  if(academic_qualification =='2'){
		  examfilename = this.examFileNames;      
    }
    let technicalfilename = this.technicalInterviewFileNames;      
	  
	  this.loadingArr['businessForm'] = true;
	        
      let businessdatas = [];
      businessdatas.push({id,standard_id:standard,business_sector_id:business_sector_id,business_sector_group_code:business_sector_group_id,academic_qualification_status:academic_qualification,examfilename:examfilename,technicalfilename:technicalfilename})
          
      let formvalue:any={};
      formvalue.business_sector_group = businessdatas;
      formvalue.actiontype = 'business_group';
      formvalue.id = this.id;
      this.businessformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.businessformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.businessformData = new FormData();
            this.success = {summary:res.message};
            this.buttonDisable = false;
			
			/*			
            this.businessEntries = res['resultarr']['businessgroup_new'];
             this.businessEntries.forEach((x,index)=>{
        
              if(x.technicalfilename){
                this.technicalInterviewFileNames[index]= {name:x.technicalfilename,added:0,deleted:0,valIndex:index};
                
              }else{
                this.technicalInterviewFileNames[index]= '';
              }
              if(x.academic_qualification ==2){
                
                this.examFileNames[index]= {name:x.examfilename,added:0,deleted:0,valIndex:index};
                
              }else{
                this.examFileNames[index] = '';
                
              }
            });
			*/
            this.getUserData('business_group');
            this.resetUserForm('business_group'); 
            setTimeout(() => {
              this.businessEditStatus=false;
              this.loadingArr['businessForm'] = false;
              this.businessForm.reset();
                  }, this.errorSummary.redirectTime);
            
                }else if(res.status == 0){
                  this.error = this.errorSummary.getErrorSummary(res.message,this,this.businessformData);	
                }else{
                  this.error = {summary:res};
                }
                this.loadingArr['businessForm'] = false;
            },
      error => {
          this.error = error;
          this.loadingArr['businessForm'] = false;
      });    
    }
  }
  
  editBusiness(index:number){
   
    //this.businessformData.delete("technicalInterviewFileNames["+this.businessIndex+"]");
     
	  this.businessEditStatus=true;
    this.businessIndex= index;
    let qual = this.businessEntries[index];
    

    this.technicalInterviewFileNames = qual.technicalfilename;
    this.examFileNames = qual.examfilename;
    let business_sector_group_id = [...qual.business_sector_group_id].map(String);
    //console.log(parseInt(qual);
    this.businessForm.patchValue({
	    id:qual.id,
      standard_id: parseInt(qual.standard_id),
      business_sector_id: ""+qual.business_sector_id,
      business_sector_group_id: business_sector_group_id,
      academic_qualification:qual.academic_qualification
    });
    this.getBgsectorList(qual.standard_id,true);
    this.getBgsectorgroupList(qual.business_sector_id,true);

    this.scrollToBottom();
  }
  //Certificate Details Code End Here
  teBusinessIndex:any = 0;
  editTeBusiness(index:number){
   
    //this.businessformData.delete("technicalInterviewFileNames["+this.businessIndex+"]");
    
	this.approved_business_group_status=false;
	this.new_business_group_status=true;	

    this.teBusinessIndex= index;
    let qual = this.teBusinessEntries[index];
    this.teBusinessGroupEditStatus = true;
    this.TEtechnicalInterviewFileNames = qual.technicalfilename;
    this.TEexamFileNames = qual.examfilename;
    let business_sector_group_id = [...qual.business_sector_group_id].map(String);
    this.technicalExpertBsForm.patchValue({
      id: qual.id,
      role_id: qual.role_id,
      business_sector_id:qual.business_sector_id,
      business_sector_group_id: business_sector_group_id,
      academic_qualification:qual.academic_qualification
    });
    this.getTeBgsectorgroupList(qual.business_sector_id,true,qual.id);
    this.scrollToBottom();
  }
  mapbusinessEditStatus:any=false;
  editMapBusiness(index:number){
   
    //this.businessformData.delete("technicalInterviewFileNames["+this.businessIndex+"]");
     
	  this.mapbusinessEditStatus=true;
    this.businessIndex= index;
    let qual = this.mapuserroleEntries[index];
    //console.log(qual);
   
    let business_sector_group_id = [...qual.business_sector_group_id];//.map(String);
    this.documents = qual.document;
    this.mapUserRoleForm.patchValue({
	    id:qual.id,
      role_id: qual.role_id,
      standard_id: qual.standard_id,
      business_sector_id: qual.business_sector_id,
      business_sector_group_id:business_sector_group_id
    });
    this.getUserBgsectorList(qual.standard_id,true);
    this.getUserBgsectorgroupList(qual.business_sector_id,true);
    this.scrollToBottom();
  }



  teBusinessformData:FormData = new FormData();
  approvedteBusinessformData:FormData = new FormData();
  TEexamfileStatus=false;
  TEtechnicalInterviewFileStatus=false;
  onTeBusinessSubmit(){
    
    this.tebsf.role_id.markAsTouched();
    this.tebsf.business_sector_id.markAsTouched();
    this.tebsf.business_sector_group_id.markAsTouched();
    this.tebsf.academic_qualification.markAsTouched();
    
    this.TEexamfileStatus=true;
    this.TEtechnicalInterviewFileStatus=true;


    if(this.technicalExpertBsForm.valid)
    {

      let role_id = this.technicalExpertBsForm.get('role_id').value;
      let business_sector_id = this.technicalExpertBsForm.get('business_sector_id').value;
      let business_sector_group_id = this.technicalExpertBsForm.get('business_sector_group_id').value;
      let academic_qualification = this.technicalExpertBsForm.get('academic_qualification').value;
      let id = this.technicalExpertBsForm.get('id').value;

      if(academic_qualification =='2'){
        if(this.TEexamFileNames  === undefined || this.TEexamFileNames ==''){
          this.uploadTEexamErrors = 'Please upload the Exam File';
          this.TEexamfileStatus=false;
        }else{
          this.uploadTEexamErrors = '';
        }

        
      }

      if(this.TEtechnicalInterviewFileNames  === undefined || this.TEtechnicalInterviewFileNames =='')
      {
        this.uploadTEtechnicalErrors = 'Please upload the Technical Interview File';
        this.TEtechnicalInterviewFileStatus=false;
      }else{
        this.uploadTEtechnicalErrors = '';
      }
      if(!this.TEexamfileStatus || !this.TEtechnicalInterviewFileStatus)
      {
       
        return false;
      }


      let examfilename = '';
      if(academic_qualification =='2'){
        examfilename = this.TEexamFileNames;      
      }
      let technicalfilename = this.TEtechnicalInterviewFileNames;   

      this.loadingArr['technicalExpertBsForm'] = true;
    
      
      let businessdatas = [];
      
      businessdatas.push({role_id,business_sector_id,business_sector_group_id,academic_qualification_status:academic_qualification,examfilename:examfilename,technicalfilename:technicalfilename,id})
    

      
      let formvalue:any={};
      formvalue.te_business_group = businessdatas;
      formvalue.actiontype = 'te_business_group';
      formvalue.id = this.id;
      this.teBusinessformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.teBusinessformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            
            this.TEexamFileNames  = '';
            this.TEtechnicalInterviewFileNames = '';
            this.technicalExpertBsForm.reset();
            
            this.teBusinessformData = new FormData();
            this.success = {summary:res.message};
            this.buttonDisable = false;
            this.getUserData('te_business_group');
            setTimeout(() => {
              this.teBusinessGroupEditStatus = false;
              //this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.teBusinessformData);	
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['technicalExpertBsForm'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['technicalExpertBsForm'] = false;
      });
    }
  }
  

  onApprovedTeBusinessSubmit(){
    this.teapprovedbsf.role_id.markAsTouched();
    this.teapprovedbsf.business_sector_id.markAsTouched();
    this.teapprovedbsf.business_sector_group_id.markAsTouched();
    if(this.technicalExpertApprovedBsForm.valid)
    {

      let role_id = this.technicalExpertApprovedBsForm.get('role_id').value;
      let business_sector_id = this.technicalExpertApprovedBsForm.get('business_sector_id').value;
      let business_sector_group_id = this.technicalExpertApprovedBsForm.get('business_sector_group_id').value;
      
      //let id = this.technicalExpertBsForm.get('id').value;

       
      this.loadingArr['technicalExpertApprovedBsForm'] = true;
    
      
      let businessdatas = [];
      
      businessdatas.push({role_id,business_sector_id,business_sector_group_id})
    

      this.approvedteBusinessformData = new FormData();
      let formvalue:any={};
      formvalue.approvedte_business_group = businessdatas;
      formvalue.actiontype = 'approvedte_business_group';
      formvalue.id = this.id;
      this.approvedteBusinessformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.approvedteBusinessformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            
            this.technicalExpertApprovedBsForm.reset();
            
            this.approvedteBusinessformData = new FormData();
            this.success = {summary:res.message};
            this.buttonDisable = false;
            this.getUserData('te_business_group');
            setTimeout(() => {
              //this.teBusinessGroupEditStatus = false;
              //this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.approvedteBusinessformData);	
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['technicalExpertApprovedBsForm'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['technicalExpertApprovedBsForm'] = false;
      });
    }
  }
  
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
    
    this.cf.training_subject.markAsTouched();
    this.cf.training_date.markAsTouched();
    this.cf.training_hours.markAsTouched();
    //console.log(this.cpdForm.valid);
    if(this.cpdForm.valid){
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
    }
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
  
  declarationEditStatus=false;
  declarationStatus=true;
  declarationIndex=0;

  addDeclaration(){
	
    this.declarationErrors ='';
    let formerror = false;

    this.declarationStatus=true;
	 let id = this.declarationForm.get('id').value;
    let declaration_company = this.declarationForm.get('declaration_company').value;
    let declaration_contract = this.declarationForm.get('declaration_contract').value;
    let declaration_interest = this.declarationForm.get('declaration_interest').value;
	  let declaration_start_year = this.declarationForm.get('declaration_start_year').value;
    let declaration_end_year = this.declarationForm.get('declaration_end_year').value;
    let sel_close = this.declarationForm.get('sel_close').value;
    let spouse_work = this.declarationForm.get('spouse_work').value;
    let sel_close2 = this.declarationForm.get('sel_close2').value;
    

    
    
    
    
    if (sel_close == 1) {
      this.df.relation_name.markAsTouched();
      this.df.declaration_relation.markAsTouched();
      this.df.rel_declaration_company.markAsTouched();
      this.df.rel_declaration_contract.markAsTouched();
      this.df.rel_declaration_interest.markAsTouched();
      this.df.rel_declaration_start_year.markAsTouched();
      this.df.rel_declaration_end_year.markAsTouched();

      if (this.relationEntries.length == 0) {
        formerror = true;
      }

    } else if (sel_close == 2) {
      this.df.spouse_work.markAsTouched();

      if (spouse_work == '' || spouse_work==null) {
        formerror = true;
      }

      if (sel_close2 == 1) {
        this.df.relation_name.markAsTouched();
        this.df.declaration_relation.markAsTouched();
        this.df.rel_declaration_company.markAsTouched();
        this.df.rel_declaration_contract.markAsTouched();
        this.df.rel_declaration_interest.markAsTouched();
        this.df.rel_declaration_start_year.markAsTouched();
        this.df.rel_declaration_end_year.markAsTouched();

        let relationName = this.declarationForm.get('relation_name').value;
        let relationNameType = this.declarationForm.get('declaration_relation').value;
        if (this.relationEntries.length == 0) {
          formerror = true;
        }
      } else if (sel_close2 == 2) {
        this.relationEntries = [];
      }
    }else if(sel_close=='' || sel_close==null){
      formerror=true;
      this.closeRelError='Please select close relation consent';
    }else if(sel_close==3){
    this.df.declaration_company.markAsTouched();
    this.df.declaration_contract.markAsTouched();
    this.df.declaration_interest.markAsTouched();
  	this.df.declaration_start_year.markAsTouched();
  	this.df.declaration_end_year.markAsTouched();

    if(declaration_company=='' || declaration_contract=='' || declaration_interest=='' || declaration_start_year=='' || declaration_end_year=='')
  	{
      formerror=true;
    }
  }

    
    let relationDataEntries = [];
    this.relationEntries.forEach(val => {
      let expobject = {
        'name': val.name,
        'type_name': val.type_name,
        'rel_declaration_company':val.rel_declaration_company,
        'rel_declaration_contract':val.rel_declaration_contract,
        'rel_declaration_interest':val.rel_declaration_interest,
        'rel_declaration_start_year':val.rel_declaration_start_year,
        'rel_declaration_end_year':val.rel_declaration_end_year
      }
      relationDataEntries.push(expobject);
    })

    if(!formerror)
    {
		/*
		let declaration_contract_name = this.userData.declaration_contract[declaration_contract];
				
		let expobject:any=[];
		expobject["declaration_company"] = declaration_company;
		expobject["declaration_contract"] = declaration_contract_name;
		expobject["declaration_contract_id"] = declaration_contract;
		expobject["declaration_interest"] = declaration_interest;
		expobject["declaration_start_year"] = declaration_start_year;
		expobject["declaration_end_year"] = declaration_end_year;
          
		if(this.declarationIndex!==null){
			this.declarationEntries[this.declarationIndex] = expobject;
		}else{
			this.declarationEntries.push(expobject);
		}
		*/
		this.loadingArr['declaration'] = true;
	       
		let declarationdatas = [];
		declarationdatas.push({id:id,
      declaration_company:declaration_company,
      declaration_contract:declaration_contract,
      declaration_interest:declaration_interest,
      declaration_start_year:declaration_start_year,
      declaration_end_year:declaration_end_year,
      sel_close:sel_close,
      sel_close2:sel_close2,
      spouse_work:spouse_work,
      relationDataEntries : relationDataEntries
    });
				  
		let formvalue:any={};
		formvalue.declaration = declarationdatas;
		formvalue.actiontype = 'declaration';
		formvalue.id = this.id;
    
		this.declarationformData.append('formvalues',JSON.stringify(formvalue));
		  
		this.userService.updateUserData(this.declarationformData)
		  .pipe(first())
		  .subscribe(res => {

			  if(res.status){

					this.success = {summary:res.message};
					this.buttonDisable = false;
					this.editrelation=false;
					this.getUserData('declaration');
					this.declarationformData=new FormData();
          this.relationEntries=[];
          this.declarationForm.reset();
					setTimeout(() => {
						this.loadingArr['declaration'] = false;
						this.declarationEditStatus=false;
					 
					}, this.errorSummary.redirectTime);
					
			  }else if(res.status == 0){
				this.error = this.errorSummary.getErrorSummary(res.message,this,this.declarationformData);	
			  }else{
				this.error = {summary:res};
			  }
			  this.loadingArr['declaration'] = false;
		},
		error => {
			this.error = error;
			this.loadingArr['declaration'] = false;
		});
		  
		
		this.declarationIndex=this.declarationEntries.length;
    }
  }
  editRelation(index){
    this.editrelation=true;
    let qual = this.relationEntries[index];
    this.declarationForm.patchValue({
      rel_index: index,
      rel_declaration_company: qual.rel_declaration_company,
      rel_declaration_contract: qual.rel_declaration_contract,
      rel_declaration_interest: qual.rel_declaration_interest,
      rel_declaration_start_year: qual.rel_declaration_start_year,
      rel_declaration_end_year: qual.rel_declaration_end_year,
      relation_name:qual.name,
      declaration_relation:qual.type_name_id,
      
    });
  }

  updateRelation(){
    let relFormError=false;
    let rel_index = this.declarationForm.get('rel_index').value;
    let rel_declaration_company = this.declarationForm.get('rel_declaration_company').value;
    let rel_declaration_contract = this.declarationForm.get('rel_declaration_contract').value;
    let rel_declaration_interest = this.declarationForm.get('rel_declaration_interest').value;
    let rel_declaration_start_year = this.declarationForm.get('rel_declaration_start_year').value;
    let rel_declaration_end_year = this.declarationForm.get('rel_declaration_end_year').value;
    let relation_name = this.declarationForm.get('relation_name').value;
    let declaration_relation = this.declarationForm.get('declaration_relation').value;
    let declaration_relation_name= this.userData.relationList[declaration_relation];
    let rel_declaration_contract_name = this.userData.declaration_contract[rel_declaration_contract];

    this.df.relation_name.setValidators([Validators.required]);
    this.df.declaration_relation.setValidators([Validators.required]);
    this.df.rel_declaration_company.setValidators([Validators.required]), 
    this.df.rel_declaration_contract.setValidators([Validators.required]),
    this.df.rel_declaration_interest.setValidators([Validators.required]),
    this.df.rel_declaration_start_year.setValidators([Validators.required,Validators.pattern("^[0-9\-]*$")]),
    this.df.rel_declaration_end_year.setValidators([Validators.required,Validators.pattern("^[0-9\-]*$")]),
    this.df.relation_name.updateValueAndValidity();
    this.df.declaration_relation.updateValueAndValidity();
    this.df.rel_declaration_company.updateValueAndValidity();
    this.df.rel_declaration_contract.updateValueAndValidity();
    this.df.rel_declaration_interest.updateValueAndValidity();
    this.df.rel_declaration_start_year.updateValueAndValidity();
    this.df.rel_declaration_end_year.updateValueAndValidity();

    this.df.relation_name.markAsTouched();
    this.df.declaration_relation.markAsTouched();
    this.df.rel_declaration_company.markAsTouched();
    this.df.rel_declaration_contract.markAsTouched();
    this.df.rel_declaration_interest.markAsTouched();
    this.df.rel_declaration_start_year.markAsTouched();
    this.df.rel_declaration_end_year.markAsTouched();

    if(this.df.relation_name.errors || this.df.declaration_relation.errors || this.df.rel_declaration_company.errors || this.df.rel_declaration_contract.errors || this.df.rel_declaration_interest.errors || this.df.rel_declaration_start_year.errors || this.df.rel_declaration_end_year.errors){
      return false;
    }

   
    if( (rel_index!=0 && rel_index=="") ||declaration_relation==''||rel_declaration_company==''  || rel_declaration_start_year=='' || rel_declaration_end_year=='' || relation_name=='' || declaration_relation=='' || rel_declaration_interest==''){
      relFormError=true;
    }

    if(!relFormError){
      let expobject:any=[];
       this.relationEntries[rel_index].name = relation_name;
       this.relationEntries[rel_index].type_name = declaration_relation_name;
       this.relationEntries[rel_index].type_name_id = declaration_relation;
       this.relationEntries[rel_index].rel_declaration_company = rel_declaration_company;
       this.relationEntries[rel_index].rel_declaration_contract = rel_declaration_contract;
       this.relationEntries[rel_index].rel_declaration_contract_name = rel_declaration_contract_name;
       this.relationEntries[rel_index].rel_declaration_interest = rel_declaration_interest;
       this.relationEntries[rel_index].rel_declaration_start_year = rel_declaration_start_year;
       this.relationEntries[rel_index].rel_declaration_end_year = rel_declaration_end_year;

       this.df.relation_name.setValidators(null);
       this.df.declaration_relation.setValidators(null);
       this.df.rel_declaration_company.setValidators(null);
       this.df.rel_declaration_contract.setValidators(null);
       this.df.rel_declaration_interest.setValidators(null);
       this.df.rel_declaration_start_year.setValidators(null);
       this.df.rel_declaration_end_year.setValidators(null);
       this.df.relation_name.updateValueAndValidity();
       this.df.declaration_relation.updateValueAndValidity();
       this.df.rel_declaration_company.updateValueAndValidity();
       this.df.rel_declaration_contract.updateValueAndValidity();
       this.df.rel_declaration_interest.updateValueAndValidity();
       this.df.rel_declaration_start_year.updateValueAndValidity();
       this.df.rel_declaration_end_year.updateValueAndValidity();
       
       this.editrelation=false;
       this.declarationForm.patchValue({
         relation_name: '',
         declaration_relation:'',
         rel_declaration_company:'',
         rel_declaration_contract:'',
         rel_declaration_interest:'',
         rel_declaration_start_year:'',
         rel_declaration_end_year:''
       });
    }



  }
  editDeclaration(index:number){
    this.relationEntries=[];
	this.declarationEditStatus=true;
    this.declarationIndex= index;
	let qual = this.declarationEntries[index];
	
	this.declarationEndYearChange(qual.declaration_start_year);
	
  if(qual.relation_consent==3){
    this.declarationForm.patchValue({
      id: qual.id,
        declaration_company: qual.declaration_company,
        declaration_contract: qual.declaration_contract_id,
        declaration_interest: qual.declaration_interest,
        declaration_start_year: qual.declaration_start_year,
        declaration_end_year: qual.declaration_end_year,
        sel_close:qual.relation_consent,
        
      });
  }else if(qual.relation_consent==1){
    this.declarationForm.patchValue({
        id: qual.id,
        sel_close:qual.relation_consent,
      });
      let expobject:any=[];
      expobject["name"] = qual.name;
      expobject["type_name"] = qual.type_name;
      expobject["type_name_id"] = qual.type_name_id;
      expobject["rel_declaration_company"] = qual.declaration_company;
      expobject["rel_declaration_contract"] = qual.declaration_contract_id;
      expobject["rel_declaration_contract_name"] = qual.declaration_contract;
      expobject["rel_declaration_interest"] = qual.declaration_interest;
      expobject["rel_declaration_start_year"] = qual.declaration_start_year;
      expobject["rel_declaration_end_year"] = qual.declaration_end_year;

      this.relationEntries.push(expobject);
  }else if(qual.relation_consent==2){
    this.declarationForm.patchValue({
      id: qual.id,
      spouse_work:qual.relation_work,
      sel_close:qual.relation_consent,
      sel_close2:qual.re_relation_consent?qual.re_relation_consent:2,
    });
    
      let expobject:any=[];
      expobject["name"] = qual.name;
      expobject["type_name"] = qual.type_name;
      expobject["type_name_id"] = qual.type_name_id;
      expobject["rel_declaration_company"] = qual.declaration_company;
      expobject["rel_declaration_contract"] = qual.declaration_contract_id;
      expobject["rel_declaration_contract_name"] = qual.declaration_contract;
      expobject["rel_declaration_interest"] = qual.declaration_interest;
      expobject["rel_declaration_start_year"] = qual.declaration_start_year;
      expobject["rel_declaration_end_year"] = qual.declaration_end_year;

      this.relationEntries.push(expobject);
   
  }
   
   
    this.scrollToBottom();
  }
  //Declaration Details Code End Here


  get filterRejectedDeclaration(){
    return this.declaration_rejectedEntries.filter(x=>x.deleted!=1);
  }
  //Reject Declaration Details Code Start Here
  removeRejectDeclaration(index:any) {
  //console.log(index);
    if(index>=0){
      this.declaration_rejectedEntries[index].deleted = 1;
     // console.log(this.declaration_rejectedEntries);
      //this.declarationEntries(index,1);
    }
    //this.declarationIndex=this.declarationEntries.length;
  }
  
  //declarationStatus=true;
  declarationRejectIndex='';
  declarationApprovedIndex='';

  addRejectDeclaration(){
  
    this.declarationErrors ='';
    let formerror = false;

    this.declarationStatus=true;
    let declaration_company = this.declarationRejectForm.get('declaration_company').value;
    let declaration_contract = this.declarationRejectForm.get('declaration_contract').value;
    let declaration_interest = this.declarationRejectForm.get('declaration_interest').value;
    let declaration_start_year = this.declarationRejectForm.get('declaration_start_year').value;
    let declaration_end_year = this.declarationRejectForm.get('declaration_end_year').value;
    let sel_close = this.declarationRejectForm.get('sel_close').value;
    let sel_close2 =this.declarationRejectForm.get('sel_close2').value;
    let name = this.declarationRejectForm.get('relation_name').value;
    let name_type = this.declarationRejectForm.get('declaration_relation').value;
    let spouse_work = this.declarationRejectForm.get('spouse_work').value;

    this.drf.declaration_company.markAsTouched();
    this.drf.declaration_contract.markAsTouched();
    this.drf.declaration_interest.markAsTouched();
    this.drf.declaration_start_year.markAsTouched();
    this.drf.declaration_end_year.markAsTouched();

    if (sel_close == 1) {
      this.drf.relation_name.markAsTouched();
      this.drf.declaration_relation.markAsTouched();
      
     

      if (name=='' || name_type=='') {
        formerror = true;
      }
      name_type=this.userData.relationList[name_type];
    } else if (sel_close == 2) {
      this.drf.spouse_work.markAsTouched();
     
      if (spouse_work == '' || spouse_work==null) {
        formerror = true;
      }else if(sel_close2==1){

        if (name=='' || name_type=='') {
          formerror = true;
        }
        name_type=this.userData.relationList[name_type];
      }
    }else if(sel_close==3){
      name="Self";
      name_type="NA";
    }

    if(declaration_company=='' || declaration_contract=='' || declaration_interest=='' || declaration_start_year=='' || declaration_end_year=='' )
  	{
      formerror=true;
    }
    
    
    if(!formerror)
    {
      let declaration_contract_name = this.userData.declaration_contract[declaration_contract];
      
      let index = this.declarationRejectIndex;
      let expobject:any = this.declaration_rejectedEntries[index];

      //let expobject:any=[];
      expobject["declaration_company"] = declaration_company;
      expobject["declaration_contract"] = declaration_contract_name;
      expobject["declaration_contract_id"] = declaration_contract;
      expobject["declaration_interest"] = declaration_interest;
      expobject["declaration_start_year"] = declaration_start_year;
      expobject["declaration_end_year"] = declaration_end_year;
      expobject["sel_close"]=sel_close;
      expobject["sel_close2"]=sel_close2;
      expobject["spouse_work"]=spouse_work;
      expobject["name"]=name;
      expobject["type_name"]=name_type;

      this.loadingArr['declaration'] = true;
    
      
      let declarationdatas = [];
      
      declarationdatas.push(expobject);
      

      
      let formvalue:any={};
      formvalue.declaration = declarationdatas;
      formvalue.actiontype = 'declarationreject';
      formvalue.id = this.id;
      this.declarationrejectionformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.declarationrejectionformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.declarationRejectForm.reset();
            this.declarationRejectForm.patchValue({
              declaration_start_year: '',
              declaration_end_year: ''
            });
            this.declarationRejectIndex='';
            this.showdecForm = false;
            this.editrelation=false;
            this.getUserData('declaration');

            this.declarationrejectionformData = new FormData();
            this.rejrelationEntries=[];
            //this.declaration_approvalwaitingEntries = res['declaration_approvalwaiting'];
           
            //this.declaration_rejectedEntries.splice(index,1);

            this.success = {summary:res.message};
            this.buttonDisable = false;
            setTimeout(() => {
               this.loadingArr['declaration'] = false;
              //this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.declarationrejectionformData);  
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['declaration'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['declaration'] = false;
      });





         
      /*if(this.declarationRejectIndex!==null){
        this.declaration_rejectedEntries[this.declarationRejectIndex] = expobject;
      }*/

      
    }
  }
  addApprovedDeclaration(){
  
    this.declarationErrors ='';
    let formerror = false;

    this.declarationStatus=true;
    let declaration_company = this.declarationApprovedForm.get('declaration_company').value;
    let declaration_contract = this.declarationApprovedForm.get('declaration_contract').value;
    let declaration_interest = this.declarationApprovedForm.get('declaration_interest').value;
    let declaration_start_year = this.declarationApprovedForm.get('declaration_start_year').value;
    let declaration_end_year = this.declarationApprovedForm.get('declaration_end_year').value;
    let sel_close = this.declarationApprovedForm.get('sel_close').value;
    let sel_close2 =this.declarationApprovedForm.get('sel_close2').value;
    let name = this.declarationApprovedForm.get('relation_name').value;
    let name_type = this.declarationApprovedForm.get('declaration_relation').value;
    let spouse_work = this.declarationApprovedForm.get('spouse_work').value;

    this.daf.declaration_company.markAsTouched();
    this.daf.declaration_contract.markAsTouched();
    this.daf.declaration_interest.markAsTouched();
    this.daf.declaration_start_year.markAsTouched();
    this.daf.declaration_end_year.markAsTouched();

    if (sel_close == 1) {
      this.daf.relation_name.markAsTouched();
      this.daf.declaration_relation.markAsTouched();
      
     

      if (name=='' || name_type=='') {
        formerror = true;
      }
      name_type=this.userData.relationList[name_type];
    } else if (sel_close == 2) {
      this.daf.spouse_work.markAsTouched();
     
      if (spouse_work == '' || spouse_work==null) {
        formerror = true;
      }else if(sel_close2==1){

        if (name=='' || name_type=='') {
          formerror = true;
        }
        name_type=this.userData.relationList[name_type];
      }
    }else if(sel_close==3){
      name="Self";
      name_type="NA";
    }

    if(declaration_company=='' || declaration_contract=='' || declaration_interest=='' || declaration_start_year=='' || declaration_end_year=='' )
  	{
      formerror=true;
    }
    
    
    if(!formerror)
    {
      let declaration_contract_name = this.userData.declaration_contract[declaration_contract];
      
      let index = this.declarationApprovedIndex;
      let expobject:any = this.declaration_approvedEntries[index];

      //let expobject:any=[];
      expobject["declaration_company"] = declaration_company;
      expobject["declaration_contract"] = declaration_contract_name;
      expobject["declaration_contract_id"] = declaration_contract;
      expobject["declaration_interest"] = declaration_interest;
      expobject["declaration_start_year"] = declaration_start_year;
      expobject["declaration_end_year"] = declaration_end_year;
      expobject["sel_close"]=sel_close;
      expobject["sel_close2"]=sel_close2;
      expobject["spouse_work"]=spouse_work;
      expobject["name"]=name;
      expobject["type_name"]=name_type;

      this.loadingArr['declaration'] = true;
    
      
      let declarationdatas = [];
      
      declarationdatas.push(expobject);
      

      
      let formvalue:any={};
      formvalue.declaration = declarationdatas;
      formvalue.actiontype = 'declarationapproved';
      formvalue.id = this.id;
      this.declarationapprovedformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.declarationapprovedformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.declarationApprovedForm.reset();
            this.declarationApprovedForm.patchValue({
              declaration_start_year: '',
              declaration_end_year: ''
            });
            this.declarationApprovedIndex='';
            this.showappdecForm = false;
            this.editrelation=false;
            this.getUserData('declaration');

            this.declarationapprovedformData = new FormData();
          
            //this.declaration_approvalwaitingEntries = res['declaration_approvalwaiting'];
           
            //this.declaration_rejectedEntries.splice(index,1);

            this.success = {summary:res.message};
            this.buttonDisable = false;
            setTimeout(() => {
               this.loadingArr['declaration'] = false;
              //this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.declarationapprovedformData);  
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['declaration'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['declaration'] = false;
      });





         
      /*if(this.declarationRejectIndex!==null){
        this.declaration_rejectedEntries[this.declarationRejectIndex] = expobject;
      }*/

      
    }
  }


  showdecForm = false;
  showrelation =true;
  showspwork=true;
 async editRejectDeclaration(index:any){
    this.rejrelationEntries=[];
    this.showdecForm = true;
    this.showrelation =true;
    this.showspwork=true;
    this.declarationRejectIndex= index;
    // console.log(this.declarationRejectIndex);
    let qual = await this.declaration_rejectedEntries[index];
    
    this.declarationEndYearChange(qual.declaration_start_year);
  
    this.declarationRejectForm.patchValue({
      declaration_company: qual.declaration_company,
      declaration_contract: qual.declaration_contract_id,
      declaration_interest: qual.declaration_interest,
      declaration_start_year: qual.declaration_start_year,
      declaration_end_year: qual.declaration_end_year, 
      sel_close:qual.relation_consent,
      sel_close2:qual.re_relation_consent
    });
    if(qual.relation_consent==1){
      this.declarationRejectForm.patchValue({
        relation_name:qual.name,
        declaration_relation:qual.type_name_id,
        spouse_work:''
      })
     
      this.showspwork=false;
    }else if(qual.relation_consent==2){
      this.declarationRejectForm.patchValue({
      spouse_work:qual.relation_work
      })
      if(qual.re_relation_consent==1){
        this.declarationRejectForm.patchValue({
          relation_name:qual.name,
          declaration_relation:qual.type_name_id,
        })
      }
      
     
    }else if(qual.relation_consent==3){
      this.declarationRejectForm.patchValue({
        relation_name:'',
        declaration_relation:'',
        spouse_work:'',
      })
      this.showrelation=false;
      
    } 
    this.scrollToBottom();
  }
  //Reject Declaration Details Code End Here

  showappdecForm = false;
  showapprelation =true;
  showappspwork=true;
  async editApprovedDeclaration(index:any){
   
    this.showappdecForm = true;
    this.showapprelation =true;
    this.showappspwork=true;
    this.declarationApprovedIndex= index;
    // console.log(this.declarationRejectIndex);
    let qual = await this.declaration_approvedEntries[index];
    
    this.declarationEndYearChange(qual.declaration_start_year);
  
    this.declarationApprovedForm.patchValue({
      declaration_company: qual.declaration_company,
      declaration_contract: qual.declaration_contract_id,
      declaration_interest: qual.declaration_interest,
      declaration_start_year: qual.declaration_start_year,
      declaration_end_year: qual.declaration_end_year, 
      sel_close:qual.relation_consent,
      sel_close2:qual.re_relation_consent
    });
    if(qual.relation_consent==1){
      this.declarationApprovedForm.patchValue({
        relation_name:qual.name,
        declaration_relation:qual.type_name_id,
        spouse_work:''
      })
     
      this.showappspwork=false;
    }else if(qual.relation_consent==2){
      this.declarationApprovedForm.patchValue({
      spouse_work:qual.relation_work
      })
      if(qual.re_relation_consent==1){
        this.declarationApprovedForm.patchValue({
          relation_name:qual.name,
          declaration_relation:qual.type_name_id,
        })
      }
      
     
    }else if(qual.relation_consent==3){
      this.declarationApprovedForm.patchValue({
        relation_name:'',
        declaration_relation:'',
        spouse_work:'',
      })
      this.showapprelation=false;
      
    } 
    this.scrollToBottom();
  }

  documentFileErr = '';
  documents:any='';
  documentChange(element) 
  {
    let files = element.target.files;
  
    for (let i = 0; i < files.length; i++) 
    {
    
      let fileextension = files[i].name.split('.').pop();
      if(this.errorSummary.checkValidDocs(fileextension))
      {
        this.documents = files[i].name; 
      }else{
        this.documentFileErr='Please upload valid files';
        element.target.value = '';
        return false;
      }
    }
    this.mapUserformData.append("documents", files[0], files[0].name);
    
    element.target.value = '';
    this.documentFileErr = '';
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

 



  hideError(){
    this.role_idErrors = '';
    
    this.userloginForm.controls.role_id.setErrors(null);
    this.userloginForm.controls.role_id.markAsUntouched();
    
    //this.userloginForm.controls.franchise_id.setErrors(null);
    //this.userloginForm.controls.franchise_id.markAsUntouched();
   //.markAsTouched();
  }
  onChange(id: number, isChecked: boolean) {
    //const emailFormArray = <FormArray>this.myForm.controls.useremail;
    //const standardsFormArray = <FormArray>this.customerForm.get('company.standardsChk');
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
	  
      
      let formvalue = this.customerForm.value;
      formvalue.actiontype = 'personnel';
      this.formData.append('formvalues',JSON.stringify(formvalue));
      

      this.userService.updateUserData(this.formData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
             this.passport_file = res['passport_file'];
              this.contract_file = res['contract_file'];
			      this.success = {summary:res.message};
				    this.buttonDisable = false;
			      setTimeout(() => {
              //this.router.navigateByUrl('/master/user/list');
              //this.router.navigateByUrl('/master/user/view?id='+res.user_id)
              //this.router.navigateByUrl('/master/user/a?id='+res.user_id)
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



  setStdDisplay(stdid){
    let selStd = this.standardList.find(x=>x.id ==stdid);
    if(selStd.code == 'GRS' || selStd.code == 'RCS'){
      this.showRecycle = true;
    }else{
      this.showRecycle = false;
    }

    if(selStd.code == 'GRS' || selStd.code == 'GOTS'){
      this.showSocial = true;
    }else{
      this.showSocial = false;
    }
  }
  stdformData:FormData = new FormData();
  
  onStandardSubmit()
  {
    this.std_examfileErrors = '';
    // this.qualification_examfileErrors='';
    this.recycle_examfileErrors = '';
    this.social_exam_fileErrors = '';
    this.qua_examfileErrors ='';
    this.witness_fileErrors = '';
    this.sf.recycle_exam_date.setValidators([]);
    this.sf.recycle_exam_date.updateValueAndValidity();

    this.sf.social_exam_date.setValidators([]);
    this.sf.social_exam_date.updateValueAndValidity();
    let stdid = this.standardForm.get('standard').value;

    let witness_date = this.standardForm.get('witness_date').value;
    
    if(stdid){
      let stdappIndex = this.standard_approvedEntries.find(x=>x.standard ==stdid);
      if(stdappIndex!== undefined && stdappIndex !== -1){
        this.error = {summary:'Standard was approved already.'};
        return false;
      }
      
      stdappIndex = this.standard_approvalwaitingEntries.find(x=>x.standard ==stdid);
      if(stdappIndex!== undefined && stdappIndex !== -1){
        this.error = {summary:'Standard was already waiting for approval.'};
        return false;
      }

      stdappIndex = this.standard_rejectedEntries.find(x=>x.standard ==stdid);
      if(stdappIndex!== undefined && stdappIndex !== -1){
        this.error = {summary:'Standard was rejected.'};
        return false;
      }
      // if(this.qualification_exam_file ==''){
      //   this.qualification_examfileErrors = 'Please upload qualification File';
      // }

    
      if(this.std_exam_file ==''){
        this.std_examfileErrors = 'Please upload Standard Exam File';
      }
      
      let selStd = this.standardList.find(x=>x.id ==stdid);
      if(selStd.code == 'GRS' || selStd.code == 'RCS'){
        if(this.recycle_exam_file ==''){
          this.recycle_examfileErrors = 'Please upload Recycle Exam File';
        }

        this.sf.recycle_exam_date.setValidators([Validators.required]);
        this.sf.recycle_exam_date.updateValueAndValidity();
        this.sf.recycle_exam_date.markAsTouched();
        
      }
      

      if(selStd.code == 'GRS' || selStd.code == 'GOTS'){
        if(this.social_exam_file ==''){
          this.social_exam_fileErrors = 'Please upload Social Exam File';
        }
        this.sf.social_exam_date.setValidators([Validators.required]);
        this.sf.social_exam_date.updateValueAndValidity();
        this.sf.social_exam_date.markAsTouched();

      }
    }
  

    if(witness_date !='' && witness_date!==null){
      if(this.witness_file ==''){
        this.witness_fileErrors = 'Please upload Witness File';
      }
    }

    if(this.qua_exam_file==''){
      this.qua_examfileErrors ='Please upload Pre Qualification file';
    }

    this.sf.standard.markAsTouched();
    this.sf.std_exam_date.markAsTouched();
    this.sf.pre_qualification.markAsTouched();
    
    /*if (this.sf.standard.value.length <= 0) {
      this.errorSummary.validateAllFormFields(this.standardForm);
    }else{
    */
    let std_exam_date = this.standardForm.get('std_exam_date').value;
    let social_exam_date = this.standardForm.get('social_exam_date').value;
    let recycle_exam_date = this.standardForm.get('recycle_exam_date').value;
    let pre_qualification = this.standardForm.get('pre_qualification').value;
    
    //let witness_valid_until = this.standardForm.get('witness_valid_until').value;
    
    if (this.standardForm.valid && this.witness_fileErrors =='' && this.std_examfileErrors =='' && this.recycle_examfileErrors =='' && this.social_exam_fileErrors =='' && this.qua_examfileErrors =='') {
      this.loadingArr['standardForm'] = true;
      
      

      
      let formvalue=  this.standardForm.value;
      formvalue.actiontype = 'standards';
      formvalue.id = this.id;
      formvalue.std_exam_date = this.errorSummary.displayDateFormat(std_exam_date);
      if(social_exam_date !='' && social_exam_date!==null){
        formvalue.social_exam_date = this.errorSummary.displayDateFormat(social_exam_date);
      }
      if(recycle_exam_date !='' && recycle_exam_date!==null){
        formvalue.recycle_exam_date = this.errorSummary.displayDateFormat(recycle_exam_date);
      }

      if(witness_date !='' && witness_date!==null){
        formvalue.witness_date = this.errorSummary.displayDateFormat(witness_date);
      }
      /*if(witness_valid_until !=''){
        formvalue.witness_valid_until = this.errorSummary.displayDateFormat(witness_valid_until);
      }*/

      
      this.stdformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.stdformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            //this.standardFormDetails.standard = formvalue.standard;
            //this.standardFormDetails.standard = formvalue.standard;
            this.stdformData = new FormData();


            
            this.standardFormDetails = res['resultarr']['standard'][0];
        //  this.qualification_exam_file=this.standardFormDetails.qualification_exam_file;

            this.std_exam_file = this.standardFormDetails.standard_exam_file;

            if(this.standardFormDetails.standard_code == 'GRS' || this.standardFormDetails.standard_code == 'RCS'){
              this.recycle_exam_file = this.standardFormDetails.recycle_exam_file;
              this.showRecycle = true;
            }

            if(this.standardFormDetails.standard_code == 'GRS' || this.standardFormDetails.standard_code == 'GOTS'){
              this.social_exam_file = this.standardFormDetails.social_course_exam_file;
              this.showSocial = true;
            }

            if(this.standardFormDetails.witness_date != ''){
              this.witness_file = this.standardFormDetails.witness_file;
            }




			      this.success = {summary:res.message};
				    this.buttonDisable = false;
			      setTimeout(() => {
              this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.stdformData);	
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['standardForm'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['standardForm'] = false;
      });
    }
  }

  SaveStdFileChanges()
  {
    this.asf.approved_witness_date.markAsTouched();
    this.asf.approved_approval_date.markAsTouched();
  //  this.approved_qualification_examfileErrors='';
    this.approved_std_examfileErrors = '';
    this.approved_recycle_examFileErrors = '';
    this.approved_social_examFileErrors = '';
    this.approved_witness_fileErrors = '';
    this.approved_qua_exam_fileErrors='';

    

    if(this.approved_std_exam_file ==''){
      this.approved_std_examfileErrors = 'Please upload Standard Exam File';
    }
    
    // if(this.approved_qualification_exam_file ==''){
    //   this.approved_qualification_examfileErrors = 'Please upload qualification Exam File';
    // }
    if(this.approved_qua_exam_file ==''){
      this.approved_qua_exam_fileErrors = 'Please upload Pre Qualification File';
    }
    
    if(this.approved_witness_file ==''){
      this.approved_witness_fileErrors = 'Please upload Witness File';
    }
    
    let selStd = this.stdfileData.standard_code;
    if(selStd == 'GRS' || selStd == 'RCS'){
      if(this.approved_recycle_exam_file ==''){
        this.approved_recycle_examFileErrors = 'Please upload Recycle Exam File';
      } 
    }
    

    if(selStd == 'GRS' || selStd == 'GOTS'){
      if(this.approved_social_exam_file ==''){
        this.approved_social_examFileErrors = 'Please upload Social Exam File';
      }
    }

    if (this.stdfileform.valid && this.approved_qua_exam_fileErrors=='' && this.approved_witness_fileErrors =='' && this.approved_std_examfileErrors =='' && this.approved_recycle_examFileErrors =='' && this.approved_social_examFileErrors =='') 
    {
      this.loading = true;

      let formvalue:any={};
      formvalue.id =  this.stdfileData.id;
      let approved_witness_date = this.stdfileform.get('approved_witness_date').value;
      let approved_approval_date = this.stdfileform.get('approved_approval_date').value;
      let approved_valid_until = this.stdfileform.get('approved_valid_until').value;
      let approved_pre_qualification = this.stdfileform.get('approved_pre_qualification').value;

      formvalue.witness_date = this.errorSummary.displayDateFormat(approved_witness_date);
      formvalue.approval_date = this.errorSummary.displayDateFormat(approved_approval_date);
      formvalue.valid_until = this.errorSummary.displayDateFormat(approved_valid_until);
      formvalue.pre_qualification = approved_pre_qualification;
      this.stdfileformData.append('formvalues',JSON.stringify(formvalue));

      this.userService.updateStdFile(this.stdfileformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.stdfileData = new FormData();
            this.success = {summary:res.message};
            this.modalss.close();
            this.getUserData('standard');
            this.loading = false;
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.stdfileformData);	
          }else{
            this.error = {summary:res};
          }
          this.loading = false;
      },
      error => {
          this.error = error;
          this.loading = false;
      });
    }
  }

  SaveBgroupDateChanges()
  {
    this.bsf.approved_approval_date.markAsTouched();
    if(this.bgroupdateform.valid)
    {
      this.loading = true;

      let formvalue:any={};
      formvalue.id =  this.bsectorgroupcodeid;

      let approved_approval_date = this.bgroupdateform.get('approved_approval_date').value;
      formvalue.approval_date = this.errorSummary.displayDateFormat(approved_approval_date);

      this.userService.updateBgroupApprovalDate(formvalue)
      .pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.success = {summary:res.message};
          this.modalss.close();
          this.getUserData('business_group_code');
          this.loading = false;
        }
        else if(res.status == 0){
          this.error = this.errorSummary.getErrorSummary(res.message,this,this.bgroupfileformData);	
        }else{
          this.error = {summary:res};
        }
        this.loading = false;
      });
    }
  }

  SaveBgroupFileChanges()
  {
    this.approved_examfileErrors = '';
    this.approved_technicalFileErrors = '';

    let qualification = this.bgroupfileData.academic_qualification;
    if(this.approved_technicalfilename =='')
    {
      this.approved_technicalFileErrors = 'Please upload Technical Interview File';
    }

    if(qualification=="2")
    {
      if(this.approved_examfilename =='')
      {
        this.approved_examfileErrors = 'Please upload Exam File';
      }
    }

    if(this.approved_technicalFileErrors =='' && this.approved_examfileErrors =='')
    {
      this.loading = true;

      let formvalue:any={};
      formvalue.id =  this.bgroupfileData.id;
      
      this.bgroupfileformData.append('formvalues',JSON.stringify(formvalue));


      this.userService.updateBgroupFile(this.bgroupfileformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.bgroupfileData = new FormData();
            this.success = {summary:res.message};
            this.modalss.close();
            this.getUserData('business_group');
            this.loading = false;
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.bgroupfileformData);	
          }else{
            this.error = {summary:res};
          }
          this.loading = false;
      },
      error => {
          this.error = error;
          this.loading = false;
      });

    }

  }
  

  qformData:FormData = new FormData();
  onQualificationSubmit(){
    this.academicFileErr = '';

    if(this.academic_file ==''){
      this.academicFileErr = 'Please upload Certificate';
    }

    if (this.userData.qualifications.length<=0 && this.qualificationEntries.length<=0 && this.academicFileErr!='') {
      //this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.qualificationForm);       
    }else{
      this.loadingArr['qualification'] = true;
	  
      let qualificationdatas = [];

      this.qualificationEntries.forEach((val)=>{
        
        qualificationdatas.push({academic_certificate:val.academic_certificate,qualification:val.qualification,board_university:val.university,subject:val.subject,start_year:val.start_year,end_year:val.end_year})
      });
      
      let formvalue:any={};
      formvalue.qualifications = qualificationdatas;
      formvalue.actiontype = 'qualification';
      formvalue.id = this.id;
      this.qformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.qformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){

            this.qualificationEntries = res['resultarr']['qualifications'];
            this.qualificationEntries.forEach((x,index)=>{
       
              if(x.academic_certificate){
                this.uploadedacademicFileNames[index]= {name:x.academic_certificate,added:0,deleted:0,valIndex:index};
              }else{
                this.uploadedacademicFileNames[index]= '';
              }
            });
			      this.success = {summary:res.message};
				    this.buttonDisable = false;
			      setTimeout(() => {
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
          this.loadingArr['qualification'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['qualification'] = false;
      });
      
    }
  }

  expformData:FormData = new FormData();
  onExperienceSubmit(){
    if (this.userData.experience.length<=0 && this.experienceEntries.length<=0) {
      //this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.experienceForm);       
    }else{
      this.loadingArr['experience'] = true;
	  
      
      let experiencedatas = [];
      this.experienceEntries.forEach((val)=>{
        experiencedatas.push({job_title:val.job_title,experience:val.experience,responsibility:val.responsibility,from_date:val.exp_from_date,to_date:val.exp_to_date})
      });

      
      let formvalue:any={};
      formvalue.experience = experiencedatas;
      formvalue.actiontype = 'experience';
      formvalue.id = this.id;
      this.expformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.expformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){

			      this.success = {summary:res.message};
				    this.buttonDisable = false;
			      setTimeout(() => {
              this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
            
            //this.submittedSuccess =1;
          }else if(res.status == 0){
            //this.submittedError =1;
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.expformData);	
          }else{
            //this.submittedError =1;
            this.error = {summary:res};
          }
          this.loadingArr['experience'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['experience'] = false;
      });
     
    }
  }

  auditexpformData:FormData = new FormData();
  onAudExperienceSubmit(){
    if (this.userData.audit_experience.length<=0 && this.auditexperienceEntries.length<=0) {
      //this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.auditExpForm);       
    }else{
      this.loadingArr['audexperience'] = true;
	  
      
      let audexperiencedatas = [];
      this.auditexperienceEntries.forEach((val)=>{
        //,process:val.process
        audexperiencedatas.push({standard:val.standard,year:val.year,company:val.company,cb:val.cb,audit_role:val.auditrolelist,days:val.days})
      });

      
      let formvalue:any={};
      formvalue.audit_experience = audexperiencedatas;
      formvalue.actiontype = 'audit_experience';
      formvalue.id = this.id;
      this.auditexpformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.auditexpformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){

			      this.success = {summary:res.message};
				    this.buttonDisable = false;
			      setTimeout(() => {
              this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
            
            //this.submittedSuccess =1;
          }else if(res.status == 0){
            //this.submittedError =1;
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.expformData);	
          }else{
            //this.submittedError =1;
            this.error = {summary:res};
          }
          this.loadingArr['audexperience'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['audexperience'] = false;
      });
     
    }
  }

  consultancyexpformData:FormData = new FormData();
  onConExperienceSubmit(){
    if (this.userData.consultancy_experience.length<=0 && this.consultancyexperienceEntries.length<=0) {
      //this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.conExpForm);       
    }else{
      this.loadingArr['conexperience'] = true;
	  
      
      let conexperiencedatas = [];
      this.consultancyexperienceEntries.forEach((val)=>{
        //,process:val.process
        conexperiencedatas.push({standard:val.standard,year:val.year,company:val.company,days:val.days})
      });

      
      let formvalue:any={};
      formvalue.consultancy_experience = conexperiencedatas;
      formvalue.actiontype = 'consultancy_experience';
      formvalue.id = this.id;
      this.consultancyexpformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.consultancyexpformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){

			      this.success = {summary:res.message};
				    this.buttonDisable = false;
			      setTimeout(() => {
              this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
            
            //this.submittedSuccess =1;
          }else if(res.status == 0){
            //this.submittedError =1;
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.expformData);	
          }else{
            //this.submittedError =1;
            this.error = {summary:res};
          }
          this.loadingArr['conexperience'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['conexperience'] = false;
      });
      
    }
  }
  
  cpdformData:FormData = new FormData();
  onCpdSubmit(){
    if (this.userData.training_info.length<=0 && this.trainingEntries.length<=0) {
      this.errorSummary.validateAllFormFields(this.cpdForm);       
    }else{
      this.loadingArr['cpd'] = true;
	  
      
      let trainingdatas = [];
      this.trainingEntries.forEach((val)=>{
        trainingdatas.push({subject:val.training_subject,training_hours:val.training_hours,training_date:val.training_date})
      }); 

      
      let formvalue:any={};
      formvalue.training_info = trainingdatas;
      formvalue.actiontype = 'cpd';
      formvalue.id = this.id;
      this.cpdformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.cpdformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){

			      this.success = {summary:res.message};
				    this.buttonDisable = false;
			      setTimeout(() => {
              this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.cpdformData);	
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['cpd'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['cpd'] = false;
      });
    }
  }

  certformData:FormData = new FormData();
  onCertificateSubmit(){
    if (this.userData.certifications.length<=0 && this.certificateEntries.length<=0) {
      this.errorSummary.validateAllFormFields(this.certificateForm); 

    }else{
        
        this.loadingArr['certificateForm'] = true;
        
        
        let certificationdatas = [];
        this.certificateEntries.forEach((val)=>{
          certificationdatas.push({certificate_name:val.certificate_name,training_hours:val.training_hours,completed_date:val.completed_date,filename:val.filename})
        });
        
        let formvalue:any={};
        formvalue.actiontype = 'certificate';
        formvalue.certifications = certificationdatas;
        formvalue.id = this.id;
        this.certformData.append('formvalues',JSON.stringify(formvalue));
        
        this.userService.updateUserData(this.certformData)
        .pipe(first())
        .subscribe(res => {
  
            if(res.status){
              this.certificateEntries = res['resultarr']['certifications'];
              this.certificateEntries.forEach((x,index)=>{
                this.uploadedFileNames[index]= {name:x.filename,added:0,deleted:0,valIndex:index};
              });
              this.success = {summary:res.message};
              this.buttonDisable = false;
              setTimeout(() => {
                this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
              }, this.errorSummary.redirectTime);
            }else if(res.status == 0){
              this.error = this.errorSummary.getErrorSummary(res.message,this,this.certformData);	
            }else{
              this.error = {summary:res};
            }
            this.loadingArr['certificateForm'] = false;
        },
        error => {
            this.error = error;
            this.loadingArr['certificateForm'] = false;
        });
      }
  }


  

  roleformData:FormData = new FormData();
  onRoleSubmit(){
    if (this.userData.role_id && this.userData.role_id.length<=0 && this.userListEntries.length<=0) {
      this.errorSummary.validateAllFormFields(this.userloginForm);       
    }else{
        
      this.loadingArr['userloginForm'] = true;
      
      
      let roledatas = [];
      this.userListEntries.forEach((val)=>{
        //
        roledatas.push({user_password:val.user_password,username:val.username,user_role_id:val.user_role_id,role_id:val.role_id,franchise_id:val.franchise_id,deleted:val.deleted,editable:val.editable})
      });
      
      let formvalue:any={};
      formvalue.actiontype = 'role';
      formvalue.roles = roledatas;
      formvalue.id = this.id;
      this.roleformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.roleformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){

            this.success = {summary:res.message};
            this.buttonDisable = false;
            this.userListEntries = res.role;

            this.userData.is_auditor = res.is_auditor;
            if(res.role.length >0 ){
              this.hasRoles = 1;
            }else{
              this.hasRoles = 0;
            }
            
            setTimeout(() => {
              this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
            
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.roleformData);	
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['userloginForm'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['userloginForm'] = false;
      });
    }
  }
  
  rejonRoleSubmit(){
    
        
    this.loadingArr['userloginForm'] = true;
    
    
    let roledatas = [];
    this.userListEntriesRejected.forEach((val)=>{
      //
      roledatas.push({user_password:val.user_password,username:val.username,user_role_id:val.user_role_id,role_id:val.role_id,franchise_id:val.franchise_id,deleted:val.deleted})
    });
    
    let formvalue:any={};
    formvalue.actiontype = 'rejrole';
    formvalue.roles = roledatas;
    formvalue.id = this.id;
    this.roleformData.append('formvalues',JSON.stringify(formvalue));
    
    this.userService.updateUserData(this.roleformData)
    .pipe(first())
    .subscribe(res => {

        if(res.status){
          this.userListEntriesRejected = this.userListEntriesRejected.filter(x=>x.deleted!=1);
          this.success = {summary:res.message};
          this.buttonDisable = false;
          
          setTimeout(() => {
            this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
          }, this.errorSummary.redirectTime);
          
        }else if(res.status == 0){
          this.error = this.errorSummary.getErrorSummary(res.message,this,this.roleformData); 
        }else{
          this.error = {summary:res};
        }
        this.loadingArr['userloginForm'] = false;
    },
    error => {
        this.error = error;
        this.loadingArr['userloginForm'] = false;
    });
    
  }

  declarationformData:FormData = new FormData();
  onDeclarationSubmit(){
    if (this.declarationEntries.length<=0) {
      this.errorSummary.validateAllFormFields(this.declarationForm);       
    }else{
      this.loadingArr['declaration'] = true;
	  
      
      let declarationdatas = [];
      this.declarationEntries.forEach((val)=>{
        declarationdatas.push({declaration_company:val.declaration_company,declaration_contract:val.declaration_contract_id,declaration_interest:val.declaration_interest,declaration_start_year:val.declaration_start_year,declaration_end_year:val.declaration_end_year})
      }); 

      
      let formvalue:any={};
      formvalue.declaration = declarationdatas;
      formvalue.actiontype = 'declaration';
      formvalue.id = this.id;
      this.declarationformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.declarationformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){

			      this.success = {summary:res.message};
				    this.buttonDisable = false;
			      setTimeout(() => {
              this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.declarationformData);	
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['declaration'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['declaration'] = false;
      });
    }
  }
  declarationapprovedformData:FormData = new FormData();
  declarationrejectionformData:FormData = new FormData();
  onRejectDeclarationSubmit(){
    if (this.declaration_rejectedEntries.length<=0) {
      this.errorSummary.validateAllFormFields(this.declarationRejectForm);       
    }else{
      this.loadingArr['declaration'] = true;
    
      
      let declarationdatas = [];
      this.declaration_rejectedEntries.forEach((val)=>{
        declarationdatas.push({deleted:val.deleted,declaration_id:val.id,declaration_company:val.declaration_company,declaration_contract:val.declaration_contract_id,declaration_interest:val.declaration_interest,declaration_start_year:val.declaration_start_year,declaration_end_year:val.declaration_end_year})
      }); 

      
      let formvalue:any={};
      formvalue.declaration = declarationdatas;
      formvalue.actiontype = 'declarationreject';
      formvalue.id = this.id;
      this.declarationrejectionformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.declarationrejectionformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){

            this.success = {summary:res.message};
            this.buttonDisable = false;
            setTimeout(() => {
              this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.declarationrejectionformData);  
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['declaration'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['declaration'] = false;
      });
    }
  }

  

  businessformData:FormData = new FormData();
  /*
  onBusinessSubmit(){
    if (this.businessEntries.length<=0) {
      this.errorSummary.validateAllFormFields(this.businessForm);       
    }else{
      this.loadingArr['businessForm'] = true;
	  
      
      let businessdatas = [];
      this.businessEntries.forEach((val)=>{
        businessdatas.push({standard_id:val.standard_id,business_sector_id:val.business_sector_id,business_sector_group_code:val.business_sector_group_id,academic_qualification_status:val.academic_qualification,examfilename:val.examfilename,technicalfilename:val.technicalfilename})
      }); 

      
      let formvalue:any={};
      formvalue.business_sector_group = businessdatas;
      formvalue.actiontype = 'business_group';
      formvalue.id = this.id;
      this.businessformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.businessformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.businessformData = new FormData();
			      this.success = {summary:res.message};
				    this.buttonDisable = false;
            this.businessEntries = res['resultarr']['businessgroup_new'];
             this.businessEntries.forEach((x,index)=>{
        
              if(x.technicalfilename){
                this.technicalInterviewFileNames[index]= {name:x.technicalfilename,added:0,deleted:0,valIndex:index};
                
              }else{
                this.technicalInterviewFileNames[index]= '';
              }
              if(x.academic_qualification ==2){
                
                this.examFileNames[index]= {name:x.examfilename,added:0,deleted:0,valIndex:index};
                
              }else{
                this.examFileNames[index] = '';
                
              }
            });



            
			      setTimeout(() => {
              this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.businessformData);	
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['businessForm'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['businessForm'] = false;
      });
    }
  }
  */
  /*
  onSubmit(){
    this.f.certificate_name.setValidators([]);
    this.f.completed_date.setValidators([]);
    this.f.certificate_name.updateValueAndValidity();
    this.f.completed_date.updateValueAndValidity();
    
    this.f.training_subject.setValidators([]);
    this.f.training_date.setValidators([]);
    this.f.training_hours.setValidators([]);
    this.f.training_subject.updateValueAndValidity();
    this.f.training_date.updateValueAndValidity();
    this.f.training_hours.updateValueAndValidity();
    
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
    this.trainingErrors ='';

    if(this.qualificationEntries.length<=0){
      this.qualificationErrors ='true';
    }
    if(this.experienceEntries.length<=0){
      this.experienceErrors ='true';
    }
    if(this.certificateEntries.length<=0){
      this.certificateErrors ='true';
    }
    if(this.trainingEntries.length<=0){
      this.trainingErrors ='true';
    }
    
    
    
    //if (this.customerForm.valid) {
    if (this.customerForm.valid && this.qualificationEntries.length>0 && this.experienceEntries.length>0 && this.certificateEntries.length>0
        && this.trainingEntries.length>0) {
        this.loading = true;      
		
      let qualificationdatas = [];
      this.qualificationEntries.forEach((val)=>{
        qualificationdatas.push({qualification:val.qualification,board_university:val.university,subject:val.subject,passing_year:val.passingyear,percentage:val.percentage})
      });
	  
      let experiencedatas = [];
      this.experienceEntries.forEach((val)=>{
        experiencedatas.push({experience:val.experience,job_title:val.job_title,responsibility:val.responsibility,from_date:val.exp_from_date,to_date:val.exp_to_date})
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
      
      this.formData.append('formvalues',JSON.stringify(formvalue));
	  
      this.userService.updateUserData(this.formData).pipe(first()
        ).subscribe(res => {

          if(res.status){
            this.success = {summary:res.message};
            this.buttonDisable = false;
            setTimeout(()=>this.router.navigateByUrl('/master/user/view?id='+this.id),this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.customerForm)};	
          }else{
            this.error = {summary:res};
          }
          //this.clearError();
          this.loading = false;
        },
        error => {
          this.error = {summary:error};
          this.loading = false;
          //this.clearError();
        });
        
      } else {
        this.error = {summary:this.errorSummary.errorSummaryText};
        
        this.errorSummary.validateAllFormFields(this.customerForm); 
        //this.clearError();
        
      }
    }
	*/
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
      this.business_sectors_status = false;
      this.map_group_user_role_status = false;
	  this.technical_expert_business_group_status = false;

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
	  }else if(arg=='technical_expert_business_group'){
      this.technical_expert_business_group_status=true;	  
      this.userService.getBusinessSectors({user_id:this.id,type:'approved'}).subscribe(res => {
        this.technicalExpertApprovedBgSectorList = res['bsectors'];
      });	
      this.userService.getBusinessSectors({user_id:this.id}).subscribe(res => {
        this.technicalExpertBgSectorList = res['bsectors'];
      });	
      this.userService.getTeRoles({user_id:this.id}).subscribe(res => {
        this.teRoleListEntriesApproved = res['rolelist'];
      });	
    
	  }else{
		  this.business_sectors_status=true;
    }	
  }
	
	changeTEUserTab(arg)
	{
		this.approved_business_group_status=false;
		this.new_business_group_status=false;
		if(arg=='approved_business_group'){
		   this.approved_business_group_status=true;
		}else if(arg=='new_business_group'){
		   this.new_business_group_status=true;
		}
  } 
  
  closeResult: string;
  commonAction(content,action,id) 
  {
    this.model.id = id;	
    this.model.action = action;	
    this.resetBtn();
    if(action=='activate')
    {		
       this.alertInfoMessage='Are you sure, do you want to activate?';
    }
    else if(action=='deactivate')
    {		
       this.alertInfoMessage='Are you sure, do you want to deactivate?';
    }
    
    this.modalss = this.modalService.open(content, this.modalOptions);
      
    this.modalss.result.then((result) => {	
      this.closeResult = `Closed with: ${result}`;	 
    }, (reason) => {
    this.model.id = null;  
    this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;	  
    });
  }


  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return  `with: ${reason}`;
    }
  }


  //Standard Code Starts Here
  removeRejectedStandard(index:number) {
    if(index != -1)
      this.businessEntries.splice(index,1);

    
    this.examFileNames.splice(index, 1);
    this.technicalInterviewFileNames.splice(index, 1);
    

    this.businessIndex= this.businessEntries.length;
  }
  
  
  //onStandardSubmit
  rejstdformData:FormData = new FormData();
  rejstd_examfileErrors = '';
  rejstd_exam_file = '';
  rejstd_examfileChange(element) {
    let files = element.target.files;
    this.rejstd_examfileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.rejstdformData.append("std_exam_file", files[0], files[0].name);
      this.rejstd_exam_file = files[0].name;
    }else{
      this.rejstd_examfileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }
  rejremovestd_examFiles(){
    this.rejstd_exam_file = '';
    this.rejstdformData.delete('std_exam_file');
  }

  rejqua_examfileErrors='';
  rejqua_exam_file='';

  rejqua_examfileChange(element){
    let files = element.target.files;
    this.rejqua_examfileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.rejstdformData.append("qua_exam_file", files[0], files[0].name);
      this.rejqua_exam_file = files[0].name;
    }else{
      this.rejqua_examfileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }

  rejremovequa_examFiles(){
    this.rejqua_exam_file = '';
     this.rejstdformData.delete('qua_exam_file');
  }


  rejwitness_fileErrors = '';
  rejwitness_file = '';
  rejwitnessfileChange(element) {
    let files = element.target.files;
    this.rejwitness_fileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.rejstdformData.append("witness_file", files[0], files[0].name);
      this.rejwitness_file = files[0].name;
    }else{
      this.rejwitness_fileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }
  rejremovestd_witnessFiles(){
    this.rejwitness_file = '';
    this.rejstdformData.delete('witness_file');
  }

  rejrecycle_examFileErr = '';
  rejrecycle_exam_file = '';
  rejrecycle_examfileChange(element) {
    let files = element.target.files;
    this.rejrecycle_examFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.rejstdformData.append("recycle_exam_file", files[0], files[0].name);
      this.rejrecycle_exam_file = files[0].name;
    }else{
      this.rejrecycle_examFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }
  rejremoverecycle_examFiles(){
    this.rejrecycle_exam_file = '';
    this.rejstdformData.delete('recycle_exam_file');
  }
  rejremovesocial_examFiles(){
    this.rejsocial_exam_file = '';
    this.rejstdformData.delete('social_exam_file');
  }
  
  rejsocial_examFileErr = '';
  rejsocial_exam_file = '';
  rejsocial_examfileChange(element) {
    let files = element.target.files;
    this.rejsocial_examFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.rejstdformData.append("social_exam_file", files[0], files[0].name);
      this.rejsocial_exam_file = files[0].name;
    }else{
      this.rejsocial_examFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }
  rejremoveresocial_examFiles(){
    this.rejsocial_exam_file = '';
    this.rejstdformData.delete('social_exam_file');
  }

  
  rejrecycle_examfileErrors = '';
  rejsocial_exam_fileErrors='';
  onRejectionStandardSubmit(){
    this.rejstd_examfileErrors = '';
    this.rejrecycle_examfileErrors = '';
    this.rejsocial_exam_fileErrors = '';
    this.rejwitness_fileErrors = '';
    this.rejqua_examfileErrors='';
    this.srf.recycle_exam_date.setValidators([]);
    this.srf.recycle_exam_date.updateValueAndValidity();

    this.srf.social_exam_date.setValidators([]);
    this.srf.social_exam_date.updateValueAndValidity();
    //let stdid = this.standardRejectionForm.get('standard').value;
    let stddetails = this.standard_rejectedEntries[this.rejStandardIndex];
    let witness_date = this.standardRejectionForm.get('witness_date').value;
    let stdid = stddetails.standard;
    
    if(stdid){
          
      if(this.rejstd_exam_file ==''){
        this.rejstd_examfileErrors = 'Please upload Standard Exam File';
      }
      
      let selStd = this.standardList.find(x=>x.id ==stdid);
      if(selStd.code == 'GRS' || selStd.code == 'RCS'){
        if(this.rejrecycle_exam_file ==''){
          this.recycle_examfileErrors = 'Please upload Recycle Exam File';
        }

        this.srf.recycle_exam_date.setValidators([Validators.required]);
        this.srf.recycle_exam_date.updateValueAndValidity();
        this.srf.recycle_exam_date.markAsTouched();
        
      }
      
    

      if(selStd.code == 'GRS' || selStd.code == 'GOTS'){
        if(this.rejsocial_exam_file ==''){
          this.rejsocial_exam_fileErrors = 'Please upload Social Exam File';
        }
        this.srf.social_exam_date.setValidators([Validators.required]);
        this.srf.social_exam_date.updateValueAndValidity();
        this.srf.social_exam_date.markAsTouched();

      }
    }
    if(witness_date !=''){
      if(this.rejwitness_file ==''){
        this.rejwitness_fileErrors = 'Please upload Witness File';
      }
    }
    
    if(this.rejqua_exam_file ==''){
      this.rejqua_examfileErrors = 'Please upload Pre Qualification File';
    }
    
    this.srf.std_exam_date.markAsTouched();
    
    
    let std_exam_date = this.standardRejectionForm.get('std_exam_date').value;
    let social_exam_date = this.standardRejectionForm.get('social_exam_date').value;
    let recycle_exam_date = this.standardRejectionForm.get('recycle_exam_date').value;
    let pre_qualification = this.standardRejectionForm.get('pre_qualification').value;
    
    
    if (this.standardRejectionForm.valid && this.rejqua_examfileErrors=='' && this.rejwitness_fileErrors =='' && this.rejstd_examfileErrors =='' && this.rejrecycle_examfileErrors =='' && this.rejsocial_exam_fileErrors =='') {
      this.loadingArr['standardForm'] = true;
      
      

      
      let formvalue=  this.standardRejectionForm.value;
      formvalue.actiontype = 'rejectionstandards';
      formvalue.id = this.id;
      formvalue.standard = stdid;
      formvalue.pre_qualification = pre_qualification;
      formvalue.std_exam_date = this.errorSummary.displayDateFormat(std_exam_date);
      if(social_exam_date !=''){
        formvalue.social_exam_date = this.errorSummary.displayDateFormat(social_exam_date);
      }
      if(recycle_exam_date !=''){
        formvalue.recycle_exam_date = this.errorSummary.displayDateFormat(recycle_exam_date);
      }

      if(witness_date !=''){
        formvalue.witness_date = this.errorSummary.displayDateFormat(witness_date);
      }
      

      
      this.rejstdformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.rejstdformData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
           this.rejStandardIndex= undefined;
            //this.standard_rejectedEntries.splice(this.rejStandardIndex, 1);
            //this.standard_approvalwaitingEntries = res.standard_approvalwaiting;

            this.stdrejdetails = '';

            
           
            this.rejshowRecycle = false;
            this.rejshowSocial = false;
            

            this.rejstdformData = new FormData();
            this.rejrecycle_exam_file = '';
            this.rejrecycle_examfileErrors = '';

            this.rejsocial_exam_file  = '';
            this.rejsocial_exam_fileErrors  = '';

            this.rejwitness_file  = '';
            this.rejwitness_fileErrors  = '';

            this.rejstd_exam_file = '';
            this.rejstd_examfileErrors = '';
            this.rejqua_examfileErrors='';


            this.standardRejectionForm.patchValue({
                
                
                std_exam_date:'',
                recycle_exam_date:'',
                social_exam_date:'',
                witness_date:''
                
              });

              this.getUserData('standard');

            this.success = {summary:res.message};
            this.buttonDisable = false;
            setTimeout(() => {
              //this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = this.errorSummary.getErrorSummary(res.message,this,this.stdformData);  
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['standardForm'] = false;
      },
      error => {
          this.error = error;
          this.loadingArr['standardForm'] = false;
      });
    }
  }
  rejStandardIndex:any;
  stdrejdetails:any;
  editStandardRejection(index:number){

    this.rejstdformData = new FormData();
    this.rejrecycle_exam_file = '';
    this.rejrecycle_examfileErrors = '';

    this.rejsocial_exam_file  = '';
    this.rejsocial_exam_fileErrors  = '';

    this.rejwitness_file  = '';
    this.rejwitness_fileErrors  = '';

    this.rejstd_exam_file = '';
    this.rejstd_examfileErrors = '';
    this.rejqua_examfileErrors='';



    this.rejshowRecycle = false;
    this.rejshowSocial = false;


    this.rejStandardIndex= index;
    let stddetails = this.standard_rejectedEntries[index];
    this.stdrejdetails = this.standard_rejectedEntries[index];
    this.rejstd_exam_file = stddetails.standard_exam_file;
    this.rejqua_exam_file = stddetails.qua_exam_file;
    let stdlistdetails = this.standardList.find(x=> x.id == stddetails.standard);

    if(stddetails.standard_code == 'GRS' || stddetails.standard_code == 'RCS'){
      this.rejrecycle_exam_file = stddetails.recycle_exam_file;
      this.rejshowRecycle = true;
    }

    if(stddetails.standard_code == 'GRS' || stddetails.standard_code == 'GOTS'){
      this.rejsocial_exam_file = stddetails.social_course_exam_file;
      this.rejshowSocial = true;
    }

    if(stddetails.witness_date != ''){
      this.rejwitness_file = stddetails.witness_file;
    }
    this.standardRejectionForm.patchValue({
      std_exam_date:stddetails.standard_exam_date?this.errorSummary.editDateFormat(stddetails.standard_exam_date):'',
      recycle_exam_date:stddetails.recycle_exam_date?this.errorSummary.editDateFormat(stddetails.recycle_exam_date):'',
      social_exam_date:stddetails.social_course_exam_date?this.errorSummary.editDateFormat(stddetails.social_course_exam_date):'',
      witness_date:stddetails.witness_date?this.errorSummary.editDateFormat(stddetails.witness_date):'',
      pre_qualification:stddetails.pre_qualification
    });
    this.scrollToBottom();
  }
  //Standard Code Ends Here







  // Rejected Business Sector Details Code Start Here
  rejexamFileNames='';
  rejtechnicalInterviewFileNames='';
  rejuploadexamErrors='';
  rejuploadtechnicalErrors='';
  rejbusinessIndex:any;
  rejbusinessFileChange(element,type) {

    let files = element.target.files;
    for (let i = 0; i < files.length; i++) {
     
      let fileextension = files[i].name.split('.').pop().toLowerCase();
      
      if(type=='exam'){
        if(this.errorSummary.checkValidDocs(fileextension))
        {
          this.rejexamFileNames = files[i].name;
         

        }else{
          this.rejuploadexamErrors='Please upload valid files';
          element.target.value = '';
          return false;
        }
        this.rejuploadexamErrors='';
        this.rejbusinessformData.append("examFileNames", files[i], files[i].name);
      }else{
        if(this.userService.technicalvalidDocs.includes(fileextension))
        {
          this.rejtechnicalInterviewFileNames = files[i].name;
        }else{
          this.rejuploadtechnicalErrors='Please upload valid files';
          element.target.value = '';
          return false;
        }
        this.rejuploadtechnicalErrors='';
        this.rejbusinessformData.append("technicalInterviewFileNames", files[i], files[i].name);
      }
    }
    
    element.target.value = '';
    //this.upload_certificateErrors = '';
  }
  
  rejTEexamFileNames='';
  rejTEtechnicalInterviewFileNames='';
  rejTEuploadexamErrors='';
  rejTEuploadtechnicalErrors='';
  rejTEbusinessIndex:any;
  rejTEbusinessFileChange(element,type) {

    let files = element.target.files;
    for (let i = 0; i < files.length; i++) {
     
      let fileextension = files[i].name.split('.').pop().toLowerCase();
      
      if(type=='exam'){
        if(this.errorSummary.checkValidDocs(fileextension))
        {
          this.rejTEexamFileNames = files[i].name;
         

        }else{
          this.rejTEuploadexamErrors='Please upload valid files';
          element.target.value = '';
          return false;
        }
        this.rejTEuploadexamErrors='';
        this.rejTEbusinessformData.append("examFileNames", files[i], files[i].name);
      }else{
        if(this.userService.technicalvalidDocs.includes(fileextension))
        {
          this.rejTEtechnicalInterviewFileNames = files[i].name;
        }else{
          this.rejTEuploadtechnicalErrors='Please upload valid files';
          element.target.value = '';
          return false;
        }
        this.rejTEuploadtechnicalErrors='';
        this.rejTEbusinessformData.append("technicalInterviewFileNames", files[i], files[i].name);
      }
    }
    
    element.target.value = '';
    //this.upload_certificateErrors = '';
  }


  rejbusinessremoveFiles(type)
  {
    let certValIndex=0;
    if(this.rejbusinessIndex >=0 && this.rejbusinessIndex !==null){
      certValIndex = this.rejbusinessIndex;
    }else{
      certValIndex = this.business_rejectedEntries.length;
    }
    if(type=='exam'){
      this.rejexamFileNames = '';
    }else{
      this.rejtechnicalInterviewFileNames = '';
    }
    this.rejuploadtechnicalErrors = '';
    this.rejuploadexamErrors = '';
  }


  rejTEbusinessremoveFiles(type)
  {
    let certValIndex=0;
    if(this.rejTEbusinessIndex >=0 && this.rejTEbusinessIndex !==null){
      certValIndex = this.rejTEbusinessIndex;
    }else{
      certValIndex = this.business_rejectedEntries.length;
    }
    if(type=='exam'){
      this.rejTEexamFileNames = '';
    }else{
      this.rejTEtechnicalInterviewFileNames = '';
    }
    this.rejTEuploadtechnicalErrors = '';
    this.rejTEuploadexamErrors = '';
  }




  
  rejremoveBusiness(index:number) {
    if(index != -1)
      this.business_rejectedEntries.splice(index,1);

    
    //this.rejexamFileNames.splice(index, 1);
    //this.rejtechnicalInterviewFileNames.splice(index, 1);
    

    this.rejbusinessIndex= this.business_rejectedEntries.length;
  }
  
  rejbusinessStatus=true;
  

  rejexamfileStatus=false;
  rejtechnicalInterviewFileStatus=false;
  rejsameStandardError = '';
  rejaddBusiness(){

    this.certificateErrors ='';
    //this.brf.standard_id.markAsTouched();
   // this.brf.business_sector_id.markAsTouched();
    //this.brf.business_sector_group_id.markAsTouched();
    this.brf.academic_qualification.markAsTouched();
    

    this.rejexamfileStatus=true;
    this.rejtechnicalInterviewFileStatus=true;
    this.rejuploadexamErrors = '';
    this.rejuploadtechnicalErrors = '';
    this.rejsameStandardError = '';
    if(this.rejbusinessForm.valid){
      //if(this.businessEntries.fin)
     // let standard = this.rejbusinessForm.get('standard_id').value;
    //  let business_sector_id = this.rejbusinessForm.get('business_sector_id').value;
     // let business_sector_group_id = this.rejbusinessForm.get('business_sector_group_id').value;
      let academic_qualification = this.rejbusinessForm.get('academic_qualification').value;
      let rejbdetails = this.business_rejectedEntries[this.rejbusinessIndex];
      /*
      let bindex = this.business_rejectedEntries.findIndex(x=>x.standard_id == standard && x.business_sector_id == business_sector_id);
      if(bindex !== -1 && bindex != this.businessIndex){
        let errorfound = 0;
        business_sector_group_id.forEach(x=>{
          let gpindex = this.businessEntries[bindex].business_sector_group_id_arr.findIndex(y=>x==y);
          if(gpindex !==-1){
            errorfound=1;
          }
        });
        if(errorfound){
          this.sameStandardError = 'Same Standard with business sector was already added';
          return false;
        }
      }



      bindex = this.business_approvalwaitingEntries.findIndex(x=>x.standard_id == standard && x.business_sector_id == business_sector_id);
      if(bindex !== -1){
        let errorfound = 0;
        business_sector_group_id.forEach(x=>{

          if(this.business_approvalwaitingEntries[bindex] && this.business_approvalwaitingEntries[bindex].business_sector_group_id_arr && this.business_approvalwaitingEntries[bindex].business_sector_group_id_arr.length>0){
            let gpindex = this.business_approvalwaitingEntries[bindex].business_sector_group_id_arr.findIndex(y=>x==y);
            if(gpindex !==-1){
              errorfound=1;
            }
          }
        });
        if(errorfound){
          this.sameStandardError = 'Same Standard with business sector was already added';
          return false;
        }
      }

      bindex = this.business_approvedEntries.findIndex(x=>x.standard_id == standard && x.business_sector_id == business_sector_id);
      if(bindex !== -1){
        let errorfound = 0;
        business_sector_group_id.forEach(x=>{
          let gpindex = this.business_approvedEntries[bindex].business_sector_group_id_arr.findIndex(y=>x==y);
          if(gpindex !==-1){
            errorfound=1;
          }
        });
        if(errorfound){
          this.sameStandardError = 'Same Standard with business sector was already added';
          return false;
        }
      }
      */
      
      if(academic_qualification =='2'){
        if(this.rejexamFileNames  === undefined || this.rejexamFileNames ==''){
          this.rejuploadexamErrors = 'Please upload the Exam File';
          this.rejexamfileStatus=false;
        }else{
          this.rejuploadexamErrors = '';
        }

        
      }
      if(this.rejtechnicalInterviewFileNames === undefined || this.rejtechnicalInterviewFileNames ==''){
        this.rejuploadtechnicalErrors = 'Please upload the Technical Interview File';
        this.rejtechnicalInterviewFileStatus=false;
      }else{
        this.rejuploadtechnicalErrors = '';
      }
      if(!this.rejexamfileStatus || !this.rejtechnicalInterviewFileStatus)
      {
      
        return false;
      }
      
      
      
      //let business_sector_group_nameList = this.rejbgsectorgroupList.filter(x=> business_sector_group_id.includes(x.id) );
      //let business_sector_group_name = [];
      /*business_sector_group_nameList.forEach(element => {
        business_sector_group_name.push(element.group_code);
      });*/
     

        //let entry= this.certificateEntries.find(s => s.id ==  productId);
      let expobject:any= rejbdetails;
      //expobject["standard_id"] = standard;
     // expobject["business_sector_id"] = business_sector_id;
      //expobject["business_sector_group_id"] = business_sector_group_id;
      expobject["academic_qualification"] = academic_qualification;
      
      expobject["academic_qualification_name"] = academic_qualification==1?'Yes':'No';;

      
      //expobject["standard_name"] = standard_name;
     // expobject["business_sector_name"] = business_sector_name;
     // expobject["business_sector_group_name"] = business_sector_group_name.join(', ');
     // expobject["business_sector_group_name_arr"] = business_sector_group_name;
      expobject["examfilename"] = '';
      expobject["technicalfilename"] = '';
      if(academic_qualification =='2'){
        let examfilename = this.rejexamFileNames;
        expobject["examfilename"] = examfilename;

        
      }
      let technicalfilename = this.rejtechnicalInterviewFileNames;
      expobject["technicalfilename"] = technicalfilename;
      expobject["rejbindex"] = this.rejbusinessIndex;
      if(this.rejbusinessIndex!==null){
        //this.business_rejectedEntries[this.rejbusinessIndex] = expobject;

          this.loadingArr['businessForm'] = true;
        
          
          let businessdatas = [];
          businessdatas.push(expobject);


          
          let formvalue:any={};
          formvalue.business_sector_group = businessdatas;
          formvalue.actiontype = 'rejbusiness_group';
          formvalue.id = this.id;
          this.rejbusinessformData.append('formvalues',JSON.stringify(formvalue));
          
          this.userService.updateUserData(this.rejbusinessformData)
          .pipe(first())
          .subscribe(res => {
             
              
              if(res.status){
                
                
                /*
                this.business_approvalwaitingEntries = res['businessgroup_approvalwaiting'];
                this.business_rejectedEntries = res['businessgroup_rejected'];
                
                
                this.business_rejectedEntries.forEach((x,index)=>{
                  if(x.technicalfilename){
                    this.rejtechnicalInterviewFileNames[index]= {name:x.technicalfilename,added:0,deleted:0,valIndex:index};
                  }else{
                    this.rejtechnicalInterviewFileNames[index]= '';
                  }
                  if(x.academic_qualification ==2){
                    this.rejexamFileNames[index]= {name:x.examfilename,added:0,deleted:0,valIndex:index};
                  }else{
                    this.rejexamFileNames[index] = '';
                  }
                });
                

                */
                //this.business_rejectedEntries.splice(this.rejbusinessIndex,1); // = res.businessgroup_rejected;
                this.brejdetail = '';
                this.rejbusinessformData = new FormData();
                this.rejbusinessForm.reset();
                this.rejtechnicalInterviewFileNames = '';
                this.rejexamFileNames = '';
                this.rejbusinessIndex=null;

                this.getUserData('business_group');
                this.success = {summary:res.message};
                this.buttonDisable = false;
                
                setTimeout(() => {
                  this.loadingArr['businessForm'] = false;
                  this.brejdetail = '';
                  //this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
                }, this.errorSummary.redirectTime);
              }else{
                this.error = {summary:res};
              }
              //this.loadingArr['businessForm'] = false;
          },
          error => {
          this.brejdetail= '';
              this.error = error;
              this.loadingArr['businessForm'] = false;
          });




      }
      
      
      
      
    }
  }


  rejTEexamfileStatus=false;
  rejTEtechnicalInterviewFileStatus=false;
  rejTEaddBusiness(){

    this.certificateErrors ='';
   
    this.terf.academic_qualification.markAsTouched();
    

    this.rejTEexamfileStatus=true;
    this.rejTEtechnicalInterviewFileStatus=true;
    this.rejTEuploadexamErrors = '';
    this.rejTEuploadtechnicalErrors = '';
    if(this.rejTEbusinessForm.valid){
      
      let academic_qualification = this.rejTEbusinessForm.get('academic_qualification').value;
      let rejTEbdetails = this.teBusiness_rejectedEntries[this.rejTEbusinessIndex];
     
      
      if(academic_qualification =='2'){
        if(this.rejTEexamFileNames  === undefined || this.rejTEexamFileNames ==''){
          this.rejTEuploadexamErrors = 'Please upload the Exam File';
          this.rejTEexamfileStatus=false;
        }else{
          this.rejTEuploadexamErrors = '';
        }

        
      }
      if(this.rejTEtechnicalInterviewFileNames === undefined || this.rejTEtechnicalInterviewFileNames ==''){
        this.rejTEuploadtechnicalErrors = 'Please upload the Technical Interview File';
        this.rejTEtechnicalInterviewFileStatus=false;
      }else{
        this.rejTEuploadtechnicalErrors = '';
      }
      if(!this.rejTEexamfileStatus || !this.rejTEtechnicalInterviewFileStatus)
      {
        return false;
      }
      
     
      let expobject:any = rejTEbdetails;
      
      expobject["academic_qualification"] = academic_qualification;
      
      expobject["academic_qualification_name"] = academic_qualification==1?'Yes':'No';;

    
      //expobject["examfilename"] = '';
      //expobject["technicalfilename"] = '';
      if(academic_qualification =='2'){
        //let examfilename = this.rejexamFileNames;
        //expobject["examfilename"] = examfilename;

        
      }
      //let technicalfilename = this.rejtechnicalInterviewFileNames;
      //expobject["technicalfilename"] = technicalfilename;
      expobject["rejbindex"] = this.rejTEbusinessIndex;
      if(this.rejTEbusinessIndex!==null){

          this.loadingArr['rejTEbusinessForm'] = true;
        
          
          let businessdatas = [];
          businessdatas.push(expobject);


          
          let formvalue:any={};
          formvalue.business_sector_group = businessdatas;
          formvalue.actiontype = 'rejtebusiness_group';
          formvalue.id = this.id;
          this.rejTEbusinessformData.append('formvalues',JSON.stringify(formvalue));
          
          this.userService.updateUserData(this.rejTEbusinessformData)
          .pipe(first())
          .subscribe(res => {
             
              
              if(res.status){
                this.tebrejdetail = '';
               
                this.rejTEbusinessformData = new FormData();
                this.rejTEbusinessForm.reset();
                this.rejTEtechnicalInterviewFileNames = '';
                this.rejTEexamFileNames = '';
                this.rejTEbusinessIndex=null;

                this.getUserData('te_business_group');
                this.success = {summary:res.message};
                this.buttonDisable = false;
                
                setTimeout(() => {
                  this.loadingArr['rejTEbusinessForm'] = false;
                  //this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
                }, this.errorSummary.redirectTime);
              }else{
                this.error = {summary:res};
              }
          },
          error => {
          this.brejdetail= '';
              this.error = error;
              this.loadingArr['rejTEbusinessForm'] = false;
          });




      }
      
      
      
      
    }
  }


  brejdetail:any='';
  rejeditBusiness(index:number){
    this.rejbusinessIndex= index;
    let qual = this.business_rejectedEntries[index];
    this.brejdetail = qual;
    this.rejuploadtechnicalErrors = '';
   
    //if(!this.rejexamFileNames[this.rejbusinessIndex])
    //this.rejexamFileNames[this.rejbusinessIndex].name = qual.examfilename;

   // if(!this.rejtechnicalInterviewFileNames[this.rejbusinessIndex])
    //  this.rejtechnicalInterviewFileNames[this.rejbusinessIndex].name = qual.technicalfilename;
    //let business_sector_group_id = [...qual.business_sector_group_id].map(String);
    //business_sector_group_id: business_sector_group_id,
    this.rejtechnicalInterviewFileNames = qual.technicalfilename;
    this.rejexamFileNames = qual.examfilename;

    this.rejbusinessForm.patchValue({
      
      
      academic_qualification:qual.academic_qualification
    });
    //this.getBgsectorList(qual.standard_id,true);
    //this.rejgetBgsectorgroupList(qual.standard_id,qual.business_sector_id);
    this.scrollToBottom();
  }

  tebrejdetail:any='';
  

 
  terejexamFileNames:any='';
  terejeditBusiness(index:number){
    this.rejTEbusinessIndex= index;
    let qual = this.teBusiness_rejectedEntries[index];
    this.tebrejdetail = qual;
   
    this.rejTEtechnicalInterviewFileNames = qual.technicalfilename;
    this.rejTEexamFileNames = qual.examfilename;
    //console.log(qual);
    this.rejTEbusinessForm.patchValue({
      id:qual.id,
      academic_qualification:qual.academic_qualification
    });
    this.scrollToBottom();
    //this.getBgsectorList(qual.standard_id,true);
    //this.rejgetBgsectorgroupList(qual.standard_id,qual.business_sector_id);
  }

  rejbusinessformData:FormData = new FormData();
  rejTEbusinessformData:FormData = new FormData();
  /*
  rejonBusinessSubmit(){
    if (this.business_rejectedEntries.length<=0) {
      this.errorSummary.validateAllFormFields(this.rejbusinessForm);       
    }else{
      this.loadingArr['businessForm'] = true;
    
      
      let businessdatas = [];
      this.business_rejectedEntries.forEach((val)=>{
        businessdatas.push({deleted:val.deleted,id:val.id, standard_id:val.standard_id,business_sector_id:val.business_sector_id,business_sector_group_code:val.business_sector_group_id,academic_qualification_status:val.academic_qualification,examfilename:val.examfilename,technicalfilename:val.technicalfilename})
      }); 

      
      let formvalue:any={};
      formvalue.business_sector_group = businessdatas;
      formvalue.actiontype = 'rejbusiness_group';
      formvalue.id = this.id;
      this.rejbusinessformData.append('formvalues',JSON.stringify(formvalue));
      
      this.userService.updateUserData(this.rejbusinessformData)
      .pipe(first())
      .subscribe(res => {
          this.brejdetail = '';
          if(res.status){
            this.rejbusinessformData = new FormData();
            this.business_rejectedEntries = res.businessgroup_rejected;
            this.success = {summary:res.message};
            this.buttonDisable = false;
            setTimeout(() => {
              this.router.navigateByUrl('/master/user/edit?id='+res.user_id)
            }, this.errorSummary.redirectTime);
          }else{
            this.error = {summary:res};
          }
          this.loadingArr['businessForm'] = false;
      },
      error => {
      this.brejdetail= '';
          this.error = error;
          this.loadingArr['businessForm'] = false;
      });
    }
  }
  */
  regroupid:any='';
  rejonteBusinessSubmitConfirm(content,groupid){
    this.regroupid = groupid;
    
    this.alertInfoMessage='Are you sure, do you want to send for approval?';
    this.modalss = this.modalService.open(content, this.modalOptions);
    //this.modalService.open(content, this.modalOptions).result.then((result) => {
  
    this.modalss.result.then((result) => {	
      //this.closeResult = `Closed with: ${result}`;	 
    }, (reason) => {
      this.alertSuccessMessage = '';
    //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;	  
    });    
  }

  rejuser_role_id:any='';
  commonformdata:FormData = new FormData();
  rejRoleSubmitConfirm(content,user_role_id){
    this.rejuser_role_id = user_role_id;
    this.alertInfoMessage='Are you sure, do you want to send for approval?';
    this.modalss = this.modalService.open(content, this.modalOptions);
    //this.modalService.open(content, this.modalOptions).result.then((result) => {
  
    this.modalss.result.then((result) => {	
      //this.closeResult = `Closed with: ${result}`;	 
    }, (reason) => {
      this.rejuser_role_id = '';
      this.alertSuccessMessage = '';
    //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;	  
    });    
  }
  dataprocessing:any = 0;
  rejRoleSubmit(){
    this.commonformdata = new FormData();
    let formvalue:any={};
    formvalue.actiontype = 'rejrole';
    formvalue.id = this.id;
    formvalue.user_role_id = this.rejuser_role_id;
    this.commonformdata.append('formvalues',JSON.stringify(formvalue));
    this.alertInfoMessage='Please wait. Your request is processing';
    this.dataprocessing = 1;
    this.userService.updateUserData(this.commonformdata)
    .pipe(first())
    .subscribe(res => {
        this.dataprocessing = 0;
        this.rejuser_role_id = '';
        if(res.status){
          this.commonformdata = new FormData();

          //this.business_rejectedEntries = res.businessgroup_rejected;
          this.alertInfoMessage='';
          this.alertSuccessMessage = res.message;
          this.getUserData('role');
          setTimeout(()=>{
            this.modalss.close('');
            this.alertSuccessMessage = '';
          },this.errorSummary.redirectTime);
        }else{
          this.error = {summary:res};
        }
        //this.loadingArr['businessForm'] = false;
    },
    error => {
      this.dataprocessing = 0;
      this.error = error;
    });
  }


  

  rejonteBusinessSubmit(){
    let formvalue:any={};
    formvalue.actiontype = 'rejtebusiness_group';
    formvalue.id = this.id;
    formvalue.regroupid = this.regroupid;
    this.rejTEbusinessformData.append('formvalues',JSON.stringify(formvalue));
    this.alertInfoMessage='Please wait. Your request is processing';
    this.userService.updateUserData(this.rejTEbusinessformData)
    .pipe(first())
    .subscribe(res => {
        this.brejdetail = '';
        if(res.status){
          this.rejTEbusinessformData = new FormData();

          //this.business_rejectedEntries = res.businessgroup_rejected;
          this.alertInfoMessage='';
          this.alertSuccessMessage = res.message;
          this.getUserData('te_business_group');
          setTimeout(()=>{
            this.modalss.close('');
            this.alertSuccessMessage = '';
          },this.errorSummary.redirectTime);
        }else{
          this.error = {summary:res};
        }
        //this.loadingArr['businessForm'] = false;
    },
    error => {
      this.error = error;
    });
  }

  model: any = {id:null,action:null,status:'',comment:'',witness_date:'',witness_file:'',popup_witness_file:'',standard_pk_id:'',witness_comment:'',valid_until:'',user_role_type:'',bscodeid:''};
  alertInfoMessage:any = '';
  modalOptions:NgbModalOptions;
  cancelBtn:any= true;
  okBtn:any = true;
  alertSuccessMessage:any = '';
  alertErrorMessage:any = '';

  //Rejected Business sector Details Code End Here
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
    //this.modalService.open(content, this.modalOptions).result.then((result) => {
  
    this.modalss.result.then((result) => {	
      //this.closeResult = `Closed with: ${result}`;	 
    }, (reason) => {
      this.model.id = null;  
      this.model.action = null;
      this.model.actiontype = null;
      this.model.bscodeid = null;
      this.alertErrorMessage = '';
      this.alertSuccessMessage = '';
      this.alertInfoMessage='';
    //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;	  
    });
  }
  
  commonModalAction(){
    let reason = this.model.action;	
    
    let actionStatus=0;
    if(reason=='delete'){		
      
    }	
    this.commonUpdateData(actionStatus);
    
  } 


  
  displayPopBtn:any = true;
  commonUpdateData(actionStatus) 
  {
    this.displayPopBtn = false;
    this.alertInfoMessage='Please wait. Your request is processing';
    this.userService.deleteUserData({id:this.model.id,user_id:this.id,action:actionStatus,actiontype:this.model.actiontype,typeaction:this.model.action,business_sector_group_code_id:this.model.bscodeid}).pipe(first())
      .subscribe(res => {

        this.resetUserForm(this.model.actiontype);
        this.getUserData(this.model.actiontype);
        if(this.model.actiontype == 'business_group' || this.model.actiontype == 'business_group_code'){
          this.getUserData('mapuserrole');
          this.getUserData('te_business_group');
        }
        

        this.model.id = null;
        this.model.action = null;
        this.model.actiontype = null;

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

    witnessdate_change(witness_date:any){
    
      if(witness_date !=''){
      let wtdate= new Date(witness_date);
       
        let newdate = new Date(wtdate.setFullYear(wtdate.getFullYear() + 3));
       
        newdate = new Date(newdate.setDate(newdate.getDate() - 1));
        
        this.model.valid_until = this.errorSummary.editDateFormat(newdate);
        
      }
    }
}
