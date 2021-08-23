import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { AuthenticationService } from '@app/services/authentication.service';
import { ProcessAdditionService } from '@app/services/change-scope/process-addition.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Router } from '@angular/router';

import { first } from 'rxjs/operators';

@Component({
  selector: 'app-process-addition',
  templateUrl: './process-addition.component.html',
  styleUrls: ['./process-addition.component.scss']
})
export class ProcessAdditionComponent implements OnInit {

  title = 'Process Addition';	
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
  redirecttype:any;
  app_id:any;
  units:any;

  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private additionservice: ProcessAdditionService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {
    this.app_id = this.activatedRoute.snapshot.queryParams.app;
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.redirecttype = this.activatedRoute.snapshot.queryParams.redirecttype;


    this.form = this.fb.group({
      app_id:['',[Validators.required]],
      unit_id:['',[Validators.required]],
    });
    this.loading.company = true;
    this.additionservice.getAppData({id:this.id}).pipe(first())
    .subscribe(res => {
      if(res.status)
      {
        this.appdata = res.appdata;
        this.app_id = res.app_id;
        this.units = res.units;
        /*this.form.patchValue({
          app_id:res.app_id,
          unit_id:res.units
        });
        */
        this.oncompanychange(res.app_id,0);
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

  checkRequestedUnitAddition(value)
  {
    this.loading.button=false;
    this.buttonDisable=false;
    this.unitlist = [];
     this.form.patchValue({
      unit_id:'',
    });
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
      let unit_id = this.f.unit_id.value;
      this.loading['button'] = true;
      this.buttonDisable = true;      
        /*this.router.navigateByUrl('/change-scope/process-addition/add?app='+app_id+'&units='+unit_id); 
         */
     
      let reqobj:any = {id:this.id,app_id:app_id,unit_id:unit_id};
      this.additionservice.addProcessAdditionData(reqobj)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            //this.enquiryForm.reset();
            //this.success = {summary:res.message};
            this.buttonDisable = true;           
            setTimeout(() => {
              this.router.navigateByUrl('/change-scope/process-addition/add?id='+res.id+'&app='+app_id+'&redirecttype=process'); 
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
