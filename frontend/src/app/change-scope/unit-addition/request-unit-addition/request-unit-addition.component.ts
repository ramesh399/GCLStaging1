import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { AuthenticationService } from '@app/services/authentication.service';
import { UnitAdditionService } from '@app/services/change-scope/unit-addition.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Router } from '@angular/router';

import { first } from 'rxjs/operators';

@Component({
  selector: 'app-request-unit-addition',
  templateUrl: './request-unit-addition.component.html',
  styleUrls: ['./request-unit-addition.component.scss']
})
export class RequestUnitAdditionComponent implements OnInit {

  title = 'Unit Addition';	
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


  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private additionservice: UnitAdditionService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {
    this.form = this.fb.group({
      app_id:['',[Validators.required]],
      //unit_id:['',[Validators.required]],
    });

    this.loading.company = true;
    this.additionservice.getAppData().pipe(first())
    .subscribe(res => {
      if(res.status)
      {
        this.appdata = res.appdata;
        
      }
      else if(res.status == 0)
      {
        this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
      }
      else
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
  
  //this.loading.button = false;
  checkRequestedUnitAddition(value)
  {
	this.loading.button=false;
	this.buttonDisable=false;
	if(value)
    {
		this.loading.button = true;
		this.buttonDisable=true;
		
		this.additionservice.getRequestedUnitStatus({id:value}).pipe(first())
		.subscribe(res => {
			if(res.status)
			{
				this.buttonDisable=false;
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
  }
  
  getRequestedUnitStatus

  oncompanychange(value)
  {
    this.form.patchValue({
      unit_id:'',
    });
    this.unitlist = [];
    if(value)
    {
      this.loading.unit = true;
      this.additionservice.getUnitData({id:value}).pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.unitlist = res.unitdata;
        }
        else if(res.status == 0)
        {
          this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
        }
        else
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
		this.loading['button'] = true;
		this.router.navigateByUrl('/change-scope/unit-addition/add?redirecttype=unit&app='+app_id);
    } else {
		this.error = {summary:this.errorSummary.errorSummaryText};
		this.errorSummary.validateAllFormFields(this.form);       
    }
  }

}


