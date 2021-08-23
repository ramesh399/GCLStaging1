import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';

import { AuditReviewerChecklist } from '@app/models/master/audit-reviewer-checklist';
import { StandardService } from '@app/services/standard.service';
import { AuditReviewerChecklistService } from '@app/services/master/checklist/audit-reviewer-checklist.service';

@Component({
  selector: 'app-edit-audit-reviewer-checklist',
  templateUrl: '../add-audit-reviewer-checklist/add-audit-reviewer-checklist.component.html',
  styleUrls: ['./edit-audit-reviewer-checklist.component.scss']
})
export class EditAuditReviewerChecklistComponent implements OnInit {

  title = 'Edit Certificate Reviewer Review Checklist';
  btnLabel = 'Update';
  riskCategoryList:any;
  
  category:number;   
  form : FormGroup;
  id:number;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  product_idErrors = '';
  product_type_idErrors = '';
  nameErrors = '';
  standardList:any; 

  riskcategoryErrors='';
  
  formData:FormData = new FormData();
  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private auditReviewerChecklistService: AuditReviewerChecklistService ,private errorSummary: ErrorSummaryService,public standardservice:StandardService) { }
  
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
	
	this.id = this.activatedRoute.snapshot.queryParams.id;
			
	this.form = this.fb.group({
	  id:[''],	
	  name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
      guidance:['',[this.errorSummary.noWhitespaceValidator]],
	    riskcategory:['',[Validators.required]],
      standard_id:['',[Validators.required]]   
    });
  
    this.standardservice.getStandard().subscribe(res => {
      this.standardList = res['standards'];
    });

	 this.auditReviewerChecklistService.getAuditReviewerChecklist(this.id).pipe(first())
    .subscribe(res => {
      let auditReviewerChecklist = res.data;
	  this.riskCategoryList = auditReviewerChecklist['riskCategory']; 
	  this.form.patchValue(auditReviewerChecklist);
	  let riskcategory = auditReviewerChecklist.riskcategory.map(String);
    this.form.patchValue({'riskcategory':riskcategory});
    let standards = auditReviewerChecklist.standard.map(String);
	  this.form.patchValue({'standard_id':standards});
    },
    error => {
        this.error = error;
        this.loading = false;
    });
	
  }

  get f() { return this.form.controls; }
  
  onSubmit(){
    if (this.form.valid) {
      
      this.loading = true; 
	  
	  this.auditReviewerChecklistService.updateData(this.form.value)
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