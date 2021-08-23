import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { MandaycostService } from '@app/services/master/mandaycost/mandaycost.service';
import { CountryService } from '@app/services/country.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';
import { Country } from '@app/services/country';

@Component({
  selector: 'app-add-mandaycost',
  templateUrl: './add-mandaycost.component.html',
  styleUrls: ['./add-mandaycost.component.scss']
})
export class AddMandaycostComponent implements OnInit {

  title = 'Add Man Day Cost';
  btnLabel = 'Save';
  countryList:Country[];
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  taxEntries:any=[];
  tax_nameErrors=''; 
  tax_percentageErrors='';
  tax_forErrors='';
  country_idErrors = '';
  
  formData:FormData = new FormData();
  
  constructor(private router: Router,private fb:FormBuilder,private countryservice: CountryService,private mandaycostService:MandaycostService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	
	this.countryservice.getCountry().subscribe(res => {
		this.countryList = res['countries'];
    });
	
	this.form = this.fb.group({
      country_id:['',[Validators.required]],      
      //man_day_cost:['',[Validators.required,Validators.pattern('/^\d*\.?\d{0,2}$/g')]],
	  man_day_cost:['',[Validators.required,Validators.maxLength(10),Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$')]], 
      currency_code:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[a-zA-Z]+$")]],
      tax_name:[''],
      tax_percentage:['',[Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.max(100)]],
	  tax_for:[''],	  
	  admin_fee:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]],
	  client_logo_approval_fee:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]]
    });
  }

  get f() { return this.form.controls; }  
  
  removeExperience(index:number) {
    //let index= this.taxEntries.findIndex(s => s.id ==  productId);
    if(index != -1)
      this.taxEntries.splice(index,1);
  }
  
  touchTax(){
    this.f.tax_name.markAsTouched();
    this.f.tax_percentage.markAsTouched();
	this.f.tax_for.markAsTouched();	
  }
  
  resetTax(){
	this.f.tax_name.setValidators([]);
    this.f.tax_percentage.setValidators([]);
	this.f.tax_for.setValidators([]);	

    this.f.tax_name.updateValueAndValidity();
    this.f.tax_percentage.updateValueAndValidity();
	this.f.tax_for.updateValueAndValidity();
	
	this.form.patchValue({
      tax_name: '',
	  tax_percentage: '',
	  tax_for: '',
	  tax_for_label: ''
    });
	
	this.taxIndex=null;
	this.updateStatus=false;
  }
  
  setTax()
  {
	this.f.tax_name.setValidators([Validators.required]);
    this.f.tax_percentage.setValidators([Validators.required,Validators.pattern(/^\d*\.?\d{0,2}$/g),Validators.max(100)]);
	this.f.tax_for.setValidators([Validators.required]);

    this.f.tax_name.updateValueAndValidity();
    this.f.tax_percentage.updateValueAndValidity();
	this.f.tax_for.updateValueAndValidity();

    this.touchTax();
  }
  
  checkTaxDetails()
  {
	let tax_name = this.form.get('tax_name').value;
	let tax_percentage = this.form.get('tax_percentage').value;
	let tax_for = this.form.get('tax_for').value;
	
	if(tax_name=='' && tax_percentage=='' && tax_for=='')
	{
		this.resetTax();	
	}else{
		this.setTax();
	}		
  }
  
  //taxStatus=true;
  taxIndex=null;
  addTax(){
	
	this.setTax();
	
	let tax_name = this.form.get('tax_name').value;
	let tax_percentage = this.form.get('tax_percentage').value;
	let tax_for = this.form.get('tax_for').value;
	
	//console.log(this.f.tax_percentage.errors.pattern);
	//console.log(this.f.tax_percentage.errors);
	//return false;
	if(tax_name=='' || tax_percentage=='' || this.f.tax_percentage.errors || tax_for==''){
      return false;
    }
			    
    let expobject:any=[];
    expobject["tax_name"] = tax_name;
    expobject["tax_percentage"] = tax_percentage;
	expobject["tax_for"] = tax_for;
	let tax_label='Same State';
	if(tax_for==2)
	{
		let tax_label='Other State';
	}
	expobject["tax_for_label"] = tax_label;
    	  
	if(this.taxIndex!==null){
		this.taxEntries[this.taxIndex] = expobject;
	}else{
		this.taxEntries.push(expobject);
	}
	
    this.form.patchValue({
      tax_name: '',
	  tax_percentage: '',
	  tax_for: '',
	  tax_label: ''
    });
	
	this.resetTax();
	
	this.taxIndex=null;
    
  }
  
  updateStatus=false;
  editTax(index:number){
	this.updateStatus=true;  
    this.taxIndex= index;
	let qual = this.taxEntries[index];
    this.form.patchValue({
      tax_name: qual.tax_name,
	  tax_percentage: qual.tax_percentage,
	  tax_for: qual.tax_for,
	  tax_label: qual.tax_label,	  
    });
  }
  
  removeTax(index){
	this.updateStatus=false;  
	if(index != -1)
      this.taxEntries.splice(index,1);
  
    this.taxIndex=null;
  }
  
  onSubmit(){
    
	this.checkTaxDetails();
	
    if (this.form.valid) {
      
     // console.log(this.form.value);
      this.loading = true;
	  
	  let taxdatas = [];
      this.taxEntries.forEach((val)=>{
        taxdatas.push({tax_name:val.tax_name,tax_percentage:val.tax_percentage,tax_for:val.tax_for})
      });
	  
	  	  
	  let formvalue = this.form.value;
	  formvalue.tax = [];      	  
      formvalue.tax = taxdatas;
      	  
	  this.formData.append('formvalues',JSON.stringify(this.formData));
      
      this.mandaycostService.addData(this.form.value)
      .pipe(
        first()        
      )
      .subscribe(res => {
        //console.log(res);
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
            setTimeout(()=>this.router.navigate(['/master/mandaycost/list']),this.errorSummary.redirectTime);            
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
