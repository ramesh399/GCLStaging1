import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';

import { AuditReviewerChecklist } from '@app/models/master/audit-reviewer-checklist';
import { StandardService } from '@app/services/standard.service';
import { AuditReviewerChecklistService } from '@app/services/master/checklist/audit-reviewer-checklist.service';

@Component({
  selector: 'app-add-audit-reviewer-checklist',
  templateUrl: './add-audit-reviewer-checklist.component.html',
  styleUrls: ['./add-audit-reviewer-checklist.component.scss']
})
export class AddAuditReviewerChecklistComponent implements OnInit {

  title = 'Add Certificate Reviewer Review Checklist';  
  btnLabel = 'Save';
  riskCategoryList:any;
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
  
  riskcategoryErrors = '';
  
  formData:FormData = new FormData();
  category:any;
  constructor(private router: Router,private fb:FormBuilder,private auditReviewerChecklistService: AuditReviewerChecklistService,private errorSummary: ErrorSummaryService,public standardservice:StandardService) { }
   
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
  }
  
  ngOnInit() {
	  this.auditReviewerChecklistService.getAuditReviewerChecklistRiskCategory().subscribe(res => {
      this.riskCategoryList = res['riskCategory'];      
    });

    this.standardservice.getStandard().subscribe(res => {
      this.standardList = res['standards'];
    });
	
	this.form = this.fb.group({
	  name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
      guidance:['',[this.errorSummary.noWhitespaceValidator]],
      riskcategory:['',[Validators.required]],
      standard_id:['',[Validators.required]]
    });	
  }  

  get f() { return this.form.controls; }
    
  onSubmit(){
    if (this.form.valid) {
      
      this.loading = true;
	  
	  this.auditReviewerChecklistService.addData(this.form.value)
      .pipe(
        first()        
      ).subscribe(res => {
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
            setTimeout(()=>this.router.navigate(['/master/audit-reviewer-checklist/list']),this.errorSummary.redirectTime);			
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
