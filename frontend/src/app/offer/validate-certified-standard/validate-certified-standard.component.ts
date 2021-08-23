import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { GenerateDetailService } from '@app/services/offer/generate-detail.service';
import { Application } from '@app/models/application/application';
import { UserService } from '@app/services/master/user/user.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import { User } from '@app/models/master/user';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';

import { CountryService } from '@app/services/country.service';
import { Country } from '@app/services/country';

import {Observable} from 'rxjs';
import { first } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-validate-certified-standard',
  templateUrl: './validate-certified-standard.component.html',
  styleUrls: ['./validate-certified-standard.component.scss']
})
export class ValidateCertifiedStandardComponent implements OnInit {

  constructor(private userservice: UserService,private activatedRoute:ActivatedRoute,private offerDetail:GenerateDetailService, 
  private modalService: NgbModal,private enquiryDetail:EnquiryDetailService,private fb:FormBuilder,
  private router:Router,private countryservice: CountryService,private errorSummary: ErrorSummaryService) { }
  id:number;
  error = '';
  success = '';
  loading = false;
  applicationdata:Application;
  panelOpenState = false;
  approvalStatusList = [{id:'1',name:'Accept'},{id:'2',name:'Reject'},{id:'3',name:'More Information'}];
  userList:User[];
  modalss:any;
  sel_user_error='';
  user_id_error='';
  approver_user_id = '';
  
  feesEntries:any=[];
  fee_nameErrors=''; 
  fee_descriptionErrors='';
  amountErrors='';
  noofmandayErrors='';
  
  expensesEntries:any=[];
  expense_nameErrors=''; 
  expense_descriptionErrors='';
  expense_amountErrors='';
      
  form  : any = {};
   
  certification_fee_sub_total=0;
  other_expense_sub_total=0;
  tax_rate=0;
  tax_percentage=0;
  total_fee=0;
  total_payable_amount=0;
  conversion_total_payable_amount=0;
  
  man_day_cost = 0;
  currency_code = '';
  conversion_required_status=0;
  
  currency=''; 
  conversion_rate=0;
  conversion_currency_code='';
     
  model:any = {user_id:'',approver_user_id:'',status:'',comment:''};

  currencyErrors ='';
  conversion_rateErrors = '';
  conversion_currency_codeErrors = '';
  expense_Errors = '';
  standards = [];
  units=[];
  offer_id=0;
  discount=0;
  offer_status=0;
  grand_total_fee=0;
  taxname='';
  unitEntries:any=[];
  discountErrors='';
  conversion_required='';
  countryCodeList:Country[];
  validatecertifiedstandard=[];
  discountcertifiedstandard=[];
  total_manday_cost=0;	
  total_manday=0;
  ngOnInit() {
			
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.offer_id = this.activatedRoute.snapshot.queryParams.offer_id;
		
     //this.validatecertifiedstandard['qtd_1_0_66_4']='1';
	
	this.offerDetail.getApplication(this.id).pipe(first())
    .subscribe(res => {
      this.applicationdata = res;
	  //console.log(this.applicationdata);
	  
	  this.total_manday=0;
	  let tot_manday_cost=0;
	  let di=0;	  
	  this.applicationdata.appunitmanday.forEach(manday_dis=>{
		if(manday_dis.manday_discount.length>0)
		{
			let md=0;
			manday_dis.manday_discount.forEach(existing_certified_std=>{				
				this.validatecertifiedstandard['qtd_'+di+'_'+md+'_'+manday_dis.id+'_'+existing_certified_std.standard_id+'']=''+existing_certified_std.status+'';
				md++;
			});		
		}
		tot_manday_cost+=parseFloat(manday_dis.unit_manday_cost);		
		this.total_manday_cost=tot_manday_cost;	
		
		this.total_manday+=parseFloat(manday_dis.final_manday);
		di++;		
      });	  
    },
    error => {
        this.error = error;
        this.loading = false;
    });		
  } 
  
  
  
  
	//297000---000---708
	
	//1. 19002 + 708 - 3288
	//2. 15714 +
	//3. //044 61318202 - Karthika ---
	//1.55
	//...

	//billing address

	//--925
 
  fnCalculateDiscount(type,val,certified_standard_index,standard_id)
  {
	this.applicationdata.appunitmanday[val].manday_discount[certified_standard_index].status=type;
	
	let tot_discount=0;
	this.applicationdata.appunitmanday[val].manday_discount.forEach(manday_dis=>{
		if(manday_dis.status==1)
		{
			tot_discount = tot_discount + parseFloat(manday_dis.discount);	
		}		
	});	
	
	this.applicationdata.appunitmanday[val].total_discount=tot_discount;
	
	let eligible_dis=tot_discount
	if(tot_discount>this.applicationdata.appunitmanday[val].maximum_discount)
	{
		eligible_dis=this.applicationdata.appunitmanday[val].maximum_discount;	
	}
	
	this.applicationdata.appunitmanday[val].eligible_discount=eligible_dis;
	
	let discountManday=((this.applicationdata.appunitmanday[val].eligible_discount*this.applicationdata.appunitmanday[val].manday)/100);
	let finalManday=this.applicationdata.appunitmanday[val].manday-discountManday;
	
	this.applicationdata.appunitmanday[val].discount_manday=discountManday;
	this.applicationdata.appunitmanday[val].final_manday=finalManday;
	this.applicationdata.appunitmanday[val].unit_manday_cost = (finalManday*this.applicationdata.appunitmanday[val].manday_cost);
	
	let tot_manday_cost=0;
	this.total_manday=0;
	this.applicationdata.appunitmanday.forEach(unit_manday_c=>{
		tot_manday_cost+=parseFloat(unit_manday_c.unit_manday_cost);
		this.total_manday_cost = tot_manday_cost;
		this.total_manday+=parseFloat(unit_manday_c.final_manday);
	});		
  }

  
  onSubmit(){
	
	this.error = '';	

    this.loading = true;

    let postdata = {
      app_id:this.id,
      offer_id:this.offer_id,
	  applicationdata:this.applicationdata
    };
    
    this.offerDetail.updateOffer(postdata)
    .pipe(first())
    .subscribe(res => {

		if(res.status){
          //this.enquiryForm.reset();
          //this.submittedSuccess =1;
          this.success = res.message;           
          setTimeout(() => {
            this.router.navigateByUrl('/offer/offer-generate?id='+this.id+'&offer_id='+this.offer_id); 
          }, 1500);
 
        }else if(res.status == 0){
          //this.submittedError =1;
          this.error = res.msg;
        }else{
          //this.submittedError =1;
          this.error = res;
        }
        this.loading = false;
        
    },
    error => {
        this.error = error;
        this.loading = false;
    });
  }
}