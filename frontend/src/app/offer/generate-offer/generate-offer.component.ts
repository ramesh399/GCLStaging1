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
  selector: 'app-generate-offer',
  templateUrl: './generate-offer.component.html',
  styleUrls: ['./generate-offer.component.scss']
})
export class GenerateOfferComponent implements OnInit {
  manday: any[];

  constructor(private userservice: UserService,  private generateDetail:GenerateDetailService, private activatedRoute:ActivatedRoute,private offerDetail:GenerateDetailService, 
  private modalService: NgbModal,private enquiryDetail:EnquiryDetailService,private fb:FormBuilder,
  private router:Router,private countryservice: CountryService,private errorSummary: ErrorSummaryService) { }
  id:number;
  error:any;
  success:any;
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
      
  form : FormGroup;
  
  certification_fee_sub_total=0;
  other_expense_sub_total=0;
  tax_rate=0;
  tax_percentage=0;
  total_fee=0;
  total_payable_amount=0;
  conversion_total_payable_amount=0;
  
  man_day_cost = 0;
  currency_code = '';
  conversion_required_status=1;
  
  currency=''; 
  conversion_rate:any;
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
  appunitmanday:any;

  form_template:any;
  myFormGroup:FormGroup;
  buttonDisable = false;
  actualmanday:any;
  ossEditStatus=false;

  ngOnInit() {
	
	  this.countryservice.getCountryCode().subscribe(res => {
      this.countryCodeList = res['country_code_list'];
    });
	
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.offer_id = this.activatedRoute.snapshot.queryParams.offer_id;
    
    

    this.form = this.fb.group({
      currency:['USD'], 
      conversion_rate:['',[Validators.required,Validators.min(0.01),Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$')]],
      conversion_currency_code:['',[Validators.required]],
      fee_name:[''],
      fee_description:[''],
      //noofmanday:[''],
      amount:[''],
      expense_name:[''],
      expense_description:[''],
      expense_amount:[''],
      discount:[''],
      conversion_required:[''],
      entry_type:[''],
      type:['']
    });
    
    this.generateDetail.getOffer({id:this.id,offer_id:this.offer_id}).pipe(first())
    .subscribe(mres => {
		this.manday = mres.manday;
    this.offerDetail.getApplication(this.id).pipe(first())
      .subscribe(res => {
        this.applicationdata = res;
      //console.log(this.applicationdata);
      
        if(this.applicationdata && (this.applicationdata.offer_status==this.applicationdata.offerenumstatus['waiting-for-oss-approval'] || this.applicationdata.offer_status==this.applicationdata.offerenumstatus['re-initiated-to-oss']))
        {
          this.ossEditStatus=true;
        }
        
        
        this.feesEntries = res.fees;
        this.expensesEntries = res.other_expenses;
        this.currency_code = res.offer_currency_code;
        this.tax_percentage = res.tax_percentage;  
        this.standards.push(res.standards);
        this.currency=res.offer_currency_code;
        this.discount=res.discount?res.discount:0;
        this.offer_status=res.offer_status;
        //this.conversion_required_status=res.conversion_required_status;
        this.conversion_required_status=1;
        this.appunitmanday = res.appunitmanday;
         
  
        let group:any;
        let actualmanday = 0;
        this.appunitmanday.forEach(manday=>{
          //group['manday_'+manday.id]=new FormControl('');  
           manday.final_manday_withtrans = 0
           manday.translator_required = manday.translator_required? manday.translator_required: 'false'
          this.form.addControl('manday_tranlatorChanges'+ manday.id,new FormControl(manday.translator_required));
           
          this.form.addControl('manday_'+manday.id,new FormControl(manday.adjusted_manday, [Validators.required,,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$')]));
          this.form.addControl('adjusted_manday_comment_'+manday.id,new FormControl(manday.adjusted_manday_comment));
  
          let percentadjus = +parseFloat(this.calculatePercentage(manday.adjusted_manday)) 
          let transVal = this.manday  && this.manday.find(res => res.unit_id === manday.id)
          debugger
          manday.final_manday_withtrans = manday.translator_required == "true" || (transVal && transVal.translator_required == "true" )?  parseFloat(manday.adjusted_manday) + percentadjus : manday.adjusted_manday      ;
          actualmanday = actualmanday + parseFloat(manday.final_manday);
        })
        this.actualmanday = actualmanday;
        //this.myFormGroup = new FromGroup(group);
        //this.form.addControl('child', this.myFormGroup);
  
        if(this.conversion_required_status==1 && this.offer_id>0)	
        {		  
          this.conversion_rate=res.conversion_rate;	  
          this.currency=res.currency;
          this.conversion_currency_code=res.conversion_currency_code;
        }
      
        this.form.patchValue({
          conversion_rate: this.conversion_rate,
          currency: this.currency,
          conversion_currency_code: this.conversion_currency_code
        });
      
        this.taxname=res.taxname;
        this.unitEntries=res.units;
        this.units.push(res.units);
        this.certificationFeeSubTotal();	
        this.getUnitManDayTotal();
      },
    error => {
      this.error = error;
      this.loading = false;
    });	
  })
    


	
	//console.log('currency:'+this.currency);
	
    this.man_day_cost = 1;		
    this.conversion_currency_code =  '';
    this.discount=0;
  } 
  
  get f() { return this.form.controls; }

  unitManDayTotal = 0;
  finalmandaytotal = 0;
  translatorValueChange (val, id) {
   
    let elem = this.appunitmanday.find(el => {
      return el.id == id
    })
    let manday = "manday_" + id
    let adjus = +parseFloat(this.form.value[manday]).toFixed(2)
    let percentadjus = +parseFloat(this.calculatePercentage(this.form.value[manday])) 
    
    elem.final_manday_withtrans =  val == "true" ?  (adjus +  percentadjus).toFixed(2) : adjus.toFixed(2);
    this.getUnitManDayTotal()
  }

  calculatePercentage(val:any){
        let mandays = parseFloat(val); 
        let percentage = 20;
        let perc='0';
        if(isNaN(mandays) || isNaN(percentage)){
            perc='0'; 
        }else{
           	perc = ((mandays/100) * percentage).toFixed(2);
        }
        return perc;
    }
  getUnitManDayTotal(){
    let totManday = 0;
    let finalmandaytotal = 0;
    let certification_fee_sub_total = 0;
    let NumberErr = false;
     
    this.appunitmanday.forEach(manday=>{
      //this.form.get('fee_name').value;
      let mandayVal = "manday_" + manday.id
      
      let unitmanday = parseFloat(this.form.value[mandayVal]);
        let adjus = +parseFloat(this.form.value[mandayVal]).toFixed(2)
    let percentadjus = +parseFloat(this.calculatePercentage(this.form.value[mandayVal])) 
    
    let finalTrans =  this.form.value["manday_tranlatorChanges" + manday.id] == "true" ?  (adjus +  percentadjus).toFixed(2) : adjus.toFixed(2);
    
      let funitmanday = parseFloat(finalTrans);
      if(isNaN(unitmanday)){
        NumberErr = true;
      }
      //if(unitmanday ===  )
      totManday = totManday + unitmanday;
      finalmandaytotal += funitmanday;
      //console.log(unitmanday +' =='+ parseFloat(manday.unit_manday_cost));
      certification_fee_sub_total = certification_fee_sub_total + (funitmanday * parseFloat(manday.manday_cost));
      
    });
    if(NumberErr){
      
      return false;
    }
     
    if(this.feesEntries && certification_fee_sub_total != this.feesEntries[0].amount || true){
      let expobject = this.feesEntries.slice(0);
      expobject = expobject[0];
      expobject.amount = certification_fee_sub_total;
      
      expobject.fee_description = finalmandaytotal+' Manday';
      this.feesEntries[0] = expobject;
      this.certificationFeeSubTotal();
    }
    
    this.unitManDayTotal = totManday;
    this.finalmandaytotal = finalmandaytotal
  }
  status_error = false;
  comment_error =false;
  checkUserSel(user_type=''){
    if(user_type =='approver'){
      if(this.model.approver_user_id ==''){
        this.user_id_error ='true';
      }else{
        this.modalss.close('AssignApprover');
      }
    }else if(user_type =='statusapproval'){
      if(this.model.status ==''){
        this.status_error =true;
      }else{
        this.status_error =false;
      }
      if(this.model.comment.trim() ==''){
        this.comment_error =true;
      }else{
        this.comment_error =false;
      }
      if(this.model.status !='' && this.model.comment.trim()!=''){
      
        this.modalss.close('StatusApproval');
      }
      
    }else{
      if(this.model.user_id ==''){
        this.user_id_error ='true';
      }else{
        this.modalss.close('Save');
      }
    }
  }
  
  logForm(val){
    //console.log(JSON.stringify(this.model));
  }
  open(content) {
     
    //, { centered: true }
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    
    this.modalss.result.then((result) => {
      if(result =='Save'){
        this.addReviewer();
      }else if(result =='AssignApprover'){
        this.assignApprover();
      }else if(result =='StatusApproval'){
        this.approveApplication();
      }

      
      
    }, (reason) => {
      //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
    });
  }

  approveApplication(){
    //console.log({app_id:this.id,user_id:this.model.comment,status:this.model.status});
    
    this.loading  = true;
    this.enquiryDetail.approveApplication({app_id:this.id,comment:this.model.comment,status:this.model.status})
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            this.applicationdata.status = res.app_status_name;
            this.applicationdata.app_status = res.app_status;
            
            this.success = res.message;

            setTimeout(() => {
              this.success = '';
            }, 1500);
          }else if(res.status == 0){
            this.error = res.message;
          }else{
            this.error = res;
          }
          this.loading = false;
        
     },
     error => {
         this.error = error;
         this.loading = false;
     });
  }
  assignApprover(){
    //console.log({app_id:this.id,user_id:this.model.approver_user_id});
    //return false;
    this.loading  = true;
    this.enquiryDetail.assignApprover({app_id:this.id,user_id:this.model.approver_user_id})
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //this.enquirydata.status = res.enquirystatus;
            //this.enquirydata.status_updated_date = res.status_updated_date;
            //this.enquirydata.status_updated_by = res.status_update_by;
            this.applicationdata.status = res.app_status_name;
            this.applicationdata.app_status = res.app_status;
            
            this.success = res.message;
            setTimeout(() => {
              this.success = '';
            }, 1500);
          }else if(res.status == 0){
            this.error = res.message;
          }else{
            this.error = res;
          }
          this.loading = false;
        
     },
     error => {
         this.error = error;
         this.loading = false;
     });
  }

  addReviewer(){
    //console.log(this.model.user_id);
    this.loading  = true;
    this.enquiryDetail.assignReviewer({app_id:this.id,user_id:this.model.user_id})
     .pipe(first())
     .subscribe(res => {
           
         if(res.status){
            //this.enquirydata.status = res.enquirystatus;
            //this.enquirydata.status_updated_date = res.status_updated_date;
            //this.enquirydata.status_updated_by = res.status_update_by;
            this.applicationdata.status = res.app_status_name;
            this.applicationdata.app_status = res.app_status;
            
            this.success = res.message;

            setTimeout(() => {
              this.success = '';
            }, 1500);
          }else if(res.status == 0){
            this.error = res.message;
          }else{
            this.error = res;
          }
          this.loading = false;
        
     },
     error => {
         this.error = error;
         this.loading = false;
     });
  }
  
  checkCertificationFee()
  {
	let fee_name = this.form.get('fee_name').value;
    let fee_description = this.form.get('fee_description').value;
	let amount = this.form.get('amount').value;	
		
	if(fee_name.trim()!='' || fee_description.trim()!='' || amount!='')
	{
		if(fee_name.trim()==''){
			this.fee_nameErrors = 'Please enter the Name';	
		}else{
			this.fee_nameErrors = '';
		}
		
		if(fee_description.trim()==''){
			this.fee_descriptionErrors = 'Please enter the Description';					
		}else{
			this.fee_descriptionErrors = '';
		}
		
		if(amount<=0 || amount==''){
			this.amountErrors = 'Please enter the Amount';	
		}else if(!amount.match(/^\d*\.?\d{0,2}$/g)){
			this.amountErrors = 'Invalid Amount';
		}else{
			this.amountErrors = '';
		}			
		
	}else{
		this.fee_nameErrors = '';
		this.fee_descriptionErrors = '';
		this.amountErrors = '';
	}	
		
  }
  
  removeFee(index:number) {
    //let index= this.feeEntries.findIndex(s => s.id ==  productId);
    if(index != -1)
      this.feesEntries.splice(index,1);
  
	  this.certificationFeeSubTotal();
  }
  
  feeStatus=true;
  feeIndex=null;
  addFee(){
	this.feeStatus=true;  
    let fee_name = this.form.get('fee_name').value;
    let fee_description = this.form.get('fee_description').value;
	//let noofmanday = this.form.get('noofmanday').value;
    let amount = this.form.get('amount').value;
		
	if(fee_name.trim()==''){
        this.fee_nameErrors = 'Please enter the Name';	
		this.feeStatus=false;		
    }
	
	if(fee_description.trim()==''){
        this.fee_descriptionErrors = 'Please enter the Description';	
		this.feeStatus=false;		
    }
	
    if(amount<=0 || amount==''){
        this.amountErrors = 'Please enter the Amount';	
		this.feeStatus=false;
    }else if(!amount.match(/^\d*\.?\d{0,2}$/g)){
		this.amountErrors = 'Invalid Amount';
		this.feeStatus=false;
	}
	
	if(!this.feeStatus)
	{
		return false;
	}
    	    
    let expobject:any=[];
    expobject["fee_name"] = fee_name;
    expobject["fee_description"] = fee_description;
	//expobject["noofmanday"] = noofmanday;
    expobject["amount"] = amount;
    	  
    if(this.feeIndex!==null){
	  this.feesEntries[this.feeIndex] = expobject;
    }else{
	  this.feesEntries.push(expobject);
    }
    this.form.patchValue({
      fee_name: '',
	    fee_description: '',	  
	    amount: ''
    });
    this.feeIndex = null;
	  this.certificationFeeSubTotal();
	//}
  }
  editFee(index:number){
   // let prd= this.feesEntries.find(s => s.id ==  productId);
   this.feeIndex= index;
	  let qual = this.feesEntries[index];
    this.form.patchValue({
      fee_name: qual.fee_name,
	    fee_description: qual.fee_description,
	    amount: qual.amount	  
    });
  }
  
  
  checkOtherExpensesFee()
  {
    let expense_name = this.form.get('expense_name').value;
    let expense_description = this.form.get('expense_description').value;
    let expense_amount = this.form.get('expense_amount').value;	
      
    if(expense_name.trim()!='' || expense_description.trim()!='' || expense_amount!='')
    {
      if(expense_name.trim()==''){
        this.expense_nameErrors = 'Please enter the Name';	
      }else{
        this.expense_nameErrors = '';
      }
      
      if(expense_description.trim()==''){
        this.expense_descriptionErrors = 'Please enter the Description';					
      }else{
        this.expense_descriptionErrors = '';
      }
      
      /*
      if(expense_amount<=0 || expense_amount==''){
        this.expense_amountErrors = 'Please enter the Amount';	
      }else if(!expense_amount.match(/^\d*\.?\d{0,2}$/g)){
        this.expense_amountErrors = 'Invalid Amount';
      }else{
        this.expense_amountErrors = '';
      }
      */		
      
      if(expense_amount.trim()==''){
        this.expense_amountErrors = 'Please enter the Amount';			
      }else if(!expense_amount.match(/^\d*\.?\d{0,2}$/g)){
        this.expense_amountErrors = 'Invalid Amount';			
        }else{
        this.expense_amountErrors = '';
      }
      
    }else{
      this.expense_nameErrors = '';
      this.expense_descriptionErrors = '';
      this.expense_amountErrors = '';
    }	
		
  }
   
  removeOtherExpenses(index:number) {
    //let index= this.feeEntries.findIndex(s => s.id ==  productId);
    if(index != -1)
      this.expensesEntries.splice(index,1);
    
	  this.otherExpensesSubTotal();
  }
  
  expenseStatus=true;
  expenseIndex=null;
  addOtherExpenses(){
    this.expenseStatus=true;  
    let expense_name = this.form.get('expense_name').value;
    let expense_description = this.form.get('expense_description').value;
    let expense_amount = this.form.get('expense_amount').value;
    let entry_type = this.form.get('entry_type').value;
    let type = this.form.get('type').value;
    if(expense_name.trim()==''){
      this.expense_nameErrors = 'Please enter the Name';
      this.expenseStatus=false;
    }
    
    if(expense_description.trim()==''){
      this.expense_descriptionErrors = 'Please enter the Description';
      this.expenseStatus=false;
    }
      
    if(expense_amount=='' || expense_amount<=0){
      this.expense_amountErrors = 'Please enter the Amount';
      this.expenseStatus=false;
    }else if(!expense_amount.match(/^\d*\.?\d{0,2}$/g)){
      this.expense_amountErrors = 'Invalid Amount';
      this.expenseStatus=false;
    }
    
    if(!this.expenseStatus)
    {
      return false;
    }
	
	  expense_amount = Number(expense_amount).toFixed(2);
	
	  let expobject:any=[];
    expobject["expense_name"] = expense_name;
    expobject["expense_description"] = expense_description;
    expobject["expense_amount"] = expense_amount;
    expobject["entry_type"] = entry_type;
    expobject["type"] = type;
    if(this.expenseIndex!==null){
	    this.expensesEntries[this.expenseIndex] = expobject;
    }else{
      expobject["entry_type"] = 1;
      expobject["type"] = 3;
      
	    this.expensesEntries.push(expobject);
    }
    this.form.patchValue({
      expense_name: '',
      expense_description: '',
      expense_amount: '',
      entry_type:'',
      type:''
    });
  //}
    this.expenseIndex = null;
	  this.otherExpensesSubTotal();
  }
  editOtherExpenses(index:number){
   // let prd= this.expensesEntries.find(s => s.id ==  productId);
    this.expenseIndex= index;
	  let qual = this.expensesEntries[index];
    this.form.patchValue({
      expense_name: qual.expense_name,
      expense_description: qual.expense_description,
      expense_amount: qual.expense_amount,
      entry_type: qual.entry_type,
      type:qual.type,
    });
  }
  
  certificationFeeSubTotal()
  {
    let fee_sub_total=0;	
    if(this.feesEntries)
    this.feesEntries.forEach(function(fees){
      fee_sub_total = fee_sub_total+parseFloat(fees.amount);
    });
    //fee_sub_total = fee_sub_total).toFixed(2);
    this.certification_fee_sub_total = fee_sub_total;
    this.otherExpensesSubTotal();
    this.totalFee();
  }
  
  otherExpensesSubTotal()
  {
    let expense_sub_total=0;	
    if(this.expensesEntries && this.expensesEntries.length>0){
    this.expensesEntries.forEach(function(fees){
      expense_sub_total = expense_sub_total+parseFloat(fees.expense_amount);
    });
    }
    //expense_sub_total = Number(expense_sub_total).toFixed(2);
    //expense_sub_total = parseFloat(expense_sub_total).toFixed(2)
    this.other_expense_sub_total = expense_sub_total;
    this.totalFee();
  }
  

  //calculateManday(val)
  //{
   // let amount_based_on_manday = 0;
    //amount_based_on_manday = val*this.man_day_cost;
    //amount_based_on_manday = Number(amount_based_on_manday).toFixed(2);
    //this.form.patchValue({amount:amount_based_on_manday});
  //}
  
  totalFee()
  {
    let tax_amount=0;
    //tax_amount.toString();
    let certification_fee_other_expenses=0;
    //certification_fee_other_expenses.toString();
    certification_fee_other_expenses = this.certification_fee_sub_total + this.other_expense_sub_total;
    this.total_fee = certification_fee_other_expenses;
	
	  this.grand_total_fee = this.total_fee-this.discount;
		
    //tax_amount = ((this.total_fee*this.tax_percentage)/100);
	  tax_amount = ((this.grand_total_fee*this.tax_percentage)/100);
	
    this.tax_rate=tax_amount;//).toFixed(2);
    
    //this.total_payable_amount=this.total_fee+this.tax_rate;
	  this.total_payable_amount=this.grand_total_fee+this.tax_rate;
	
    this.total_payable_amount = this.total_payable_amount;
    //this.total_payable_amount = Number(this.total_payable_amount).toFixed(2);
    this.calculateCoversionTotalPayableAmount();
  }
  
  changeCurrency(val)
  {
	  this.currency_code =  val;
  }
  
  conversionRequired(val)
  {
    let conStatus=0;
    if(val)	
    {
      conStatus=1;	
    }
    this.conversion_required_status=conStatus; 	
  }
  
  changeDiscount(val)
  {
    this.discount =  val;
    this.totalFee();
  } 
  
  changeConversionRate(val)
  {
    let conversion_rate_fld=this.form.get('conversion_rate').value.toString();   
    if(!conversion_rate_fld.match(/^\d*\.?\d{0,2}$/g))	
    {
      this.conversion_rateErrors = 'Invalid Conversion Rate';	
      return false;
    }
    this.conversion_rateErrors = '';	
	
	  this.conversion_rate =  val;
    this.calculateCoversionTotalPayableAmount();
  }
  
  calculateCoversionTotalPayableAmount()
  {
    this.conversion_total_payable_amount=this.total_payable_amount*this.conversion_rate;  
    //this.conversion_total_payable_amount= this.conversion_total_payable_amount.toFixed(2);
    //this.conversion_total_payable_amount = Number(this.conversion_total_payable_amount).toFixed(2);
  }

  changeConversionCurrencyCode(val)
  {
    let conversion_currency_code_fld=this.form.get('conversion_currency_code').value;   
    if(conversion_currency_code_fld=='')	
    {
      this.conversion_currency_codeErrors = 'Please enter the Conversion Currency';	
      return false;
    }
    this.conversion_currency_codeErrors = ''; 
    this.conversion_currency_code =  val;
  }
  /*
  ngAfterViewInit() {
    setTimeout(() => {
		this.certificationFeeSubTotal();
    }, 500);
  }
  */

  
  onSubmit(){
	
	// ------------- Currency & Conversion Rate Validation Code Start Here ----------------
      let conversionCurrencyStatus=true;
      let conversion_required_fld=this.form.get('conversion_required').value;
	  //if(conversion_required_fld==1)
      //{
        let currency_fld=this.form.get('currency').value;
        let conversion_rate_fldd=this.form.get('conversion_rate').value!='' && this.form.get('conversion_rate').value!= null ?this.form.get('conversion_rate').value.toString():'';	
        let conversion_currency_code_fld=this.form.get('conversion_currency_code').value;
        
        if(currency_fld.trim()==''){
          this.currencyErrors = 'Please enter the Base Currency';	
          conversionCurrencyStatus=false;			
        }else{
          this.currencyErrors = '';
        }
          
        if(conversion_rate_fldd=='' && conversion_rate_fldd<=0){
          this.conversion_rateErrors = 'Please enter the Conversion Rate';
          conversionCurrencyStatus=false;
        }else if(!conversion_rate_fldd.match(/^\d*\.?\d{0,2}$/g)){
          this.conversion_rateErrors = 'Invalid Conversion Rate';
          conversionCurrencyStatus=false;
          }else{
          this.conversion_rateErrors = '';
        }
        
        if(conversion_currency_code_fld.trim()==''){
          this.conversion_currency_codeErrors = 'Please select the Conversion Currency';
          conversionCurrencyStatus=false;
        }else{
          this.conversion_currency_codeErrors = '';
        }		
      //}

      if(!conversionCurrencyStatus)
      {
        this.error = {summary:this.errorSummary.errorSummaryText};
        return false;
      }
      // ------------- Currency & Conversion Rate Validation Code End Here ----------------
        
      // ------------- Certification Fee Validation Code Start Here ----------------
      this.checkCertificationFee();	
      if(this.fee_nameErrors!='' || this.fee_descriptionErrors != '' || this.amountErrors !='')
      {
        this.error = {summary:this.errorSummary.errorSummaryText};
        return false;
      }		
      this.fee_nameErrors = '';
      this.fee_descriptionErrors = '';
      this.amountErrors = '';
      // ------------- Certification Fee Validation Code End Here ----------------
      
      // ------------- Other Expenses Fee Validation Code Start Here ----------------
      this.checkOtherExpensesFee();	
      if(this.expense_nameErrors!='' || this.expense_descriptionErrors != '' || this.expense_amountErrors !='' || !this.form.valid)
      {
        this.error = {summary:this.errorSummary.errorSummaryText};
        return false;
      }		


      this.expense_nameErrors = '';
      this.expense_descriptionErrors = '';
      this.expense_amountErrors = '';
      // ------------- Other Expenses Fee Validation Code End Here ----------------
      if (this.form.valid) 
	  {    
		  this.error = '';	

		  this.loading = true;

		  let certification_fee =[];
		  let other_expenses =[];
      if(this.feesEntries)
		  this.feesEntries.forEach(val=>{
			certification_fee.push({activity:val.fee_name,description:val.fee_description,amount:val.amount});
		  });
      if(this.expensesEntries)
		  this.expensesEntries.forEach(val=>{
			other_expenses.push({type:val.type,entry_type:val.entry_type,activity:val.expense_name,description:val.expense_description,amount:val.expense_amount});
		  });
		  
		  let subcontractor_names = this.unitEntries.map(unit=>unit.name);
		  let subcontractor_name = subcontractor_names.join();
		  //console.log(subcontractor_names);
		  //return false;
		
		  const standard = this.standards.join();
		  //subcontractor_name = this.units.map(unit=>unit.name);

		  let unitmandayArr=[];
		  if(!this.ossEditStatus)
		  {
			  this.appunitmanday.forEach(manday=>{
				//this.form.get('fee_name').value;
				unitmandayArr.push({unit_id:manday.id,adjusted_manday:parseFloat(this.f['manday_'+manday.id].value),
        translator_required: this.f['manday_tranlatorChanges' + manday.id].value,
        final_manday_withtrans: manday.final_manday_withtrans,
        adjusted_manday_comment:this.f['adjusted_manday_comment_'+manday.id].value});
			  });
		  }else{
			  this.appunitmanday.forEach(manday=>{
				//this.form.get('fee_name').value;
				unitmandayArr.push({unit_id:manday.id,adjusted_manday:manday.adjusted_manday,
               translator_required: this.f['manday_tranlatorChanges' + manday.id].value,
        final_manday_withtrans: manday.final_manday_withtrans,
          adjusted_manday_comment:manday.adjusted_manday_comment});
			  });
		  }
		  //return false;

		  let postdata = {
			app_id:this.id,
			unitManday:unitmandayArr,
			offer_id:this.offer_id,
			offer_code:this.applicationdata.offercode,
			standard,
			inspection_type :1,
			noof_subcontractor :this.units.length,
			subcontractor_name,
			conversion_rate:this.conversion_rate,
			currency:this.currency_code,
			conversion_currency_code:this.conversion_currency_code,
			certification_fee_sub_total:this.certification_fee_sub_total,
			other_expense_sub_total:this.other_expense_sub_total,
			total:this.total_fee,
			gst_rate:this.tax_rate,
			total_payable_amount:this.total_payable_amount,
			conversion_total_payable:this.conversion_total_payable_amount,
			grand_total_fee:this.grand_total_fee,
			discount:this.discount,
			taxname:this.taxname,
			certification_fee,
			other_expenses,
			manday: this.unitManDayTotal,//this.applicationdata.mandays,
			conversion_required_status:this.conversion_required_status,
			tax_percentage:this.tax_percentage
		  };
     
		  
		  this.offerDetail.addOffer(postdata)
		  .pipe(first())
		  .subscribe(res => {

			  if(res.status){
				//this.enquiryForm.reset();
				//this.submittedSuccess =1;
				let offer_id = this.offer_id;
				if(res.offer_id){
				  offer_id = res.offer_id;
				}
				//this.success = res.message;           
				this.success = {summary:res.message};
				this.buttonDisable = true;
				
				setTimeout(() => {
				  this.router.navigateByUrl('/offer/view-offer?id='+this.id+'&offer_id='+offer_id); 
				}, this.errorSummary.redirectTime);
	  
			  }else if(res.status == 0){
				//this.submittedError =1;
				this.error = {summary:res.msg};
			  }else{
				//this.submittedError =1;
				this.error = {summary:res};
			  }
			  this.loading = false;
			  
		  },
		  error => {
			  this.error = {summary:error};
			  this.loading = false;
		  });
	  }	  
    }
  }
