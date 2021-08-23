import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';

import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import { AuthenticationService } from '@app/services';
import {Observable,Subject} from 'rxjs';
import { first, debounceTime, distinctUntilChanged, map,tap } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {saveAs} from 'file-saver';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Process } from '@app/models/master/process';
import { ProcessService } from '@app/services/master/process/process.service';
import { ProductAdditionService } from '@app/services/change-scope/product-addition.service';
import { ChangeAddressService } from '@app/services/change-scope/change-address.service';
import { Country } from '@app/services/country';
import { State } from '@app/services/state';
import { CountryService } from '@app/services/country.service';

@Component({
  selector: 'app-edit-change-address',
  templateUrl: '../add-change-address/add-change-address.component.html',
  styleUrls: ['./edit-change-address.component.scss']
})
export class EditChangeAddressComponent implements OnInit {

  title = 'Change of Address';
  form : FormGroup;
  loading:any={};
  buttonDisable = false;
  showform = false; 
  unitypename = '';
  unit_type:number;
  id:number;
  app_id:number;
  error:any;
  success:any;
  requestdata:any=[];
  appdata:any=[];
  unitlist:any=[];
  unitdata:any=[];
  addressdata:any=[];
  units:any;

  countryList:Country[];
  stateList:State[];

  userType:number;
  userdetails:any;
  arrEnumStatus:any[];
  salutationList = [{"id":1,"name":"Mr"},{"id":2,"name":"Mrs"},{"id":3,"name":"Ms"},{"id":4,"name":"Dr"}];

  constructor(private additionservice: ProductAdditionService,private addressservice: ChangeAddressService,private fb:FormBuilder, private countryservice: CountryService,private modalService: NgbModal,private router:Router,private authservice:AuthenticationService,public errorSummary: ErrorSummaryService, private userservice: UserService,private activatedRoute:ActivatedRoute) { }

  ngOnInit() 
  {
    this.form = this.fb.group({
		app_id:['',[Validators.required]],
		unit_id:['',[Validators.required]],
		unit_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
		unit_address:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],		
		unit_zipcode:['',[Validators.required,Validators.pattern("^[a-zA-Z0-9]+$"),Validators.maxLength(15)]],		
		unit_country_id:['',[Validators.required]],
		unit_state_id:['',[Validators.required]],
		unit_city:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
		salutation:['',[Validators.required]],
		title:[''],
		first_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],
		last_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],
		job_title:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],
		company_telephone:['',[Validators.required,Validators.pattern("^[0-9\-]*$"), Validators.minLength(8), Validators.maxLength(15)]],
		company_email:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.email,Validators.maxLength(255)]],
    });
    this.app_id = this.activatedRoute.snapshot.queryParams.app;
    this.id = this.activatedRoute.snapshot.queryParams.id;

    this.additionservice.getAppCompanyData({id:this.id}).pipe(first())
    .subscribe(res => {
      if(res.status)
      {
        this.appdata = res.appdata;
        this.app_id = res.app_id;
        this.units = res.units;
        
        //this.oncompanychange(res.app_id,0);
      }else
      {			      
        this.error = {summary:res};
      }
      
     
    },
    error => {
        this.error = error;
    });


    this.addressservice.getAddress({id:this.id}).pipe(
    tap(res=>{
      this.addressdata = res.data;
      this.unit_type = this.addressdata.unit_type;
      this.loading.unit = true;
      this.additionservice.getUnitData({id:this.addressdata.app_id}).pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.unitlist = res.unitdata;
          this.app_id = this.addressdata.app_id;
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

      this.countryservice.getStates(this.addressdata.country_id).pipe(first()).subscribe(res => {
        this.stateList = res['data'];
      });

    }),first())
    .subscribe(res => {
      this.form.patchValue({
        app_id:this.addressdata.app_id,
        unit_id:this.addressdata.unit_id,
        unit_name:this.addressdata.name,
        unit_address:this.addressdata.address,
        unit_zipcode:this.addressdata.zipcode,
        unit_city:this.addressdata.city,
        unit_country_id:this.addressdata.country_id,
        unit_state_id:this.addressdata.state_id,
		salutation:this.addressdata.salutation,
		title:this.addressdata.title,
		first_name:this.addressdata.first_name,
		last_name:this.addressdata.last_name,
		job_title:this.addressdata.job_title,
		company_telephone:this.addressdata.telephone,
		company_email:this.addressdata.email_address
      });
      this.showform = true;
    },
    error => {
        this.error = error;
        this.loading = false;
    });

    this.countryservice.getCountry().pipe(first()).subscribe(res => {
      this.countryList = res['countries'];
    });
  }

  get f() { return this.form.controls; }

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
    this.showform = false;

    if(value){
      this.addressservice.getUnitList({id:value}).pipe(first())
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
      },
      error => {
          this.error = error;
      });
    }
    
  }

  getUnitDetails(value)
  { 
    if(value)
    { 
      this.addressservice.getUnitData({id:value}).pipe(
        tap(res=>{
          this.unitdata = res.data;
          this.countryservice.getStates(this.unitdata.unit_country_id).pipe(first()).subscribe(res => {
            this.stateList = res['data'];
          });
        }),first()
      )
      .subscribe(res => {
          this.showform = true;
          this.unit_type = this.unitdata.unit_type;
          this.form.patchValue({
            unit_name:this.unitdata.unit_name,
            unit_address:this.unitdata.unit_address,
            unit_zipcode:this.unitdata.unit_zipcode,
            unit_city:this.unitdata.unit_city,
            unit_country_id:this.unitdata.unit_country_id,
            unit_state_id:this.unitdata.unit_state_id,
			salutation:this.unitdata.salutation,
			title:this.unitdata.title,
			first_name:this.unitdata.first_name,
			last_name:this.unitdata.last_name,
			job_title:this.unitdata.job_title,
			company_telephone:this.unitdata.telephone,
			company_email:this.unitdata.email_address
          });
      },
      error => {
          this.error = error;
      });
    }
    else
    {
      this.showform = false; 
    }
  }

  getStateList(id:number,stateid='')
  {  
    this.stateList = [];
	  this.form.patchValue({state_id:''});
    this.loading['state'] = 1;
        
    this.countryservice.getStates(id).pipe(first()).subscribe(res => {       
        this.stateList = res['data'];
        this.loading['state'] = 0;       
    });    
  }

  onSubmit(type)
  {
    this.f.app_id.markAsTouched();
    this.f.unit_id.markAsTouched();
    this.f.unit_name.markAsTouched();
    this.f.unit_address.markAsTouched();
    this.f.unit_zipcode.markAsTouched();
    this.f.unit_city.markAsTouched();
    this.f.unit_country_id.markAsTouched();
    this.f.unit_state_id.markAsTouched();
	
	this.f.salutation.markAsTouched();	
	this.f.first_name.markAsTouched();
	this.f.last_name.markAsTouched();
	this.f.job_title.markAsTouched();
	this.f.company_telephone.markAsTouched();
	this.f.company_email.markAsTouched();
    
    if(this.form.valid)
    {
      this.buttonDisable = true;     
      this.loading.button = true;
      
      let app_id = this.form.get('app_id').value;
      let unit_id = this.form.get('unit_id').value;
      let unit_name = this.form.get('unit_name').value;
      let unit_address = this.form.get('unit_address').value;
      let unit_zipcode = this.form.get('unit_zipcode').value;
      let unit_city = this.form.get('unit_city').value;
      let unit_country_id = this.form.get('unit_country_id').value;
      let unit_state_id = this.form.get('unit_state_id').value;
	  
	  let salutation = this.form.get('salutation').value;
	  let first_name = this.form.get('first_name').value;
	  let last_name = this.form.get('last_name').value;
	  let job_title = this.form.get('job_title').value;
	  let telephone = this.form.get('company_telephone').value;
	  let email_address = this.form.get('company_email').value;

      let expobject:any={id:this.id,type:type,app_id:app_id,unit_id:unit_id,unit_type:this.unit_type,unit_name:unit_name,unit_address:unit_address,unit_zipcode:unit_zipcode,unit_city:unit_city,unit_country_id:unit_country_id,unit_state_id:unit_state_id,salutation:salutation,first_name:first_name,last_name:last_name,job_title:job_title,telephone:telephone,email_address:email_address};

      this.addressservice.addAddressData(expobject)
        .pipe(first())
        .subscribe(res => {

            if(res.status){
              this.success = {summary:res.message};
              setTimeout(() => {
                if(type=='2'){
                  this.router.navigateByUrl('/application/apps/view?id='+res.new_app_id);
                }else{
                  this.router.navigateByUrl('/change-scope/change-address/list');
                }                
              }, this.errorSummary.redirectTime);
            }else{
              this.buttonDisable = false;
              this.error = {summary:res};
              
            }
            this.loading.button = false;
          
        },
        error => {
            this.error = {summary:error};
            this.loading.button = false;
            this.buttonDisable = false;
        });
    }
  }

}
