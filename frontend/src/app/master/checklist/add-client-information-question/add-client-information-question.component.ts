import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';

import {ClientInformationChecklist} from '@app/models/master/client-information-checklist';
import { StandardService } from '@app/services/standard.service';
import { ProcessService } from '@app/services/master/process/process.service';
import {ClientInformationChecklistService} from '@app/services/master/checklist/client-information-checklist.service';
import { AuditReviewerChecklistService } from '@app/services/master/checklist/audit-reviewer-checklist.service';


@Component({
  selector: 'app-add-client-information-question',
  templateUrl: './add-client-information-question.component.html',
  styleUrls: ['./add-client-information-question.component.scss']
})
export class AddClientInformationQuestionComponent implements OnInit {

  title = 'Add Client Information Question Checklist';  
  btnLabel = 'Save';
  riskCategoryList:any;
  categoryList:any;
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  product_idErrors = '';
  product_type_idErrors = '';
  nameErrors = '';
  standardList:any; 
  processList:any;
  riskcategoryErrors = '';
  
  formData:FormData = new FormData();
  category:any;
  constructor(private router: Router,private fb:FormBuilder, private processService:ProcessService, private clientInformationChecklistService: ClientInformationChecklistService,private auditReviewerChecklistService: AuditReviewerChecklistService,private errorSummary: ErrorSummaryService,public standardservice:StandardService) { }

  ngOnInit() {

    this.form = this.fb.group({
        name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
        interpretation:['',[this.errorSummary.noWhitespaceValidator]],
        riskcategory:['',[Validators.required]],
        standard_id:['',[Validators.required]],
        //process_id:['',[Validators.required]],
        client_information_id:['',[Validators.required]]
      });	

    this.auditReviewerChecklistService.getAuditReviewerChecklistRiskCategory().subscribe(res => {
      this.riskCategoryList = res['riskCategory'];      
    });

    this.standardservice.getStandard().subscribe(res => {
      this.standardList = res['standards'];
    });
    /*
    this.processService.getProcessList().pipe(first()).subscribe(res => {
      this.processList = res['processes'];      
    });	
    */
    this.clientInformationChecklistService.getClientInformations().subscribe(res => {
      this.categoryList = res['informations'];      
    });
  }

  getSelectedValue(type,val)
  {
    if(type=='riskcategory')
    {
      return this.riskCategoryList.find(x=> x.id==val).name;
    }
    else if(type=='standard')
    {
      return this.standardList.find(x=> x.id==val).name;
    }
    else if(type=='process')
    {
      return this.processList.find(x=> x.id==val).name;
    }
  }

  get f() { return this.form.controls; }

  onSubmit(){
    if (this.form.valid) {
      
      this.loading = true;
	  
	  this.clientInformationChecklistService.addData(this.form.value)
      .pipe(
        first()        
      ).subscribe(res => {
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
            setTimeout(()=>this.router.navigate(['/master/client-information-question/list']),this.errorSummary.redirectTime);			
          }else if(res.status == 0){
            this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};				      
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
      this.errorSummary.validateAllFormFields(this.form);       
    }
  }

}
