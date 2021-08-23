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
  selector: 'app-add-franchise',
  templateUrl: './add-franchise.component.html',
  styleUrls: ['./add-franchise.component.scss']
})
export class AddFranchiseComponent implements OnInit {

  title = 'Add OSS';
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
      telephone:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.pattern("^[0-9\-\+]*$"), Validators.minLength(8), Validators.maxLength(15)]],	  
	  country_id:['',[Validators.required]],
      state_id:['',[Validators.required]],	
	  */	  
      company_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
	  contact_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      //contact_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],	  
      company_email:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.email,Validators.maxLength(255)]],
      company_telephone:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.pattern("^[0-9\-\+]*$"), Validators.minLength(8), Validators.maxLength(15)]],
      //company_website:[''],
	  company_website:['',[Validators.pattern("^(http[s]?:\\/\\/){0,1}(www\\.){0,1}[a-zA-Z0-9\\.\\-]+\\.[a-zA-Z]{2,5}[\\.]{0,1}$"),this.errorSummary.noWhitespaceValidator]],
      company_address1:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
      company_address2:['',[this.errorSummary.noWhitespaceValidator]],
      company_city :['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.pattern("^[a-zA-Z .]+$"),Validators.maxLength(255)]],
      company_zipcode:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(15)]],
      company_country_id:['',[Validators.required]],
      company_state_id:['',[Validators.required]],
	  osp_details:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
      payment_label:['',[this.errorSummary.noWhitespaceValidator]],
      payment_content:['',[this.errorSummary.noWhitespaceValidator]], 
	  //osp_number:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
	  osp_number:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.pattern("^[0-9]*$"),Validators.maxLength(10)]],
      //number_of_employees:[''],
      //number_of_sites:[''],
      //content:[''],
      //other_information:['']
	  mobile:[''],
	  gst_no:[''],
    });	
	
	//this.first_name.errors
	//this.form.controls['first_name'].setErrors({'incorrect': true});

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
  
  //get company_country_id() { return this.form.get('company_country_id'); }
  
  get f() { return this.form.controls; }

  touchPaymentDetailform(){
    this.f.payment_label.markAsTouched();
    this.f.payment_content.markAsTouched();
  }

  paymentdatas:any=[];
  addDetail()
  {
    this.f.payment_label.setValidators([Validators.required,Validators.maxLength(255)]);
    this.f.payment_content.setValidators([Validators.required]);

    this.f.payment_label.updateValueAndValidity();
    this.f.payment_content.updateValueAndValidity();

    this.touchPaymentDetailform();

    let payment_label = this.form.get('payment_label').value;
    let payment_content = this.form.get('payment_content').value;

    if(payment_label == '' || payment_content == '' ){
      return false;
    }

    let expobject:any;
    expobject = {
      "payment_label":payment_label,
      "payment_content":payment_content
    }

    if(this.paymentIndex!=null && this.paymentIndex!=undefined && this.paymentIndex>=0)
    {
      this.paymentdatas[this.paymentIndex] = expobject;
    }
    else
    {
      this.paymentdatas.push(expobject);
    }
    
    this.form.patchValue({
      payment_label: '',
      payment_content:''
    });

    this.f.payment_label.setValidators([]);
    this.f.payment_content.setValidators([]);

    this.f.payment_label.updateValueAndValidity();
    this.f.payment_content.updateValueAndValidity();
    this.editStatus=false;
    this.paymentIndex = null;
  }

  editStatus=false;
  paymentIndex:number;
  editPayment(index:number)
  {
    this.editStatus=true;
    this.paymentIndex = index;
    let qual = this.paymentdatas[index];
    
    this.form.patchValue({
      payment_label: qual.payment_label,
      payment_content: qual.payment_content
    });
  }

  removePayment(index:number) {
    if(index != -1)
      this.paymentdatas.splice(index,1);
  }

  resetDetailform()
  {
    this.editStatus=false;
    this.paymentIndex = null;
    this.form.patchValue({
      payment_label: '',
      payment_content:''
    });
  }
    
  onSubmit()
  {
   
    if (this.form.valid) 
    {
      this.loading = true;

      let paymentvals=[];
      this.paymentdatas.forEach(val=>{
        paymentvals.push({payment_label:val.payment_label,payment_content:val.payment_content}); 
      })

      let formvalue = this.form.value;
      formvalue.payment_details = paymentvals;
      
      this.userService.addFranchise(formvalue)
      .pipe(
        first()        
      )
      .subscribe(res => {
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
            setTimeout(()=>this.router.navigate(['/master/franchise/list']),this.errorSummary.redirectTime);
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
