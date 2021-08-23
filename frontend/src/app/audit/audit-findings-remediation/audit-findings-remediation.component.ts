import { Component, OnInit,EventEmitter } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { FindingsCorrectiveActionService } from '@app/services/audit/findings-corrective-action.service';
import { Router,ActivatedRoute ,Params } from '@angular/router';
import { UnitFindings } from '@app/models/audit/unit-findings';
import { AuthenticationService } from '@app/services/authentication.service';
import { AuditExecutionService } from '@app/services/audit/audit-execution.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';


@Component({
  selector: 'app-audit-findings-remediation',
  templateUrl: './audit-findings-remediation.component.html',
  styleUrls: ['./audit-findings-remediation.component.scss'],
  providers:[FindingsCorrectiveActionService]
})
export class AuditFindingsRemediationComponent implements OnInit {

  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  res:any = [];
  success:any;
  UnitFindings:UnitFindings;
  title = 'Corrective Action Plan';
  public validDocs = ['pdf','docx','doc','jpeg','jpg','png'];
  
  root_causeErrors:any;
  correctionErrors:any;
  corrective_actionErrors:any;
  panelOpenState=false;
  constructor(public executionService:AuditExecutionService,private modalService: NgbModal, private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService, private FindingsCorrectiveActionService:FindingsCorrectiveActionService) { }

  unit_id:any;
  audit_plan_id:any;
  audit_id:any;
  finding_id:any;
  app_id:any;
  audit_plan_unit_id:any;
  updateStatus:number = 0;
  ngOnInit() {

    this.finding_id = this.activatedRoute.snapshot.queryParams.finding_id;

    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
    this.audit_plan_id = this.activatedRoute.snapshot.queryParams.audit_plan_id;
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.app_id = this.activatedRoute.snapshot.queryParams.app_id;
    this.audit_plan_unit_id = this.activatedRoute.snapshot.queryParams.audit_plan_unit_id;
    this.form = this.fb.group({
      finding_id:[''],
      root_cause:['',[Validators.required]], 
      correction:['',[Validators.required]], 
      evidence_file_list:[''],
      corrective_action:['',[Validators.required]],	  
    });

    this.loading = true;
    this.FindingsCorrectiveActionService.getFindingDetails(this.finding_id).pipe(first())
    .subscribe(res => {
      this.UnitFindings = res;
      this.loading = false;
    },
    error => {
        this.error = {summary:error};
        this.loading = false;
    });
    
    this.executionService.getRemediation({finding_id:this.finding_id}).pipe(first())
    .subscribe(res => {
      if(res.status){
        this.updateStatus = 1;

        this.res = res.remediation;
        this.finding_id = res.remediation.audit_plan_unit_execution_checklist_id;
        this.loading = false;
        this.evidence_file_list = this.res.evidence_file_list;
        // if(this.evidence_file_list !=''){
        //   this.curfileupload =1;
        // }
        
        this.form.patchValue({
          root_cause:this.res.root_cause,
          correction:this.res.correction,
          corrective_action:this.res.corrective_action
        });
      }else if(res && res.data && res.data.remediation_new){
        this.updateStatus = 1;

        this.res = res.data.remediation_new;
        this.finding_id = this.res.audit_plan_unit_execution_checklist_id;
        this.loading = false;
        this.evidence_file_list = this.res.evidence_file;
        // if(this.evidence_file !=''){
        //   this.curfileupload =1;
        // }
        
        this.form.patchValue({
          root_cause:this.res.root_cause,
          correction:this.res.correction,
          corrective_action:this.res.corrective_action
        });
      }
      

    },
    error => {
        this.error = {summary:error};
        this.loading = false;
    });
  }
  get f() { return this.form.controls; }
  // curfileupload:number = 0;
  modalss:any;
  open(content,arg='') {
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

  downloadFindingFile(filename){
    this.FindingsCorrectiveActionService.downloadEvidenceFile({id:this.finding_id})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
     
    });
  }

  evidenceFileError = '';
  //evidence_file = '';
  formData:FormData = new FormData();
  evidence_file_list:any=[];
  evidencefileChange(element) 
  {
    let files = element.target.files;
    this.evidenceFileError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      //this.curfileupload = 0;
      let evidence_file_listlength = this.evidence_file_list.length;
      this.formData.append('evidence_file_list['+evidence_file_listlength+']', files[0], files[0].name);
      //this.evidence_file = files[0].name;
      console.log(this.evidence_file_list);
      this.evidence_file_list.push({deleted:0,added:1,name:files[0].name});
      
    }else{
      this.evidenceFileError ='Please upload valid file';
    }
    element.target.value = '';
  }

  removeevidenceFile(filedata,index)
  {
    if(filedata.added)
    {
      this.formData.delete("evidence_file_list["+index+"]"); 
    }
    this.evidence_file_list[index].deleted =1;
    // this.evidence_file = '';
    // this.formData.delete('evidence_file');
  }

  onSubmit()
  {

    
    this.evidenceFileError ='';
    let evidence_file_list = this.evidence_file_list.filter(x=>x.deleted != 1);

    if(this.evidence_file_list===undefined || evidence_file_list.length<=0){
      this.evidenceFileError ='Please upload Evidence file';
	    return false;
    }

    if (this.form.valid) 
    {
      this.loading = true;
      
      let formvalue = this.form.value;
      formvalue.evidence_file_list = this.evidence_file_list;
      formvalue.unit_id = this.unit_id;
      formvalue.audit_plan_id = this.audit_plan_id;
      formvalue.audit_id = this.audit_id;
      formvalue.finding_id = this.finding_id;
      
      this.formData.append('formvalues',JSON.stringify(formvalue));

      this.FindingsCorrectiveActionService.addData(this.formData)
      .pipe(
        first()        
      ).subscribe(res => {
          if(res.status)
          {

            this.updateStatus = 1;
            this.success = {summary:res.message};
			      this.buttonDisable = true;
                      
			      setTimeout(() => {
              //unit_id:unit_id,audit_plan_id:audit_plan_id,audit_id:audit_id
                this.router.navigateByUrl('/audit/audit-findings?unit_id='+this.unit_id+'&app_id='+this.app_id+'&audit_plan_id='+this.audit_plan_id+'&type=nc&audit_id='+this.audit_id+'&audit_plan_unit_id='+this.audit_plan_unit_id);
            }, this.errorSummary.redirectTime);
          }
          else if(res.status == 0)
          {
            this.loading = false;
            this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};				      
          }
          else
          {			 
            this.loading = false;     
            this.error = {summary:res};
          }
                   
      },
      error => {
          this.error = {summary:error};
          this.loading = false;
      });      
    }
    else
    {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form); 
    }
  }
}
