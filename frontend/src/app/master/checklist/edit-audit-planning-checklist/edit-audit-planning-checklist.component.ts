import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';

import { AuditPlanningChecklist } from '@app/models/master/audit-planning-checklist';

import { AuditPlanningChecklistService } from '@app/services/master/checklist/audit-planning-checklist.service';

@Component({
  selector: 'app-edit-audit-planning-checklist',
  templateUrl: '../add-audit-planning-checklist/add-audit-planning-checklist.component.html',
  styleUrls: ['./edit-audit-planning-checklist.component.scss']
})

export class EditAuditPlanningChecklistComponent implements OnInit {

  title = '';
  btnLabel = 'Update';
  
  riskCategoryList:any;
  audit_typeList:any;
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
  
  riskcategoryErrors='';
  
  formData:FormData = new FormData();
  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private auditPlanningChecklistService: AuditPlanningChecklistService ,private errorSummary: ErrorSummaryService) { }
  
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
	
	this.id = this.activatedRoute.snapshot.queryParams.id;
	this.category = this.activatedRoute.snapshot.queryParams.category;
    
    if(this.category == 2){
      this.title = 'Edit Audit Planning Unit Review Checklist';
    }else{
      this.title = 'Edit Audit Planning Review Checklist';
    }
	/*
	this.auditPlanningChecklistService.getAuditPlanningChecklistRiskCategory().subscribe(res => {
      this.riskCategoryList = res['riskCategory'];      
    });	*/
	
	this.form = this.fb.group({
	  id:[''],	
	  category:[''],	
	  name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
    guidance:['',[this.errorSummary.noWhitespaceValidator]],
	  riskcategory:['',[Validators.required]],
    audit_type:['',[Validators.required]]        
  });
	
	 this.auditPlanningChecklistService.getAuditPlanningChecklist(this.id).pipe(first())
    .subscribe(res => {
      let auditPlanningChecklist = res.data;
    this.riskCategoryList = auditPlanningChecklist['riskCategory']; 
    this.audit_typeList = auditPlanningChecklist['audittype'];
	  this.form.patchValue(auditPlanningChecklist);
    let riskcategory= auditPlanningChecklist.riskcategory.map(String);
    let audit_type = auditPlanningChecklist.audit_type.map(String);
	  this.form.patchValue({'riskcategory':riskcategory,'audit_type':audit_type});
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
	  
	  this.auditPlanningChecklistService.updateData(this.form.value)
      .pipe(
        first()        
      ).subscribe(res => {
		  
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
            //setTimeout(()=>this.router.navigate(['/master/audit-planning-checklist/list']),this.errorSummary.redirectTime);            
			setTimeout(()=>this.router.navigateByUrl('/master/audit-planning-checklist/list?category='+this.category),this.errorSummary.redirectTime);
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
