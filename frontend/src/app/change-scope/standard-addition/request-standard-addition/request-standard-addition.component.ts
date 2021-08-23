import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { AuthenticationService } from '@app/services/authentication.service';
import { StandardAdditionService } from '@app/services/change-scope/standard-addition.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Router } from '@angular/router';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-request-standard-addition',
  templateUrl: './request-standard-addition.component.html',
  styleUrls: ['./request-standard-addition.component.scss']
})
export class RequestStandardAdditionComponent implements OnInit {

  title = 'Standard Addition';	
  form : FormGroup;
  loading:any={};
  buttonDisable = false;
  id:number;
  app_id:number;
  new_app_id:number;
  redirecttype:any;

  error:any;
  success:any;
  requestdata:any=[];
  appdata:any=[];
  standardlist:any=[];

  userType:number;
  userdetails:any;
  userdecoded:any;


  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private additionservice: StandardAdditionService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {
    this.app_id = this.activatedRoute.snapshot.queryParams.app;
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.new_app_id = this.activatedRoute.snapshot.queryParams.new_app_id;
    this.redirecttype = this.activatedRoute.snapshot.queryParams.redirecttype;

    this.form = this.fb.group({
      company_id:['',[Validators.required]],
      standard_id:['',[Validators.required]],
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
      this.loading.company = false;
     
    },
    error => {
        this.error = error;
        this.loading.company = false;
    });
  }

  getSelectedValue(val)
  {
    return this.standardlist.find(x=> x.id==val).name; 
  }

  oncompanychange(value)
  {
    this.form.patchValue({
      standard_id:'',
    });

    this.standardlist = [];

    if(value)
    {
      this.loading.standard = true;
      this.additionservice.getStandardData({id:value}).pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.standardlist = res.stdlist;
        }
        else if(res.status == 0)
        {
          this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
        }
        else
        {			      
          this.error = {summary:res};
        }
        this.loading.standard = false;
      },
      error => {
          this.error = error;
          this.loading.standard = false;
      });
    }
  }

  get f() { return this.form.controls; } 

  
  checkRequestedAddition(value)
  {
    this.loading.button=false;
    this.buttonDisable=false;
    if(value)
    {
      this.loading.button = true;
      this.buttonDisable=true;
      
      this.additionservice.getRequestedStatus({id:value}).pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.oncompanychange(value);
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
    }else{
      this.standardlist = [];
    }
  }
  
  

  onSubmit()
  {
    if (this.form.valid) {
      this.loading['button'] = true;
      this.buttonDisable = true;  

      let expobj:any = this.form.value;
      expobj.id = this.id;
      expobj.app_id = this.app_id;

      this.additionservice.addData(expobj)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
           
            this.success = {summary:res.message};
            this.buttonDisable = true;           
            setTimeout(() => {
              this.router.navigateByUrl('/application/add-request?app_id='+expobj.company_id+'&standard_addition_id='+res.id); 
            }, this.errorSummary.redirectTime);           
          }else{
            //this.submittedError =1;
            this.error = {summary:res};
            this.buttonDisable = false;         
            this.loading['button'] = false;
          }
          
         
      },
      error => {
         this.buttonDisable = false;       
          this.error = {summary:error};
          this.loading['button'] = false;
      });
    }
    else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form); 
      
    }
  }

}
