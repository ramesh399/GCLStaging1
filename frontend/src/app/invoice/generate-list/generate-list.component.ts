import {DecimalPipe} from '@angular/common';
import {Directive, Component, QueryList, ViewChildren,OnInit } from '@angular/core';
import {Observable} from 'rxjs';
import { ActivatedRoute, Router } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
//import { Offer } from '@app/models/offer/offer';
import { Invoice } from '@app/models/invoice/invoice';

import { AuthenticationService } from '@app/services';
import {GenerateListService} from '@app/services/invoice/generate-list.service';
import { UserService } from '@app/services/master/user/user.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl, Form } from '@angular/forms';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';
import {saveAs} from 'file-saver';
import { User } from '@app/models/master/user';
import { UserToken } from '@app/models/usertoken';


@Component({
  selector: 'app-generate-list',
  templateUrl: './generate-list.component.html',
  styleUrls: ['./generate-list.component.scss'],
  providers: [GenerateListService] 
})
export class GenerateListComponent implements OnInit{

  invoices$: Observable<Invoice[]>;
  total$: Observable<number>;
  invoiceamount$:any=[];
  userdecoded:UserToken;
  //sno:number;
  invoice_id:number;
  userType:number;
  userdetails:any;
  franchiseList:User[];
  creditList:any=[];
  paymentList:any=[];
  filterpaymentList:any=[];
  paginationList = PaginationList;
  commontxt = commontxt;
  type:any;
  title:any;
  form : FormGroup;
  alertSuccessMessage:any = '';
  alertErrorMessage:any = '';
  popupbtnDisable=false;
  invoiceTypeArray = {'1':'Customer Invoice List','2':'OSS Invoice List','3':'Customer Additional Invoice List','4':'OSS Additional Invoice List'};
  model: any = {payment_date:'',payment_status:'',payment_comment:''};
  minDate: Date;
  error:any;
  totalPaid:any=0;
  totalUnPaid:any=0;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  addAdditionalInvoice:any=0;
  ngOnInit() {
    this.minDate = new Date();
    this.type = this.activatedRoute.snapshot.data['invoicetype'];	
    
    this.title = this.invoiceTypeArray[this.type];	

    this.form = this.fb.group({
      payment_date:['',[Validators.required]],
      payment_status:['',[Validators.required]],
      payment_comment:['',[Validators.required]],     
    });

    this.userservice.getAllUser({type:3}).pipe(first())
		.subscribe(res => {
		  this.franchiseList = res.users;
		},
		error => {
			this.error = {summary:error};
    });
    
    this.service.getFilterOptions().pipe(first())
		.subscribe(res => {
      this.creditList = res.creditOptions;
      this.paymentList = res.paymentOptions;
      this.filterpaymentList = res.filterpaymentOptions;
		});
    
    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;

        if(this.type==3 || this.type==4){
          if(this.userdetails.resource_access == 1){
            this.addAdditionalInvoice = 1;
          }
          
          if(this.type == 3 && (this.userType == 3 || this.userdetails.resource_access==5)){
            this.addAdditionalInvoice = 1;
          }
          if( this.userType == 1 && this.userdetails.rules.includes('generate_invoice') ){
            if(this.type == 3){
              this.addAdditionalInvoice = 1;
            }
            if(this.type == 4 && this.userdetails.is_headquarters==1){
              this.addAdditionalInvoice = 1;
            }
            
          }
        }
        
      }
    });

    this.invoices$.subscribe(res=>{
      ///console.log(res.length);
      this.service.selInoviceIds = [];
      this.totalamountselected = 0;
      //this.addbulkAdditional=false;
    })
  }
    
  constructor(private activatedRoute:ActivatedRoute,private fb:FormBuilder,private modalService: NgbModal, public service: GenerateListService, private router: Router,private authservice:AuthenticationService,public errorSummary: ErrorSummaryService,private userservice: UserService) {
    this.invoices$ = service.invoices$;
    this.total$ = service.total$; 
	  this.invoiceamount$ = service.invoiceamount$;	
    this.router.routeReuseStrategy.shouldReuseRoute = () => false;
  }
  
  modalss:any;
  open(content,arg:number=0) {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    if(arg)
      this.invoice_id = arg;
    this.resetform();
  }
  
  
  downloadFile(invoicecode,id){
    this.service.downloadInvoiceFile({id})
    .subscribe(res => {
        this.modalss.close();
        saveAs(new Blob([res],{type:'application/pdf'}),'invoice_'+invoicecode+'.pdf');
    });    
  }
   
  

  onSort({column, direction}: SortEvent) {
    // resetting other headers
    
    this.headers.forEach(header => {
      if (header.sortable !== column) {
        header.direction = '';
      }
    });

    this.service.sortColumn = column;
    this.service.sortDirection = direction;
  }

  
  updatepaymentstatus()
  {
    this.f.payment_date.markAsTouched();
    this.f.payment_status.markAsTouched();
    this.f.payment_comment.markAsTouched();

    if(this.form.valid)
    {
      this.popupbtnDisable= true;

      let payment_date = this.form.get('payment_date').value;
      let payment_status = this.form.get('payment_status').value;
      let payment_comment = this.form.get('payment_comment').value;

      let datapost:any = {payment_date:this.errorSummary.displayDateFormat(payment_date),payment_status:payment_status,payment_comment:payment_comment};

      if(this.service.selInoviceIds.length>0)
      {
        datapost.id = this.service.selInoviceIds;
      }
      else
      {
        datapost.id = this.invoice_id;
      }
  
      
      this.service.updatePaymentStatus(datapost).subscribe(res => {
       
        if(res.status)
        {
          this.alertSuccessMessage = res.message;
          this.resetform();
          setTimeout(()=>{
            this.modalss.close('');
            this.alertSuccessMessage='';
            this.popupbtnDisable= false;
          },this.errorSummary.redirectTime);
          this.service.searchTerm=this.service.searchTerm;
        }
        else if(res.status == 0)
        {			
          this.alertErrorMessage = res.message;	
          this.popupbtnDisable= false;
        }
        else
        {
          this.alertErrorMessage = res.message;
          this.popupbtnDisable= false;
        }	
      });
    }
    else
    {
      this.popupbtnDisable = false;
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form);
    }
  }

  resetform()
  {
    this.form.reset();
    this.popupbtnDisable = false;

    this.form.patchValue({
      payment_date:'',
      payment_status:'',
      payment_comment:''
    });
  }

  get f() { return this.form.controls; }

  totalamountselected:any=0;
  bulkpaymentupdate(offerdata: any, isChecked: boolean)
  {
    
    if (isChecked) 
    {
      this.service.selInoviceIds.push(""+offerdata.id+"");
      this.totalamountselected = parseFloat(offerdata.total_payable_amount)+ parseFloat(this.totalamountselected);
      this.totalamountselected = Number(this.totalamountselected).toFixed(2);
    }
    else 
    {
      let indexsel = this.service.selInoviceIds.findIndex(x => x == ""+offerdata.id+"");
      if(indexsel !== -1){
        this.service.selInoviceIds.splice(indexsel,1);
        this.totalamountselected = parseFloat(this.totalamountselected) - parseFloat(offerdata.total_payable_amount);
        this.totalamountselected = Number(this.totalamountselected).toFixed(2);
      }
    }

    
  }

  addbulkAdditional()
  {
    if(this.service.selInoviceIds.length>0)
    {
      return true;
    }
    else 
    {
      return false;
    }
  }

}