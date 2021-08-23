import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditRaScopeHolder } from '@app/models/audit/audit-ra-scopeholder';
import { AuditRaScopeHolderService } from '@app/services/audit/audit-ra-scopeholder.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-audit-ra-scopeholder',
  templateUrl: './audit-ra-scopeholder.component.html',
  styleUrls: ['./audit-ra-scopeholder.component.scss'],
  providers: [AuditRaScopeHolderService]
})
export class AuditRaScopeholderComponent implements OnInit {

  title = 'Audit Attendance Sheet'; 
  form : FormGroup; 
  scope_holders$: Observable<AuditRaScopeHolder[]>;
  total$: Observable<number>;
  id:number;
  audit_id:number;
  scope_holderData:any;
  ScopeHolderData:any;
  risklist:any;
  conformitylist:any;
  auditTypelist:any;
  error:any;
  success:any;
  buttonDisable = false;
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  modalss:any;
  loading:any=[];
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service:AuditRaScopeHolderService, public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
    this.scope_holders$ = service.scope_holders$;
    this.total$ = service.total$;
  }

  ngOnInit() 
  {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;

    this.form = this.fb.group({	
      potential_risks:['',[Validators.required, this.errorSummary.noWhitespaceValidator]], 
      measures_for_risk_reduction:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      frequency_of_risk:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      probability_rate:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      responsible_person:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      description_of_risk:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      auditor_comments:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      conformity:['',[Validators.required]],
      type_of_risk_id:['',[Validators.required]],
      audit_type_id:['',[Validators.required]]	
    });


    this.service.getOptionList().pipe(first())
    .subscribe(res => {    
      this.risklist  = res.risklist;
      this.conformitylist  = res.conformitylist;
      this.auditTypelist  = res.auditTypelist;
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });

    
    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
      }else{
        this.userdecoded=null;
      }
    });
  }

  get f() { return this.form.controls; }

  scope_holderIndex:number=null;
  addscope_holder()
  {
    this.f.potential_risks.markAsTouched();
    this.f.measures_for_risk_reduction.markAsTouched();
    this.f.frequency_of_risk.markAsTouched();
    this.f.conformity.markAsTouched();
    this.f.type_of_risk_id.markAsTouched();
    this.f.probability_rate.markAsTouched();
    this.f.audit_type_id.markAsTouched();
    this.f.responsible_person.markAsTouched();
    this.f.description_of_risk.markAsTouched();
    this.f.auditor_comments.markAsTouched();
   

    if(this.form.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let potential_risks = this.form.get('potential_risks').value;
      let frequency_of_risk = this.form.get('frequency_of_risk').value;
      let measures_for_risk_reduction = this.form.get('measures_for_risk_reduction').value;
      let conformity = this.form.get('conformity').value;
      let type_of_risk_id = this.form.get('type_of_risk_id').value;
      let audit_type_id = this.form.get('audit_type_id').value;
      let probability_rate = this.form.get('probability_rate').value;
      let responsible_person = this.form.get('responsible_person').value;
      let description_of_risk = this.form.get('description_of_risk').value;
      let auditor_comments = this.form.get('auditor_comments').value;


      let expobject:any={audit_id:this.audit_id,potential_risks:potential_risks,frequency_of_risk:frequency_of_risk,measures_for_risk_reduction:measures_for_risk_reduction,conformity:conformity,type_of_risk_id:type_of_risk_id,audit_type_id:audit_type_id,probability_rate:probability_rate,responsible_person:responsible_person,description_of_risk:description_of_risk,auditor_comments:auditor_comments};
      
      if(1)
      {
        if(this.scope_holderData)
        {
          expobject.id = this.scope_holderData.id;
        }
        
        this.service.addData(expobject)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              this.success = {summary:res.message};
              this.service.customSearch();
              this.scope_holderFormreset();
              
              this.buttonDisable = false;
              
              
             
            }else if(res.status == 0){
              //this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.enquiryForm)};
              this.error = {summary:res};
            }
            this.loading['button'] = false;
            this.buttonDisable = false;
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        });
        
      } else {
        
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.form); 
        
      }   
    }
  }


  viewScopeHolder(content,data)
  {
    this.ScopeHolderData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  editStatus=0;
  editScopeHolder(index:number,scope_holderdata) 
  { 
    this.editStatus=1;
    this.success = {summary:''};
    this.scope_holderData = scope_holderdata;
    this.form.patchValue({
      potential_risks:scope_holderdata.potential_risks,
      frequency_of_risk:scope_holderdata.frequency_of_risk,
      measures_for_risk_reduction:scope_holderdata.measures_for_risk_reduction,     
      conformity:scope_holderdata.conformity,
      audit_type_id:scope_holderdata.audit_type_id,
      probability_rate:scope_holderdata.probability_rate,
      responsible_person:scope_holderdata.responsible_person,
      type_of_risk_id:scope_holderdata.type_of_risk_id,
      description_of_risk:scope_holderdata.description_of_risk,
      auditor_comments:scope_holderdata.auditor_comments
    });
    this.scrollToBottom();
  }


  removeScopeHolder(content,index:number,scope_holderdata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.scope_holderFormreset();
        this.service.deleteData({id:scope_holderdata.id})
        .pipe(first())
        .subscribe(res => {

            if(res.status){
              this.service.customSearch();
              this.success = {summary:res.message};
              this.buttonDisable = true;
            }else if(res.status == 0){
              this.error = {summary:res};
            }
            this.loading['button'] = false;
            this.buttonDisable = false;
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        });
    }, (reason) => {
    })
    
  
  }

  scope_holderFormreset()
  {
    this.editStatus=0;
    
    this.scope_holderData = '';  
    this.form.reset();
    
    this.form.patchValue({     
      potential_risks:'',   
      frequency_of_risk:'',  
      responsible_person:'',
      measures_for_risk_reduction:'',
      description_of_risk:'',
      auditor_comments:'',
      probability_rate:'',
      conformity:'',
      audit_type_id:'',
      type_of_risk_id:''
    });
  }

  onSubmit(){ }

  scrollToBottom()
  {
    window.scroll({ 
      top: window.innerHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }

}
