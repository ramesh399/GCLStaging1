import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { CountryService } from '@app/services/master/country/country.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { Country } from '@app/services/country';

import { first,delay } from 'rxjs/operators';

@Component({
  selector: 'app-edit-country',
  templateUrl: '../add-country/add-country.component.html',
  styleUrls: ['./edit-country.component.scss']
})

export class EditCountryComponent implements OnInit {
  
  title = 'Edit Country';
  btnLabel = 'Update';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  success:any;
  nameErrors = '';
  codeErrors = '';
  phonecodeErrors = '';
  descriptionErrors = '';
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private countryservice: CountryService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() { 
    
    this.id = this.activatedRoute.snapshot.queryParams.id;
    

    this.form = this.fb.group({
      id:[''],
      //name:['',[Validators.required, this.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\-]+$")]],
	  name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\-]+$")]],
      code:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z]+$")]],
	  phonecode:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.pattern("^[0-9]*$"), Validators.maxLength(11)]],
    });

    this.countryservice.getCountry(this.id).pipe(first())
    .subscribe(res => {
      let result = res.data;
      this.form.patchValue(result);
    },
    error => {
        this.error = error;
        this.loading = false;
    });
  }
  
  /*
  public noWhitespaceValidator(control: FormControl) {
    const isWhitespace = (control.value || '').trim().length === 0;
    const isValid = !isWhitespace;
	return isValid ? null : { 'whitespace': true };
  }
  */

  get f() { return this.form.controls; }

  validateAllFormFields(formGroup: FormGroup) {         //{1}
    Object.keys(formGroup.controls).forEach(field => {  //{2}
	  //formGroup.get(field).setValue(formGroup.get(field).value.trim()));
      const control = formGroup.get(field);             //{3}
      if (control instanceof FormControl) {             //{4}
	    //control.setValue((control.value || '').trim());
        control.markAsTouched({ onlySelf: true });
      } else if (control instanceof FormGroup) {        //{5}
        this.validateAllFormFields(control);            //{6}
      }
    });
  }

  onSubmit(){
    
	//Object.keys(this.form.controls).forEach((key) => this.form.get(key).setValue(this.form.get(key).value.trim()));
			
    if (this.form.valid) {
      
      this.loading = true;
      
      this.countryservice.updateData(this.form.value).pipe(first()
      //delayWhen(res=>res.status? )
      //,tap(res=> res.status?this.success = res.msg )
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
      //console.log('sdfsdfdf');
    } else {
      this.error = {summary:this.errorSummary.errorSummaryText}; 
	  this.errorSummary.validateAllFormFields(this.form);	  
    }
  }

}
