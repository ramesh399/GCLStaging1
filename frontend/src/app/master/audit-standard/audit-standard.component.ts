import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { AuditStandardService } from '@app/services/master/auditStandard/audit-standard.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';
@Component({
  selector: 'app-audit-standard',
  templateUrl: './audit-standard.component.html',
  styleUrls: ['./audit-standard.component.scss']
})
export class AuditStandardComponent implements OnInit {

  title = 'Audit Standards';
  btnLabel = 'Save';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  nameErrors = '';
  codeErrors = '';
  descriptionErrors = '';
  auditexpformData:FormData = new FormData();
  
  constructor(private router: Router,private fb:FormBuilder,
    private auditStandardService:AuditStandardService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	this.form = this.fb.group({
      name:['',[Validators.required,  Validators.maxLength(255)]],      
	  code:['',[Validators.required,  Validators.maxLength(50)]],
      version:['', [Validators.required,  Validators.maxLength(50)]]      
    });
  }

  get f() { return this.form.controls; }
    
  onSubmit(){
    //console.log(this.form.valid);
    //console.log(this.form.value);
    if (this.form.valid) {
      this.auditStandardService.standards.push(this.form.value);
      console.log(this.form.value);
      this.auditexpformData.append('formvalues',JSON.stringify({
        "audit_standard": [this.form.value],
        "actiontype": "audit_standard",
        "id": "67"
      }));

      this.auditStandardService.addData(this.auditexpformData)
      .pipe(
        first()        
      )   
      .subscribe(res => {
        //console.log(res);
          if(res.status){			  
            this.success = {summary:res.message};
            this.buttonDisable = true;
            this.form.reset();  
			 setTimeout(()=>this.router.navigate(['master/list-audit-standard']));
      //     }else if(res.status == 0){
      //       this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};			      
      //     }else{			      
      //       this.error = {summary:res};
      //     }
      //     this.loading = false;
          }
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
