import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { UnitFindingsListService } from '@app/services/audit/findings-list.service';
import { AuditExecutionService } from '@app/services/audit/audit-execution.service';

import { Router,ActivatedRoute ,Params } from '@angular/router';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { FindingsCorrectiveActionService } from '@app/services/audit/findings-corrective-action.service';
import { AuthenticationService } from '@app/services/authentication.service';

import {UnitFindings} from '@app/models/audit/unit-findings';
import {saveAs} from 'file-saver';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-audit-findings',
  templateUrl: './audit-findings.component.html',
  styleUrls: ['./audit-findings.component.scss'],
  providers:[UnitFindingsListService]
})
export class AuditFindingsComponent {

  UnitFindings$: Observable<UnitFindings[]>;
  total$: Observable<number>;
  auditplanStatus$: Observable<any>;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;
  

  form : FormGroup;
  loading = false;
  error:any;
  modalss:any;
  id:number;
  finding_id:number;
  success:any;
  title = 'List Unit Findings';
  evidence_file = '';
  status_error:any;
  comment_error:any;
  findingsList:any = [];
  res:any = [];
  severityList:any = [];
  arrEnumStatus:any = [];
  modalOptions:NgbModalOptions;

  userType:number;
  userdetails:any;
  userdecoded:any;

  audit_plan_id:number;
  audit_id:number;
  unit_id:number;
  app_id:number;
  audit_plan_unit_id:number;
  type:any;
  otherData:any;
  remed_new:any;
  remed_old:any;
  reviewer_comments:any;
  auditor_comments:any;
  unit_auditor_comments:any;

  panelOpenState=false;
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
  nctype:any = '';
  audit_type:number;
  
  constructor(public executionService:AuditExecutionService,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,public errorSummary: ErrorSummaryService, 
    private authservice:AuthenticationService, private modalService: NgbModal, public service: UnitFindingsListService, private FindingsCorrectiveActionService:FindingsCorrectiveActionService) {
    this.UnitFindings$ = service.unitfindings$;
    this.total$ = service.total$;
    this.auditplanStatus$ = service.auditplanStatus$;
    //this.auditplanLeadAuditor$ = service.auditplanLeadAuditor$;
    

    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
    this.audit_plan_id = this.activatedRoute.snapshot.queryParams.audit_plan_id;
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;	
    this.audit_plan_unit_id = this.activatedRoute.snapshot.queryParams.audit_plan_unit_id;
    this.type = this.activatedRoute.snapshot.queryParams.type;
    this.app_id = this.activatedRoute.snapshot.queryParams.app_id;
    this.nctype = this.activatedRoute.snapshot.queryParams.nctype;
    
    	// ------ New Code Start Here ---------
      this.modalOptions = this.errorSummary.modalOptions;	
      // ------ New Code End Here ---------
        this.authservice.currentUser.subscribe(x => {
          if(x){
            
            
            let user = this.authservice.getDecodeToken();
            this.userType= user.decodedToken.user_type;
            this.userdetails= user.decodedToken;
            
          }else{
            this.userdecoded=null;
          }
        });


        this.executionService.getApplicationDetails({audit_plan_id:this.audit_plan_id,audit_plan_unit_id:this.audit_plan_unit_id,unit_id:this.unit_id}).pipe(first())
        .subscribe(res => {
          if(res.status ==1){
            this.otherData = res['data'];
          }
          this.loading = false;
        },
        error => {
            this.error = {summary:error};
            this.loading = false;
        });
		
		 
        this.executionService.geAuditReportDisplayStatus({actiontype:'view',app_id:this.app_id,audit_id:this.audit_id,unit_id:this.unit_id}).pipe(first())
        .subscribe(res => {
          this.applicableforms = res;
          this.audit_type = res['audit_type'];
        },
        error => {
          this.error = error;
          this.loading = false;
        });
		 
   }

   onSort({column, direction}: SortEvent) {
    this.headers.forEach(header => {
      if (header.sortable !== column) {
        header.direction = '';
      }
    });

    this.service.sortColumn = column;
    this.service.sortDirection = direction;
  }
  
  /*
  ngOnInit() {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.audit_plan_id = this.activatedRoute.snapshot.queryParams.audit_plan_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;

    this.authservice.currentUser.subscribe(x => {
      if(x){
        
         
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        
      }
    });
    

    this.severityList = {1:'Critical',2:'Major',3:'Minor'};
    this.arrEnumStatus = {'open':1,'in_progress':2,'completed':3};
    this.findingsList = [
      {id:1,clauseno:'2',clause:'test clause 1',finding:'test finding 1',severity:1,due_date:'Feb 24, 2020',status:1,status_name:'Open'},
      {id:2,clauseno:'3',clause:'test clause 1',finding:'test finding 2',severity:2,due_date:'Feb 25, 2020',status:2,status_name:'In-Progress'},
      {id:3,clauseno:'4.1',clause:'test clause 1',finding:'test finding 3',severity:3,due_date:'Feb 26, 2020',status:3,status_name:'Submitted'},
      {id:4,clauseno:'5.6',clause:'test clause 1',finding:'test finding 4',severity:2,due_date:'Feb 27, 2020',status:2,status_name:'Open'}
    ];

    

  }
  */
  resetPageList(){
    this.service.customSearch();
  }
  model:any = {status:'',comment:''};
  auditor_status_error:any;
  auditor_comment_error:any;
  checkUserSel(action=''){
    
    if(action =='reviewer_review'){
      //this.model.status ='';
      //this.model.comment ='';

      this.status_error = '';
      this.comment_error = '';
      if(this.model.status ==''){
        this.status_error ='true';
      }
      if(this.model.comment ==''){
        this.comment_error ='true';
      }
      if(this.status_error =='' && this.comment_error ==''){
        this.modalss.close(action);
        //this.model.status ='';
        //this.model.comment ='';
      }
    }else if(action =='auditor_review'){
      //this.model.auditor_status_error ='';
      //this.model.auditor_comment_error ='';


      this.auditor_status_error = '';
      this.auditor_comment_error = '';
      if(this.model.auditor_status ==''){
        this.auditor_status_error ='true';
      }
      if(this.model.auditor_comment ==''){
        this.auditor_comment_error ='true';
      }
      if(this.auditor_status_error =='' && this.auditor_comment_error ==''){
        this.modalss.close(action);
        //this.model.auditor_status ='';
        //this.model.auditor_comment ='';
      }
    }else if(action =='closefindingcontent'){
      this.modalss.close(action);
    }else if(action =='followup_auditor_review'){
      this.auditor_status_error = '';
      this.auditor_comment_error = '';
      if(this.model.auditor_status ==''){
        this.auditor_status_error ='true';
      }
      if(this.model.auditor_comment ==''){
        this.auditor_comment_error ='true';
      }
      if(this.auditor_status_error =='' && this.auditor_comment_error ==''){
        this.modalss.close(action);
      }
    }else if(action =='followup_lead_auditor_review'){
      this.auditor_status_error = '';
      this.auditor_comment_error = '';
      if(this.model.auditor_status ==''){
        this.auditor_status_error ='true';
      }
      if(this.model.auditor_comment ==''){
        this.auditor_comment_error ='true';
      }
      if(this.auditor_status_error =='' && this.auditor_comment_error ==''){
        this.modalss.close(action);
      }
    }else if(action =='followup_reviewer_review'){
      this.auditor_status_error = '';
      this.auditor_comment_error = '';
      if(this.model.auditor_status ==''){
        this.auditor_status_error ='true';
      }
      if(this.model.auditor_comment ==''){
        this.auditor_comment_error ='true';
      }
      if(this.auditor_status_error =='' && this.auditor_comment_error ==''){
        this.modalss.close(action);
      }
    }
  }
  
  showremediation(content,finding_id)
  {
    this.res = undefined;
    this.loading = true;
    this.modalss = this.modalService.open(content, {size:'lg',ariaLabelledBy: 'modal-basic-title'});

    this.executionService.getRemediation({finding_id:finding_id,nctype:this.nctype}).pipe(first())
    .subscribe(res => {
      this.res = res.data;
      this.finding_id = this.res.finding_id;
      this.remed_new = this.res.remediation_new;
      this.remed_old = this.res.remediation_old;
      this.reviewer_comments = this.res.reviewer_comments;
      this.auditor_comments = this.res.auditor_comments;
      this.unit_auditor_comments = this.res.unit_auditor_comments;
      this.loading = false;
    },
    error => {
        this.error = {summary:error};
        this.loading = false;
    });
  }

  showdownloadingpopup(content)
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  downloadFile(fileid,filename){
    this.executionService.downloadEvidenceFile({id:fileid})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
     
    });
  }

  downloadFindingFile(filename,finding_id){
    this.FindingsCorrectiveActionService.downloadEvidenceFile({id:finding_id})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
     
    });
  }

  open(content,finding_id) {
    
    this.model.status ='';
    this.model.comment ='';
    this.model.auditor_status ='';
    this.model.auditor_comment ='';

    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    
    this.modalss.result.then((result) => {
      
      if(result =='reviewer_review'){
        let data = {audit_plan_unit_id:this.audit_plan_unit_id,unit_id:this.unit_id,finding_id:finding_id,audit_plan_id:this.audit_plan_id,audit_id:this.audit_id,comment:this.model.comment,status:this.model.status};
        this.approveApplication(data);
      }else if(result =='auditor_review'){
        let data = {audit_plan_unit_id:this.audit_plan_unit_id,unit_id:this.unit_id,finding_id:finding_id,audit_plan_id:this.audit_plan_id,audit_id:this.audit_id,comment:this.model.auditor_comment,status:this.model.auditor_status};
        this.auditorApproveApplication(data);
      }else if(result =='closefindingcontent'){
        let data = {audit_plan_unit_id:this.audit_plan_unit_id,unit_id:this.unit_id,finding_id:finding_id,audit_plan_id:this.audit_plan_id,audit_id:this.audit_id,comment:this.model.auditor_comment,status:this.model.auditor_status};
        this.closeFindings(data);
      }else if(result =='followup_auditor_review'){
        let data = {actiontype:result,audit_plan_unit_id:this.audit_plan_unit_id,unit_id:this.unit_id,finding_id:finding_id,audit_plan_id:this.audit_plan_id,audit_id:this.audit_id,comment:this.model.auditor_comment,status:this.model.auditor_status};
        this.saveFollowupFindingReview(data);
      }else if(result =='followup_lead_auditor_review'){
        let data = {actiontype:result,audit_plan_unit_id:this.audit_plan_unit_id,unit_id:this.unit_id,finding_id:finding_id,audit_plan_id:this.audit_plan_id,audit_id:this.audit_id,comment:this.model.auditor_comment,status:this.model.auditor_status};
        this.saveFollowupFindingReview(data);
      }else if(result =='followup_reviewer_review'){
        let data = {actiontype:result,audit_plan_unit_id:this.audit_plan_unit_id,unit_id:this.unit_id,finding_id:finding_id,audit_plan_id:this.audit_plan_id,audit_id:this.audit_id,comment:this.model.auditor_comment,status:this.model.auditor_status};
        this.saveFollowupFindingReview(data);
      }
      
      
      
    }, (reason) => {
      
      //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
    });
  }

  
  closeFindings(data){
    this.loading  = true;
    
    this.executionService.ReviewerCloseFindings(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //let resdata = res.data;
            this.success = {summary:res.message};


            this.resetPageList();
            //let finding_id = resdata.finding_id;
            //this.UnitFindings$.find(x=>x.id ===finding_id).status = resdata.status;
            //unitFinding.status = ;

            //setTimeout(()=>this.router.navigate(['/application/list']),this.errorSummary.redirectTime);
            
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


  successmsg:any;
  approveApplication(data){
    this.loading  = true;
    
    this.executionService.saveReviewerCustomerReview(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //let resdata = res.data;
            this.success = {summary:res.message};


            this.resetPageList();
            //let finding_id = resdata.finding_id;
            //this.UnitFindings$.find(x=>x.id ===finding_id).status = resdata.status;
            //unitFinding.status = ;

            //setTimeout(()=>this.router.navigate(['/application/list']),this.errorSummary.redirectTime);
            
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
  auditorApproveApplication(data){
    this.loading  = true;
    
    this.executionService.saveAuditorFindingReview(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //let resdata = res.data;
            this.success = {summary:res.message};
            this.resetPageList();
            //let finding_id = resdata.finding_id;
            //this.UnitFindings$.find(x=>x.id ===finding_id).status = resdata.status;
            //unitFinding.status = ;

            //setTimeout(()=>this.router.navigate(['/application/list']),this.errorSummary.redirectTime);
            
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

  saveFollowupFindingReview(data){
    this.loading  = true;
    
    this.executionService.saveFollowupFindingReview(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //let resdata = res.data;
            this.success = {summary:res.message};
            this.resetPageList();
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
   
  /*
  reviewerApproveApplication(data){
    this.loading  = true;
    
    this.executionService.saveReviewerFindingApproval(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //let resdata = res.data;
            this.success = {summary:res.message};

            this.resetPageList();
            //let finding_id = resdata.finding_id;
            //this.UnitFindings$.find(x=>x.id ===finding_id).status = resdata.status;
            //unitFinding.status = ;

            //setTimeout(()=>this.router.navigate(['/application/list']),this.errorSummary.redirectTime);
            
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
  */
  
  changeAuditExecutionTab(arg)
  {
	  this.checklist_status=false;
	  this.attendance_status=false; 
	  this.sampling_status=false;
	  this.interview_status=false; 
	  this.client_information_status=false;
	  this.environment_status=false; 
	  this.living_wage_calc_status=false; 
	  this.qbs_status=false; 
	  this.chemical_list_status=false; 
    this.audit_ncn_report_status=false;
    this.report_status=false;
	  
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

} 
