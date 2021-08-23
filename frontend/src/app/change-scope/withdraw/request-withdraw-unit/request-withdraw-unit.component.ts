import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { AuthenticationService } from '@app/services/authentication.service';
import { WithdrawUnitService } from '@app/services/change-scope/withdraw-unit.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Router } from '@angular/router';

import { first } from 'rxjs/operators';

@Component({
  selector: 'app-request-withdraw-unit',
  templateUrl: './request-withdraw-unit.component.html',
  styleUrls: ['./request-withdraw-unit.component.scss']
})
export class RequestWithdrawUnitComponent implements OnInit {

  title = 'Withdraw of Unit';	
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

  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private withdrawservice: WithdrawUnitService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {
    this.form = this.fb.group({
      app_id:['',[Validators.required]],
      unit_id:['',[Validators.required]],
	  reason:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],	  
    });
    this.app_id = this.activatedRoute.snapshot.queryParams.app;
    this.id = this.activatedRoute.snapshot.queryParams.id;

    this.loading.company = true;
    this.withdrawservice.getAppCompanyData({id:this.id}).pipe(first())
    .subscribe(res => {
      if(res.status)
      {
        this.appdata = res.appdata;
        this.app_id = res.app_id;
        this.units = res.units;
		
		this.form.patchValue({
			reason:res.reason
		});
        
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
      this.withdrawservice.getUnitData({id:value}).pipe(first())
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

  onSubmit(type)
  {
	if (this.form.valid) 
	{
		let app_id = this.f.app_id.value;
		let unit_id = this.f.unit_id.value;
		let reason = this.f.reason.value;
		this.loading['button'] = true;

		let reqobj:any = {type:type,id:this.id,app_id:app_id,unit_id:unit_id,reason:reason};
		this.withdrawservice.addWithdrawUnitData(reqobj)
		.pipe(first())
		.subscribe(res => {

			if(res.status){
			  //this.enquiryForm.reset();
			  //this.success = {summary:res.message};
			  this.buttonDisable = true;           
			  setTimeout(() => {
				//this.router.navigateByUrl('/change-scope/withdraw/add?id='+res.id+'&app_id='+app_id); 
				this.router.navigateByUrl('/change-scope/withdraw-unit/list'); 
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

    }else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form); 
      
    }
  }

}