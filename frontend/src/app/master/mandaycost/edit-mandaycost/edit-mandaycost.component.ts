import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { MandaycostService } from '@app/services/master/mandaycost/mandaycost.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import { CountryService } from '@app/services/country.service';
import { Country } from '@app/services/country';

@Component({
  selector: 'app-edit-mandaycost',
  templateUrl: '../../mandaycost/add-mandaycost/add-mandaycost.component.html',
  styleUrls: ['./edit-mandaycost.component.scss']
})
export class EditMandaycostComponent implements OnInit {

  title = 'Edit Man Day Cost';
  btnLabel = 'Update';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  success:any;
  //audittype:Audittype;
  country_idErrors = '';
  
  countryList:Country[];
  
  taxEntries:any=[];
  tax_nameErrors=''; 
  tax_percentageErrors='';
  tax_forErrors='';
  
  formData:FormData = new FormData();
  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private countryservice: CountryService,private mandaycostService: MandaycostService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	this.id = this.activatedRoute.snapshot.queryParams.id;
    
	this.countryservice.getCountry().subscribe(res => {
		this.countryList = res['countries'];
    });
	
    this.form = this.fb.group({
      id:[''],
      country_id:['',[Validators.required]],      
      man_day_cost:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]],
      currency_code:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[a-zA-Z]+$")]],
      tax_name:[''],
      tax_percentage:['',[Validators.pattern("^[0-9]+(.[0-9]{0,2})?$"),Validators.max(100)]],
	  tax_for:[''],	
	  admin_fee:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]],
	  client_logo_approval_fee:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]]
    });

    this.mandaycostService.getMandaycost(this.id).pipe(first())
    .subscribe(res => {
      let audittype = res.data;
	  
	  this.taxEntries = res.tax;
	  
      this.form.patchValue(audittype);
    },
    error => {
        this.error = error;
        this.loading = false;
    });
  }
  
  get f() { return this.form.controls; }
    
  removeTax(index:number) {
	this.updateStatus=false;  
    if(index != -1)
      this.taxEntries.splice(index,1);
  
	this.taxIndex=null;
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
	  tax_for: ''
    });
	this.taxIndex=null;
	this.updateStatus=false;
  }
  
  setTax()
  {
	this.f.tax_name.setValidators([Validators.required]);
    this.f.tax_percentage.setValidators([Validators.required,Validators.pattern("^[0-9]+(.[0-9]{0,2})?$"),Validators.max(100)]);
	this.f.tax_for.setValidators([Validators.required]);

    this.f.tax_name.updateValueAndValidity();
    this.f.tax_percentage.updateValueAndValidity();
	this.f.tax_for.updateValueAndValidity();

    this.touchTax();
  }
    
  //taxStatus=true;
  taxIndex=null;
  addTax(){
	this.updateStatus=false;      
	this.setTax();
	
	let tax_name = this.form.get('tax_name').value;
	let tax_percentage = this.form.get('tax_percentage').value;
	let tax_for = this.form.get('tax_for').value;
	
	if(tax_name=='' || tax_percentage=='' || this.f.tax_percentage.errors || tax_for==''){
      return false;
    }
	    
    //let entry= this.taxEntries.find(s => s.id ==  productId);
    let expobject:any=[];
    //expobject["id"] = selproduct.id;
    expobject["tax_name"] = tax_name;
    expobject["tax_percentage"] = tax_percentage;
	expobject["tax_for"] = tax_for;
	let tax_label='Same State';
	if(tax_for==2)
	{
		tax_label='Other State';
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
	  tax_for_label: '',
    });
	
	this.resetTax();
	
	this.taxIndex=null;
  }
  
  updateStatus=false;  
  editTax(index:number){
	this.updateStatus=true;    
   // let prd= this.taxEntries.find(s => s.id ==  productId);
   this.taxIndex= index;
	let qual = this.taxEntries[index];
    this.form.patchValue({
      tax_name: qual.tax_name,
	  tax_percentage: qual.tax_percentage,
	  tax_for: qual.tax_for,
	  tax_for_label: qual.tax_for_label,	  
    });
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

  onSubmit(){
	  
	this.checkTaxDetails();	
    
    if (this.form.valid) {
        this.loading = true;    

        let taxdatas = [];
        this.taxEntries.forEach((val)=>{
          taxdatas.push({tax_name:val.tax_name,tax_percentage:val.tax_percentage,tax_for:val.tax_for})
        });
	  
	  	  
	    let formvalue = this.form.value;
	    formvalue.tax = [];      	  
        formvalue.tax = taxdatas;
      	  
	    this.formData.append('formvalues',JSON.stringify(this.formData));
	  
		this.mandaycostService.updateData(this.form.value).pipe(first()
      ).subscribe(res => {

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
      //console.log('sdfsdfdf');
    } else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form); 
      
    }
  }

}