import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { AuthenticationService } from '@app/services/authentication.service';
import { ProductAdditionService } from '@app/services/change-scope/product-addition.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Router } from '@angular/router';

import { first } from 'rxjs/operators';

@Component({
  selector: 'app-request-product-addition',
  templateUrl: './request-product-addition.component.html',
  styleUrls: ['./request-product-addition.component.scss']
})
export class RequestProductAdditionComponent implements OnInit {

  title = 'Product Addition';	
  form : FormGroup;
  loading:any={};
  buttonDisable = false;
  id:number;
  error:any;
  success:any;
  requestdata:any=[];
  appdata:any=[];
  unitlist:any=[];

  descriptionErrors = '';
  userType:number;
  userdetails:any;
  userdecoded:any;
  app_id:any;
  units:any;

  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private additionservice: ProductAdditionService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {
    this.form = this.fb.group({
      app_id:['',[Validators.required]],
      unit_id:['',[Validators.required]],
    });
    this.app_id = this.activatedRoute.snapshot.queryParams.app;
    this.id = this.activatedRoute.snapshot.queryParams.id;

    this.loading.company = true;
    this.additionservice.getAppCompanyData({id:this.id}).pipe(first())
    .subscribe(res => {
      if(res.status)
      {
        this.appdata = res.appdata;
        this.app_id = res.app_id;
        this.units = res.units;
        
        this.oncompanychange(res.app_id,0);
      }else
      {			      
        this.error = {summary:res};
      }
      this.loading.company= false;
     
    },
    error => {
        this.error = error;
        this.loading.company= false;
    });
  }

  getSelectedValue(val)
  {
    return this.unitlist.find(x=> x.id==val).name;
  }

  checkRequestedUnitAddition(value)
  {
    this.loading.button=false;
    this.buttonDisable=false;
    this.unitlist = [];
     this.form.patchValue({
      unit_id:'',
    });
	
	this.oncompanychange(value);
	
	/*
    if(value)
    {
        this.loading.button = true;
        this.buttonDisable=true;
        
        this.additionservice.getRequestedStatus({id:value}).pipe(first())
        .subscribe(res => {
          if(res.status)
          {
            this.buttonDisable=false;
            this.oncompanychange(value);
          }
          else if(res.status == 0)
          {
            this.buttonDisable=true;
            this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
          }
          else
          {           
            this.error = {summary:res};
          }
          this.loading.button = false;
        },
        error => {
              this.error = error;
              this.loading.button = false;
        });
    }
	*/	
  }


  oncompanychange(value,unitreset=1)
  {
    if(unitreset)
    {
      this.form.patchValue({
        unit_id:'',
      });
    }
    this.unitlist = [];

    if(value){
       this.loading.unit = true;
      this.additionservice.getUnitData({id:value}).pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.unitlist = res.unitdata;
          this.app_id = value;
          if(unitreset){
            this.units = [];
          }
          if(this.units && this.units.length >0)
          {
            this.form.patchValue({
              app_id:this.app_id,
              unit_id:this.units
            });
          }
        }else
        {           
          this.error = {summary:res};
        }
        this.loading.unit = false;
      },
      error => {
          this.error = error;
          this.loading.unit = false;
      });
    }
    
  }

  get f() { return this.form.controls; } 

  onSubmit()
  {
    if (this.form.valid) {
		let app_id = this.f.app_id.value;
		let unit_id = this.f.unit_id.value;
		this.loading['button'] = true;


    let reqobj:any = {id:this.id,app_id:app_id,unit_id:unit_id};
    this.additionservice.addProductAdditionData(reqobj)
    .pipe(first())
    .subscribe(res => {

        if(res.status){
          //this.enquiryForm.reset();
          //this.success = {summary:res.message};
          this.buttonDisable = true;           
          setTimeout(() => {
            this.router.navigateByUrl('/change-scope/product-addition/add?id='+res.id+'&app_id='+app_id); 
          }, this.errorSummary.redirectTime);           
        }else{
          this.buttonDisable = false;  
          this.loading['button'] = false;
           
          this.error = {summary:res};
        }
       
    },
    error => {
        this.error = {summary:error};
        this.loading['button'] = false;
        this.buttonDisable = false;  
    });
		//this.router.navigateByUrl('/change-scope/product-addition/add?app_id='+app_id+'&units='+unit_id); 

		/*
		let reqobj = {}
		this.additionservice.updateApplication()
		  .pipe(first())
		  .subscribe(res => {

			  if(res.status){
				//this.enquiryForm.reset();
				this.success = {summary:res.message};
				this.buttonDisable = true;           
				setTimeout(() => {
				  this.router.navigateByUrl('/application/apps/view?id='+this.id); 
				}, this.errorSummary.redirectTime);           
			  }else if(res.status == 0){
				//this.submittedError =1;
				this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.enquiryForm)};
				//this.error = res.msg;
			  }else{
				//this.submittedError =1;
				this.error = {summary:res};
			  }
			  this.loading['button'] = false;
			 
		},
		error => {
			  this.error = {summary:error};
			  this.loading['button'] = false;
		});
		*/

    }else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form); 
      
    }
  }

}

