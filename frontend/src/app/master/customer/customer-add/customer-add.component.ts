import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { CountryService } from '@app/services/country.service';
import { UserService } from '@app/services/master/user/user.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Router } from '@angular/router';
import { Country } from '@app/services/country';
import { State } from '@app/services/state';


import { first } from 'rxjs/operators';


@Component({
  selector: 'app-customer-add',
  templateUrl: './customer-add.component.html',
  styleUrls: ['./customer-add.component.scss']
})
export class CustomerAddComponent implements OnInit {

  title = 'Add Customer';
  btnLabel = 'Save';
  countryList:Country[];
  stateList:State[];
  companyStateList:State[];
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;

  constructor(private router: Router,private fb:FormBuilder,private countryservice: CountryService,private userService:UserService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() { 
    this.countryservice.getCountry().subscribe(res => {
      this.countryList = res['countries'];
    });

    this.form = this.fb.group({
		
	  /*
      first_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],
      last_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],
      email:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.email,Validators.maxLength(255)]],
      telephone:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.pattern("^[0-9\-]*$"), Validators.minLength(8), Validators.maxLength(15)]],
	  country_id:['',[Validators.required]],
      state_id:['',[Validators.required]],
	  */
	  
	  company_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
	  contact_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      //contact_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],	  
      company_email:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.email,Validators.maxLength(255)]],
      company_telephone:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.pattern("^[0-9\-\+]*$"), Validators.minLength(8), Validators.maxLength(15)]],
	  
     //company_website:['',[Validators.pattern("/(^|\s)((https?:\/\/)?[\w-]+(\.[\w-]+)+\.?(:\d+)?(\/\S*)?)/gi")]],
	 
	  company_website:['',[Validators.pattern("^(http[s]?:\\/\\/){0,1}(www\\.){0,1}[a-zA-Z0-9\\.\\-]+\\.[a-zA-Z]{2,5}[\\.]{0,1}$"),this.errorSummary.noWhitespaceValidator]],
      company_address1:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
      company_address2:['',[this.errorSummary.noWhitespaceValidator]],
      company_city :['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.pattern("^[a-zA-Z .]+$"),Validators.maxLength(255)]],
      company_zipcode:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.pattern("^[a-zA-Z0-9]+$"),Validators.maxLength(15)]],
      company_country_id:['',[Validators.required]],
      company_state_id:['',[Validators.required]],
      //number_of_employees:['',[Validators.pattern("^[0-9]*$")]],
      number_of_sites:['',[Validators.pattern("^[0-9]*$"),Validators.maxLength(10),Validators.min(1)]],
      customer_number:['',[Validators.pattern("^[0-9]*$"),Validators.maxLength(11)]],
      
      description:['',[this.errorSummary.noWhitespaceValidator]],
      other_information:['']
    });
	
	

  }

  getStateList(id:number,stateUpdate){
    if(stateUpdate=='company_state_id'){
		this.companyStateList = [];  
		this.form.patchValue({company_state_id:''});
	}else{
		this.stateList = [];
		this.form.patchValue({state_id:''});			
	}
	
	if(id>0)
	{
		this.countryservice.getStates(id).subscribe(res => {
			if(res['status'])
			{
			  if(stateUpdate=='company_state_id'){
				this.companyStateList = res['data'];  
				this.form.patchValue({company_state_id:''});
			  }else{
				this.stateList = res['data'];
				this.form.patchValue({state_id:''});
			  }
			}  
		});
	}	
  }
  
  get company_country_id() { return this.form.get('company_country_id'); }
  get f() { return this.form.controls; }

  onSubmit(){
    //console.log(this.form.valid);
    //console.log(this.form.value);
    if (this.form.valid) {
      
      
      this.loading = true;
      
      this.userService.addCustomer(this.form.value)
      .pipe(
        first()        
      )
      .subscribe(res => {
        //console.log(res);
          if(res.status){			
            this.success = {summary:res.message};
			this.buttonDisable = true;
			setTimeout(()=>this.router.navigate(['/master/customer/list']),this.errorSummary.redirectTime);
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
