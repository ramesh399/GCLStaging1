import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { GenerateDetailService } from '@app/services/invoice/generate-detail.service';
import {GenerateListService} from '@app/services/invoice/generate-list.service';
import { Invoice } from '@app/models/invoice/invoice';
import { Observable} from 'rxjs';
import { first } from 'rxjs/operators';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { UserService } from '@app/services/master/user/user.service';
import { CountryService } from '@app/services/country.service';
import { Country } from '@app/services/country';

@Component({
  selector: 'app-invoice-generate',
  templateUrl: './invoice-generate.component.html',
  styleUrls: ['./invoice-generate.component.scss']
})
export class InvoiceGenerateComponent implements OnInit {

  constructor(private countryservice: CountryService, private userService:UserService,private activatedRoute:ActivatedRoute,private generateDetail:GenerateDetailService, private modalService: NgbModal,private fb:FormBuilder,private router:Router,private errorSummary: ErrorSummaryService, public service: GenerateListService) { }
  id:number;
  offer_id:number;
  error:any;
  success:any;
  loading = false;
  offerdata:Invoice;
  discount=0;
  discount_status=false;
  grand_total_fee=0;
  total_payable_amount=0; 
  total_fee=0;
  conversion_total_payable_amount=0;
  discountErrors = '';
  feesEntries = [];
  creditList:any=[];
  expensesEntries = [];
  form : FormGroup;  
  userForm : FormGroup;  
  currency_code = '';
  loadinginfo:any;
  loadingdata:any;
  expense_nameErrors=''; 
  expense_descriptionErrors='';
  expense_amountErrors='';
  type:any;
  title:any;
  backLink:any;
  invoiceTypeArray = {'1':'Customer Invoice','2':'OSS Invoice','3':'Customer Additional Invoice','4':'OSS Additional Invoice'};
  invoiceLinkArray = {'1':'customer-invoice-list','2':'oss-invoice-list','3':'customer-additional-invoice-list','4':'oss-additional-invoice-list'};
  customerList:any;
  ddlLabel:any;

  totalColSpan:number = 4;
  ngOnInit() {
	  this.type = this.activatedRoute.snapshot.queryParams.type;	
	  this.backLink = this.invoiceLinkArray[this.type];
	  this.title = this.invoiceTypeArray[this.type];	
	  this.id = this.activatedRoute.snapshot.queryParams.id;
    this.offer_id = this.activatedRoute.snapshot.queryParams.offer_id;	      
    if(this.type ==3 || this.type ==4){
      this.totalColSpan = 3;
    }
    this.userForm = this.fb.group({
      customer:['',[Validators.required]],
      credit_note:[1]	  
	  });	  
	   
	  this.form = this.fb.group({
		  discount:['',[Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$')]],
		  expense_name:[''],
		  expense_description:[''],
      expense_amount:[''],
      conversion_required:[''],
      currency:[''], 
      conversion_rate:['',[Validators.required,Validators.min(0.01),Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$')]],
      conversion_currency_code:['',[Validators.required]]
    });
    
	  if(this.id!==undefined && this.type!='')
	  {
      this.loadingdata = true;
		  this.generateDetail.getInvoice({id:this.id,type:this.type}).pipe(first())
		  .subscribe(res => {
        this.offerdata = res;
        let customer_id;
        let credit_note:any =1;
        this.loadingdata = false;
        this.currency_code = res.offer?res.offer.currency_code:'';
        //this.conversion_required_status = res.offer ? res.offer.conversion_required_status : 0;
		this.conversion_required_status = 1;
        this.total_payable_amount = this.offerdata.offer.total_payable_amount;
        this.conversion_total_payable_amount = this.offerdata.offer.conversion_total_payable_amount;

        if(this.type==3){
          customer_id = this.offerdata.customer_id;
          credit_note = this.offerdata.credit_note_option;
        }else if(this.type==4){
          customer_id = this.offerdata.franchise_id;
          credit_note = this.offerdata.credit_note_option;
        }
        this.userForm.patchValue({
          customer: customer_id,
          credit_note: credit_note
        });

        if(this.conversion_required_status==1 && res.offer)	
        {		  
          this.conversion_rate=res.offer.conversion_rate;	  
          //this.currency_code=res.currency_code;
          this.conversion_currency_code=res.offer.conversion_currency_code;
        }
        
        this.form.patchValue({
          conversion_rate: this.conversion_rate,
          currency: this.currency_code,
          conversion_currency_code: this.conversion_currency_code ? this.conversion_currency_code : '' 
        });
        //this.tax_rate = this.offerdata.offer.gst_rate;
        
        if(this.offerdata && this.offerdata.invoice_discount>0)
        {
          this.discount = this.offerdata.invoice_discount;
          //this.grand_total_fee = this.offerdata.invoice_grand_total_fee;
          //this.tax_rate = this.offerdata.tax_amount;
          
          
          this.changeDiscount(this.offerdata.invoice_discount);
        }		
        this.otherExpensesSubTotal();
		  },error => {
        this.error = {summary:error};
			this.loadingdata = false;
		  });		  
    }
    
    if(this.type==3){
      this.loadinginfo = true;
		  this.ddlLabel='Customer';
		  this.userService.getAllUser({type:2,filterdata:1}).pipe(first())
		  .subscribe(res => {
        
        this.customerList = res.users;
        this.loadinginfo = false;
		  },error => {
        this.error = {summary:error};
        this.loadinginfo = false;
		  });		  
	  }else if(this.type==4){
      this.loadinginfo = true;
		  this.ddlLabel='OSS';
		  this.userService.getAllUser({type:3}).pipe(first())
		  .subscribe(res => {
        this.customerList = res.users;
        this.loadinginfo = false;			
		  },error => {
        this.error = {summary:error};
        this.loadinginfo = false;
		  });
    }

    this.service.getFilterOptions().pipe(first())
		.subscribe(res => {
      this.creditList = res.creditOptions;
		});

    this.countryservice.getCountryCode().subscribe(res => {
      this.countryCodeList = res['country_code_list'];
    });
  }

  get f() { return this.form.controls; }

  get uf() { return this.userForm.controls; }
  
  changeDiscount(val)
  {
	let discountVal = this.form.get('discount').value;
	 
	
	//this.discountErrors = '';
	//this.error = '';
	
	if(!discountVal.match(/^\d*\.?\d{0,2}$/g)){
		this.discountErrors = 'Invalid Discount';
    //this.error = this.errorSummary.errorSummaryText;
    this.error = {summary:this.errorSummary.errorSummaryText};
		return false;
	}else if(parseFloat(discountVal)>=parseFloat(this.offerdata.offer.other_expense_sub_total) && parseFloat(discountVal)!=0){
		this.discountErrors = 'Discount should be less than the Total Amount';
		this.error = {summary:this.errorSummary.errorSummaryText};
		return false;
	}
	this.discountErrors = '';
	//this.error = '';
	
	this.offerdata.invoice_discount = val;
	
	this.discount =  val;
	if(val>0){
		this.discount_status=true;
	}else{
		this.discount_status=false;
	}
	
  //this.totalFee();
  this.otherExpensesSubTotal();
	
	/*
	this.total_fee = this.offerdata.offer.total;
	
	this.grand_total_fee = this.total_fee-this.discount;
	
	this.tax_rate = ((this.grand_total_fee*this.offerdata.offer.tax_percentage)/100);
	
	this.total_payable_amount=this.grand_total_fee+this.tax_rate;
	
	if(this.offerdata.offer.conversion_required_status==1)
	{
		this.conversion_total_payable_amount=this.total_payable_amount*this.offerdata.offer.conversion_rate;
	}	
	*/
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
  
  resetOtherExpenses(){
	this.form.patchValue({
      expense_name: '',
      expense_description: '',
      expense_amount: '',
      entry_type:''
    });
  
    this.expenseIndex = null;
  }
   
  removeOtherExpenses(index:number) {
    //let index= this.feeEntries.findIndex(s => s.id ==  productId);
    if(index != -1)
      this.offerdata.offer_other_expenses.splice(index,1);
    
	  this.otherExpensesSubTotal();
  }
  
  expenseStatus=true;
  expenseIndex=null;
  addOtherExpenses(){
    this.expenseStatus=true;  
    let expense_name = this.form.get('expense_name').value;
    let expense_description = this.form.get('expense_description').value;
    let expense_amount = this.form.get('expense_amount').value;
    let entry_type = 'new';
      
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
    expobject["activity"] = expense_name;
    expobject["description"] = expense_description;
    expobject["amount"] = expense_amount;
    expobject["entry_type"] = entry_type;
    
	if(this.expenseIndex!==null){
	    this.offerdata.offer_other_expenses[this.expenseIndex] = expobject;
    }else{
		this.offerdata.offer_other_expenses.push(expobject);
    }
	
	
	
    this.form.patchValue({
      expense_name: '',
      expense_description: '',
      expense_amount: '',
      entry_type:''
    });
  
    this.expenseIndex = null;
	this.otherExpensesSubTotal();
  }
  editOtherExpenses(index:number){
   // let prd= this.expensesEntries.find(s => s.id ==  productId);
    window.scroll(0,0);
    this.expenseIndex= index;
	let qual = this.offerdata.offer_other_expenses[index];
    this.form.patchValue({
      expense_name: qual.activity,
      expense_description: qual.description,
      expense_amount: qual.amount,
      entry_type: qual.entry_type
    });
  }
  
  otherExpensesSubTotal()
  {
    let expense_sub_total=0;	
    this.offerdata.offer_other_expenses.forEach(function(fees){
		expense_sub_total = expense_sub_total+parseFloat(fees.amount);
    });
	
    //expense_sub_total = Number(expense_sub_total).toFixed(2);
    //expense_sub_total = parseFloat(expense_sub_total).toFixed(2)
	
    this.offerdata.offer.other_expense_sub_total = expense_sub_total.toString();

    //this.offerdata.offer.total = expense_sub_total.toString();
    
	
    this.totalFee();
  }
  
  totalFee()
  {
    let tax_amount=0;
    //tax_amount.toString();
    let certification_fee_other_expenses:any=0;
    //certification_fee_other_expenses.toString();
    certification_fee_other_expenses = this.offerdata.offer.other_expense_sub_total;
    this.total_fee = certification_fee_other_expenses;
    
    if(parseFloat(this.discount.toString())<parseFloat(this.total_fee.toString())){
      this.offerdata.offer.grand_total_fee = this.total_fee-this.discount;
    }else{
      
      this.discount = 0;
      this.offerdata.offer.grand_total_fee = this.total_fee;
    }
	  
		
    tax_amount = ((this.offerdata.offer.grand_total_fee*this.offerdata.offer.tax_percentage)/100);
	
    this.offerdata.offer.gst_rate = ((this.offerdata.offer.grand_total_fee*this.offerdata.offer.tax_percentage)/100);
    
    //this.total_payable_amount=this.total_fee+this.tax_rate;
	  this.total_payable_amount=parseFloat(this.offerdata.offer.grand_total_fee.toString())+parseFloat(this.offerdata.offer.gst_rate.toString());
	
    this.total_payable_amount = this.total_payable_amount;
    //this.total_payable_amount = Number(this.total_payable_amount).toFixed(2);
    this.calculateCoversionTotalPayableAmount();
  }
  /*
  totalFee()
  {
    let tax_amount=0;
        
    //this.offerdata.offer.total = parseFloat(this.offerdata.offer.certification_fee_sub_total) + parseFloat(this.offerdata.offer.other_expense_sub_total);
	 
	this.offerdata.offer.total = parseFloat(this.offerdata.offer.certification_fee_sub_total) + parseFloat(this.offerdata.offer.other_expense_sub_total);
	 
    //this.total_fee = this.offerdata.offer.total;
	
	this.offerdata.offer.grand_total_fee = this.offerdata.offer.total-this.offerdata.invoice_discount;
	
	this.offerdata.offer.gst_rate = ((this.offerdata.offer.grand_total_fee*this.offerdata.offer.tax_percentage)/100);
	
	this.offerdata.offer.total_payable_amount=this.offerdata.offer.grand_total_fee+this.offerdata.offer.gst_rate;
	
	if(this.offerdata.offer.conversion_required_status==1)
	{
		this.offerdata.offer.conversion_total_payable=this.offerdata.offer.total_payable_amount*this.offerdata.offer.conversion_rate;
	}	
	
	//this.offerdata.offer.grand_total_fee = this.offerdata.offer.total-this.offerdata.offer.invoice_discount;
		
    //this.offerdata.offer.gst_rate = ((this.offerdata.offer.grand_total_fee*this.offerdata.offer.tax_percentage)/100);
	
    //this.offerdata.offer.tax_rate=tax_amount;//).toFixed(2);
    
    //this.total_payable_amount=this.total_fee+this.tax_rate;
	//this.total_payable_amount=this.offerdata.offer.grand_total_fee+this.offerdata.offer.tax_rate;
	
    //this.total_payable_amount = this.total_payable_amount;
    //this.total_payable_amount = Number(this.total_payable_amount).toFixed(2);
    //this.calculateCoversionTotalPayableAmount();
  }
  */
  
  checkEntryType()
  {
	 if(this.offerdata && this.offerdata.offer_other_expenses)
	 {
		 let prd = this.offerdata.offer_other_expenses.find(s => s.entry_type ==  'new'); 
		 if(prd!==undefined)
		 {
			 return true;
		 }
	 }	 
	 return false;
  }
  
 
  
  
  onSubmit(){
    

    // ------------- Currency & Conversion Rate Validation Code Start Here ----------------
    let conversionCurrencyStatus=true;
    //let conversion_required_fld=this.form.get('conversion_required').value;
    let conversion_required_fld=this.conversion_required_status;
    let currency_fld=this.form.get('currency').value;
    
    //if(conversion_required_fld==1)
    //{   
      let conversion_rate_fldd=(this.form.get('conversion_rate').value!='' && this.form.get('conversion_rate').value!=null)?this.form.get('conversion_rate').value.toString():'';

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
      
      if(conversion_currency_code_fld==null || conversion_currency_code_fld=='' || conversion_currency_code_fld.trim()==''){
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



    let discountVal = this.form.get('discount').value;
    
    if(discountVal!=0){
      if(!discountVal.match(/^\d*\.?\d{0,2}$/g))
      {
        this.discountErrors = 'Invalid Discount';
        this.error = {summary:this.errorSummary.errorSummaryText};
        return false;
      }else if(parseFloat(discountVal)>=parseFloat(this.offerdata.offer.other_expense_sub_total)){
        this.discountErrors = 'Discount should be less than the Total Amount';
        this.error = {summary:this.errorSummary.errorSummaryText};
        return false;
      }
    }
    
	
    if(!this.offerdata || !this.offerdata.offer_other_expenses || this.offerdata.offer_other_expenses.length<=0 ){
      //this.error = "Please Add Expenses";
      this.error = {summary:'Please Add Expenses'};
      return false;
    }
	
	if (this.form.valid) 
	{
		this.discountErrors = '';
		//this.error = '';	
		
		this.loading = true; 

		let other_expenses = [];
		this.offerdata.offer_other_expenses.forEach(val=>{
		  //if(val.entry_type=='new')
		  //{
			other_expenses.push({entry_type:val.entry_type,activity:val.activity,description:val.description,amount:val.amount});
		  //}
		});	

		let customer_id:any;
		let credit_note:any;
		if(this.type == 3 || this.type == 4){
		  customer_id = this.userForm.get('customer').value;
		  credit_note = this.userForm.get('credit_note').value;
		}
		
		
		let postdata = {
		  id:this.id,
		  type:this.type,
		  customer_id:customer_id,
		  credit_note_option:credit_note,
		  offer_id:this.offer_id,	   
		  certification_fee_sub_total:this.offerdata.offer.certification_fee_sub_total,
		  other_expense_sub_total:this.offerdata.offer.other_expense_sub_total,
		  total_fee:this.total_fee,      
		  discount:this.offerdata.invoice_discount,
		  grand_total_fee:this.offerdata.offer.grand_total_fee,
		  tax_amount:this.offerdata.offer.gst_rate,
		  total_payable_amount:this.total_payable_amount,
		  //conversion_total_payable:this.offerdata.offer.conversion_total_payable,
		  other_expenses,
		  conversion_rate:this.conversion_rate,
		  currency:currency_fld,
		  conversion_currency_code:this.conversion_currency_code,
		  conversion_total_payable:this.conversion_total_payable_amount,
		  conversion_required_status:this.conversion_required_status
		};	
		
		this.generateDetail.addOffer(postdata)
		.pipe(first())
		.subscribe(res => {

			if(res.status){
			  //this.enquiryForm.reset();
			  //this.submittedSuccess =1;
				  this.success = {summary:res.message};           
			  setTimeout(() => {
				this.router.navigateByUrl('/invoice/view-invoice?id='+res.id+'&type='+this.type); 
			  },this.errorSummary.redirectTime);
	 
			}else if(res.status == 0){
			  this.error = {summary:this.errorSummary.errorSummaryText};
			}else{
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
  
  changeCustomer()
  {
    //this.offerdata={};
    this.loadingdata = true;
	  this.generateDetail.getAdditionInvoice({id:this.userForm.get('customer').value,type:this.type}).pipe(first())
	  .subscribe(res => {
      this.offerdata = res;
      this.form.patchValue({
        conversion_rate: this.offerdata.offer.conversion_rate,
        currency: this.offerdata.offer.currency,
        conversion_currency_code: '',
        discount:0
      });
      this.conversion_rate = '';
      this.conversion_currency_code = '';
      this.discount = 0;

      this.currency_code = this.offerdata.offer.currency;
      //this.tax_rate = this.offerdata.offer.gst_rate;
      
      if(this.offerdata && this.offerdata.invoice_discount>0)
      {
        this.discount = this.offerdata.invoice_discount;
        //this.grand_total_fee = this.offerdata.invoice_grand_total_fee;
        //this.tax_rate = this.offerdata.tax_amount;
        //this.total_payable_amount = this.offerdata.invoice_total_payable_amount;
        //this.conversion_total_payable_amount = this.offerdata.invoice_conversion_total_payable;
        this.otherExpensesSubTotal();
        this.changeDiscount(this.offerdata.invoice_discount);
      }		
      this.loadingdata = false;
	  },error => {
      this.error = {summary:error};
      this.loadingdata = false;
	  });
  }
  

  conversion_required_status:any=1;
  conversion_rateErrors = '';
  conversion_rate:any;
  countryCodeList:Country[];
  conversion_required='';
  currencyErrors ='';
  conversion_currency_codeErrors = '';
  ossEditStatus=false;
  conversion_currency_code='';

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

  changeConversionRate(val)
  {
    let conversion_rate_fld=this.form.get('conversion_rate').value!='' && this.form.get('conversion_rate').value != null? this.form.get('conversion_rate').value.toString():'';   
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
}
