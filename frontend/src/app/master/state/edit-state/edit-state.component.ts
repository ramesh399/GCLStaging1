import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { StatesService } from '@app/services/master/states/states.service';
import { CountryService } from '@app/services/country.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import { Country } from '@app/services/country';

@Component({
  selector: 'app-edit-state',
  templateUrl: '../add-state/add-state.component.html',
  styleUrls: ['./edit-state.component.scss']
})
export class EditStateComponent implements OnInit {

  title = 'Edit State';
  btnLabel = 'Update';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  success:any;
  countryList:Country[];
  
  nameErrors = '';
  country_idErrors = '';
  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private countryservice: CountryService,private statesService:StatesService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	this.id = this.activatedRoute.snapshot.queryParams.id;
	
	this.countryservice.getCountry().subscribe(res => {
		//console.log(res['countries']);
      this.countryList = res['countries'];
    });
    
    this.form = this.fb.group({
      id:[''],
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\-]+$")]],
      country_id:['',[Validators.required]],	  
    });

    this.statesService.getStates(this.id).pipe(first())
    .subscribe(res => {
      let audittype = res.data;
	  
      this.form.patchValue(audittype);
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
		this.statesService.updateData(this.form.value).pipe(first()
      ).subscribe(res => {

          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
			setTimeout(()=>this.router.navigate(['/master/state/list']),this.errorSummary.redirectTime);
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
