import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { CountryService } from '@app/services/master/country/country.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Router } from '@angular/router';
import { Country } from '@app/services/country';

import { first } from 'rxjs/operators';

@Component({
  selector: 'app-add-country',
  templateUrl: './add-country.component.html',
  styleUrls: ['./add-country.component.scss']
})
export class AddCountryComponent implements OnInit {
	
  title = 'Add Country';
  btnLabel = 'Save';  
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  success:any;
  
  nameErrors = '';
  codeErrors = '';
  phonecodeErrors = '';
  descriptionErrors = '';

  constructor(private router: Router,private fb:FormBuilder,private countryservice: CountryService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() { 
    /*
	this.countryservice.getCountry().subscribe(res => {
      this.countryList = res['countries'];
    });
	*/

    this.form = this.fb.group({
	  name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\-]+$")]],
      code:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z]+$")]],
	  phonecode:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.pattern("^[0-9]*$"), Validators.maxLength(11)]],
    });
  }
  
  trimValue(event) { event.target.value = event.target.value.trim(); }

  get f() { return this.form.controls; }

  validateAllFormFields(formGroup: FormGroup) {         //{1}
    Object.keys(formGroup.controls).forEach(field => {  //{2}
      const control = formGroup.get(field);             //{3}
      if (control instanceof FormControl) {             //{4}
        control.markAsTouched({ onlySelf: true });
      } else if (control instanceof FormGroup) {        //{5}
        this.validateAllFormFields(control);            //{6}
      }
    });
  }

  onSubmit(){
    //console.log(this.form.valid);
    //console.log(this.form.value);
    if (this.form.valid) {
      
      
      this.loading = true;
      
      this.countryservice.addData(this.form.value)
      .pipe(
        first()        
      )
      .subscribe(res => {

          if(res.status){
			this.success = {summary:res.message};
			this.buttonDisable = true;
			setTimeout(() => {
                this.router.navigateByUrl('/master/country/list');
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

