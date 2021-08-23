import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';

import { AuditPlanningChecklist } from '@app/models/master/audit-planning-checklist';
import { AuditPlanningChecklistService } from '@app/services/master/checklist/audit-planning-checklist.service';

@Component({
  selector: 'app-add-audit-planning-checklist',
  templateUrl: './add-audit-planning-checklist.component.html',
  styleUrls: ['./add-audit-planning-checklist.component.scss']
})
export class AddAuditPlanningChecklistComponent implements OnInit {

  title = '';  
  btnLabel = 'Save';
  riskCategoryList:any;
  audit_typeList:any;
  category:number; 
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  product_idErrors = '';
  product_type_idErrors = '';
  nameErrors = '';
  
  
  formData:FormData = new FormData();
  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private auditPlanningChecklistService: AuditPlanningChecklistService,private errorSummary: ErrorSummaryService) { }
   
  getSelectedValue(type,val)
  {
    if(type=='riskcategory'){
      return this.riskCategoryList.find(x=> x.id==val).name;
    }
    if(type=='audit_type'){
      return this.audit_typeList[val];
    }
  }
  
  ngOnInit() {
	this.category = this.activatedRoute.snapshot.queryParams.category;
	this.auditPlanningChecklistService.getAuditPlanningChecklistRiskCategory().subscribe(res => {
      this.riskCategoryList = res['riskCategory'];
      this.audit_typeList = res['audittype'];      
  });
	
	this.form = this.fb.group({
	  category:[''],	
	  name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
    guidance:['',[this.errorSummary.noWhitespaceValidator]],
    riskcategory:['',[Validators.required]],
    audit_type:['',[Validators.required]]    
    });
	
	if(this.category == 2){
      this.title = 'Add Audit Planning Unit Review Checklist';
    }else{
      this.title = 'Add Audit Planning Review Checklist';
    }
	
	this.form.patchValue({category:this.category});
	
  }  

  get f() { return this.form.controls; }
    
  onSubmit(){
    if (this.form.valid) {
      
      this.loading = true;
	  
	  this.auditPlanningChecklistService.addData(this.form.value)
      .pipe(
        first()        
      ).subscribe(res => {
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
            //setTimeout(()=>this.router.navigate(['/master/audit-planning-checklist/list']),this.errorSummary.redirectTime);            
			setTimeout(() => {
                this.router.navigateByUrl('/master/audit-planning-checklist/list?category='+this.category);
            }, this.errorSummary.redirectTime);
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
