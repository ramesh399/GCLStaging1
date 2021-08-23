import { Component, OnInit } from '@angular/core';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { SeverityTimeline } from '@app/services/master/audit-severity-timeline/timeline.service';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-audit-execution-severity',
  templateUrl: './audit-execution-severity.component.html',
  styleUrls: ['./audit-execution-severity.component.scss']
})
export class AuditExecutionSeverityComponent implements OnInit {

  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  actionsError = '';
  success:any;
  title = 'Non - Conformative Timeline';
  major_duedaysErrors='';
  minor_duedaysErrors = '';
  critical_duedaysErrors = '';
  
  constructor(private router: Router,private fb:FormBuilder,private errorSummary: ErrorSummaryService,private severitytimeline: SeverityTimeline) { }

  ngOnInit() 
  {
    this.form = this.fb.group({
      major_duedays:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(3),Validators.pattern("^[0-9]+$")]],
      minor_duedays:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(3),Validators.pattern("^[0-9]+$")]],
      critical_duedays:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(3),Validators.pattern("^[0-9]+$")]]
    });
  
    this.severitytimeline.getTimelines().pipe(first())
    .subscribe(res => {
     
      this.form.patchValue({
        critical_duedays:res.Critical.timeline,
        major_duedays:res.Major.timeline,
        minor_duedays:res.Minor.timeline
      });
    },
    error => {
        error = error;
        this.loading = false;
    });

    
	
  }
  get f() { return this.form.controls; }

  onSubmit()
  {
    if (this.form.valid) 
    {
      this.loading = true;
      this.buttonDisable = true;
      let formvalue = this.form.value; 

      this.severitytimeline.updateSeverityTimeline(formvalue).pipe(first()).subscribe(res => {

          if(res.status)
          {
			      this.success = {summary:res.message};
            this.buttonDisable = false;
          }
          else if(res.status == 0)
          {
			      this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
          }
          else
          {			      
            this.error = {summary:res};
          }
          this.loading = false;
         
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
