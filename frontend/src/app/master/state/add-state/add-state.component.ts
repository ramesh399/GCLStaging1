import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { StatesService } from '@app/services/master/states/states.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { CountryService } from '@app/services/country.service';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';
import { Country } from '@app/services/country';

@Component({
  selector: 'app-add-state',
  templateUrl: './add-state.component.html',
  styleUrls: ['./add-state.component.scss']
})
export class AddStateComponent implements OnInit {

  title = 'Add State';
  btnLabel = 'Save';
  form : FormGroup;
  countryList:Country[];
  loading = false;
  buttonDisable = false;
  error:any;
  success:any;
  submittedError = false;
  
  nameErrors = '';
  country_idErrors = '';
  
  constructor(private router: Router,private fb:FormBuilder,private countryservice: CountryService,private statesService:StatesService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	
	this.countryservice.getCountry().subscribe(res => {
	  //console.log(res['countries']);
      this.countryList = res['countries'];
    });
	
	
	this.form = this.fb.group({
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\-]+$")]],      
      country_id:['',[Validators.required]]    
    });
  }

  get f() { return this.form.controls; }
    
  onSubmit(){
    //console.log(this.form.valid);
    //console.log(this.form.value);
    if (this.form.valid) {
      this.loading = true;
      
      this.statesService.addData(this.form.value)
      .pipe(
        first()        
      )
      .subscribe(res => {
        //console.log(res);
          if(res.status){
			this.success = {summary:res.message};
			this.buttonDisable = true;
			setTimeout(() => {
                this.router.navigateByUrl('/master/state/list');
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

