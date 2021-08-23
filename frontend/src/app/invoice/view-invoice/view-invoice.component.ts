import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { GenerateDetailService } from '@app/services/invoice/generate-detail.service';
import { AuthenticationService } from '@app/services';
import { Invoice } from '@app/models/invoice/invoice';
import {Observable} from 'rxjs';
import { first } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import {GenerateListService} from '@app/services/invoice/generate-list.service';
import { environment } from '@environments/environment';
import {saveAs} from 'file-saver';

@Component({
  selector: 'app-view-invoice',
  templateUrl: './view-invoice.component.html',
  styleUrls: ['./view-invoice.component.scss']
})
export class ViewInvoiceComponent implements OnInit {

  constructor(private authservice:AuthenticationService,public service: GenerateListService,private activatedRoute:ActivatedRoute,private generateDetail:GenerateDetailService, private modalService: NgbModal,private fb:FormBuilder,private router:Router,private errorSummary: ErrorSummaryService) { }
  id:number;
  offer_id:number;
  error:any;
  success:any;
  loading = false;
  offerdata:Invoice; 
  modalss:any;  
  user_id_error = '';
  feesEntries = [];
  expensesEntries = [];
  currency_code = '';
  userType:number;
  userdetails:any;
  buttonDisable = false;
  invoiceForm : any = {payment_date:'',payment_status:''};
  payment_status='';
  paymentDate:any='';
  payment_comment = '';
  weburl:any;
  type:any;
  title:any;
  backLink:any;
  loadingdata:any;
  totalColSpan:number = 3;
  minDate: Date;
  invoiceTypeArray = {'1':'Customer Invoice List','2':'OSS Invoice List','3':'Customer Additional Invoice List','4':'OSS Additional Invoice List'};
  invoiceLinkArray = {'1':'customer-invoice-list','2':'oss-invoice-list','3':'customer-additional-invoice-list','4':'oss-additional-invoice-list'};
  ngOnInit() {
    this.minDate = new Date();
	  this.type = this.activatedRoute.snapshot.queryParams.type;		  
	  this.title = this.invoiceTypeArray[this.type];
	  this.backLink = this.invoiceLinkArray[this.type];
	  if(this.type ==3 || this.type ==4){
      this.totalColSpan = 2;
    }
	  this.weburl = environment.apiUrl;
	  this.id = this.activatedRoute.snapshot.queryParams.id;
      this.offer_id = this.activatedRoute.snapshot.queryParams.offer_id;
      
     	  
      this.loadInvoiceData();
      
      this.authservice.currentUser.subscribe(x => {
        if(x){
          let user = this.authservice.getDecodeToken();
          this.userType= user.decodedToken.user_type;
          this.userdetails= user.decodedToken;
        }
      });

  }  
  
  loadInvoiceData(){
    this.loadingdata = true;
    this.generateDetail.getInvoice({id:this.id}).pipe(first())
    .subscribe(res => {
      
      this.loadingdata = false;
      this.offerdata = res;		
      this.payment_status = res['paymentDetails']['payment_status_id']?res['paymentDetails']['payment_status_id']:'';
      this.payment_comment = res['paymentDetails']['payment_comment']?res['paymentDetails']['payment_comment']:'';
    },
    error => {
      this.error = error;
      this.loadingdata = false;
    });
  }
   open(content) {
     
    //, { centered: true }
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    
    this.modalss.result.then((result) => {
      //if(result =='finalize'){
        this.finalizeInvoice(result);
      //}else if(result =='reject'){
       // this.rejectInvoice();
      //}

      
      
    }, (reason) => {
      //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
    });
  }
  
  openModel(content,arg='') 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  comment_error =false;
  model:any = {comment:''};
  checkUserSel(type=''){
    
    if(type =='finalize'){
      this.modalss.close(type);
    }if(type =='approval'){
      this.modalss.close(type);
    }else if(type =='reject'){
      
      if(this.model.comment.trim() ==''){
        this.comment_error =true;
      }else{
        this.comment_error =false;
      }
      if(this.model.comment.trim()!=''){
        this.modalss.close(type);
      }
      
    }
  }
  /*rejectInvoice(){
    console.log(this.model.comment);
  }*/
  
  finalizeInvoice(result){
    //console.log('fff');
    //console.log({app_id:this.id,user_id:this.model.comment,status:this.model.status});
    
    this.loading  = true;
	
    this.generateDetail.approveApplication({invoice_id:this.id,comment:this.model.comment,offer_id:this.offer_id,invoicestatus:result})
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //this.applicationdata.status = res.app_status_name;
            //this.applicationdata.app_status = res.app_status;
            this.offerdata.invoice_status = res.invoice_status;
            this.success = {summary:res.message};
            this.loadInvoiceData();
          }else if(res.status == 0){
            this.error = {summary:res.message};
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

  payment_date_error = false;
  onSubmit(f:NgForm) 
  {
    this.payment_date_error = false;
    
    f.controls.payment_status.markAsTouched();
    f.controls.payment_date.markAsTouched();
    f.controls.payment_comment.markAsTouched();

    if(f.controls.payment_date.value =='')
    {
      this.payment_date_error = true;
    }

    if (f.valid && !this.payment_date_error) {
      let payment_date = this.errorSummary.displayDateFormat(f.controls.payment_date.value);
      let payment_status = f.controls.payment_status.value;
      let payment_comment = f.controls.payment_comment.value;
      
      let reviewdata={
        id:this.id,
        payment_date,
        payment_status,
        payment_comment,
      }
      //console.log(unit_review_comment);
      //return false;
      this.loading  = true;
      this.generateDetail.updatePayment(reviewdata)
      .pipe(first())
      .subscribe(res => {
            
          if(res.status==1){
              this.success = {summary:res.message};
              this.buttonDisable = true;
              setTimeout(() => {
                this.router.navigateByUrl('/invoice/'+this.backLink);
              }, this.errorSummary.redirectTime);
              
            }else if(res.status == 0){
              this.error = {summary:res.message};
            }else{
              this.error = {summary:res};
            }
            this.loading = false;
          
      },
      error => {
          this.error = {summary:error};
          this.loading = false;
      });
      
    } else {
      this.error = {summary:this.errorSummary.errorSummaryText};
    }
  }
  
  downloadFile(invoicecode,id){
    this.service.downloadInvoiceFile({id})
    .subscribe(res => {
        this.modalss.close();
        saveAs(new Blob([res],{type:'application/pdf'}),'invoice_'+invoicecode+'.pdf');
    });    
  }
  
}