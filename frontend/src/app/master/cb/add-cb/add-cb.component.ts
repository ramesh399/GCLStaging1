import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { CbService } from '@app/services/master/cb/cb.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-add-cb',
  templateUrl: './add-cb.component.html',
  styleUrls: ['./add-cb.component.scss']
})
export class AddCbComponent implements OnInit {

  title = 'Add CB';
  btnLabel = 'Save';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  success:any;
  submittedError = false;
  nameErrors = '';
  constructor(private router: Router,private fb:FormBuilder,private CbService:CbService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	this.form = this.fb.group({
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z0-9 \'\-+%/&,().-]+$")]],      
      description:['',[this.errorSummary.noWhitespaceValidator]]      
    });
  }

  get f() { return this.form.controls; } 
  
  onSubmit(){
    //console.log(this.form.valid);
    //console.log(this.form.value);
    if (this.form.valid) {
      
      //console.log(this.form.value);
      this.loading = true;
      
      this.CbService.addData(this.form.value)
      .pipe(
        first()        
      )
      .subscribe(res => {
        //console.log(res);
          if(res.status){
			this.success = {summary:res.message};
			this.buttonDisable = true;
            setTimeout(()=>this.router.navigate(['/master/cb/list']),this.errorSummary.redirectTime);
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
      //console.log('sdfsdfdf');
    } else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form); 
      
    }
  }

}

