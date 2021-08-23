import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';

import { ApplicationChecklist } from '@app/models/master/application-checklist';
import { ApplicationChecklistService } from '@app/services/master/checklist/application-checklist.service';

@Component({
  selector: 'app-edit-application-checklist',
  templateUrl: '../add-application-checklist/add-application-checklist.component.html',
  styleUrls: ['./edit-application-checklist.component.scss']
})
export class EditApplicationChecklistComponent implements OnInit {

  title = 'Edit Application Checklist';  
  btnLabel = 'Update';
  answerList:any;
  form : FormGroup;
  id:number;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  nameErrors = '';
  
  answerErrors = '';

  formData:FormData = new FormData();

  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private applicationChecklistService: ApplicationChecklistService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.form = this.fb.group({	
      id:[''],
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
      guidance:['',[this.errorSummary.noWhitespaceValidator]],
      // answer:['',[Validators.required]],
      file_upload_required:['']	  
    });

    this.applicationChecklistService.getApplicationChecklist(this.id).pipe(first())
    .subscribe(res => {
      let applicationChecklist = res.data;
	    this.form.patchValue(applicationChecklist);
	  // let riskcategory= auditPlanningChecklist.riskcategory.map(String);
	  // this.form.patchValue({'riskcategory':riskcategory});
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

      let formvalue = this.form.value; 
      
      let fileUploadRequired=0;
      if(formvalue.file_upload_required)
      {
        fileUploadRequired=1;
      }
      formvalue.file_upload_required=fileUploadRequired;
	  
	  this.applicationChecklistService.updateData(formvalue)
      .pipe(
        first()        
      ).subscribe(res => {
		  
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;         
			setTimeout(()=>this.router.navigateByUrl('/master/application-checklist/list'),this.errorSummary.redirectTime);
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
