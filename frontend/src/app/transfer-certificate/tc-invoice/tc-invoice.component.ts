import {DecimalPipe} from '@angular/common';
import {Directive, Component, QueryList, ViewChildren,OnInit } from '@angular/core';
import {Observable} from 'rxjs';

import { ActivatedRoute ,Params, Router } from '@angular/router';
import {Request} from '@app/models/transfer-certificate/request';
import { TcInvocieListService } from '@app/services/transfer-certificate/tc-invoice/tc-invoice-list.service';
import {NgbdSortableHeader, SortEvent, PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl } from '@angular/forms';
import { RequestListService } from '@app/services/transfer-certificate/request/request-list.service';

import { first } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';
import {saveAs} from 'file-saver';
import { User } from '@app/models/master/user';
import { UserService } from '@app/services/master/user/user.service';

@Component({
  selector: 'app-tc-invoice',
  templateUrl: './tc-invoice.component.html',
  styleUrls: ['./tc-invoice.component.scss'],
  providers: [TcInvocieListService, DecimalPipe]
})
export class TcInvoiceComponent implements OnInit {

  requests$: Observable<Request[]>;
  total$: Observable<number>;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  closeResult: string;
  invoiceForm : any = {};
  franchiseList:User[];
  modalOptions:NgbModalOptions;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  success:any;
  modalss:any;
  standardList:Standard[];
  
  model: any = {id:null,action:null};
  alertInfoMessage:any;
  alertSuccessMessage:any;
  alertErrorMessage:any;
  cancelBtn=true;
  okBtn=true;
  title = '';
  userType:number;
  userdetails:any;
  appdata:any=[];
  paymentstatuslist:any=[];
  userdecoded:any;
  type:number;

  constructor(private activatedRoute:ActivatedRoute,public service: TcInvocieListService,private modalService: NgbModal,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService,private standardservice: StandardService,private router:Router,private userservice: UserService,private requestservice: RequestListService) {
    this.requests$ = service.request$;
    this.total$ = service.total$;
    
    this.modalOptions = this.errorSummary.modalOptions;	

    
    this.type = this.activatedRoute.snapshot.data['pageType'];
    if(this.type == 1){
      this.title = 'TC for Bill Generation';
    }else{
      this.title = 'Bill Generated TC List';
    }

    this.authservice.currentUser.subscribe(x => {
      if(x){
        
         
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        
      }else{
        this.userdecoded=null;
      }
    });
  }
  arrEnumStatus:any;
  InvoiceOptions:any;
  ngOnInit() {
    this.service.getInvoiceTypes().pipe(first())
    .subscribe(res => {
      this.InvoiceOptions = res.optionlist;
    },
    error => {
        //this.error = {summary:error};
        //this.loading = false;
    });

    this.userservice.getAllUser({type:3}).pipe(first())
    .subscribe(res => {
      this.franchiseList = res.users;
    },
    error => {
      this.error = {summary:error};
    });
    
    this.requestservice.getAppData().pipe(first())
    .subscribe(res => {
      if(res.status)
      {
        this.appdata = res.appdata;   
        this.paymentstatuslist = res.paymentStatus;       
      }
    });
  }

  onSort({column, direction}: SortEvent) {
    // resetting other headers
    //console.log('sdfsdfdsf');
    this.headers.forEach(header => {
      if (header.sortable !== column) {
        header.direction = '';
      }
    });

    this.service.sortColumn = column;
    this.service.sortDirection = direction;
  }

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    this.alertInfoMessage = 'Are you sure, do you want to submit for bill generation?';
    
  }

  DownloadFile(val,tcfilename)
  {
    this.loading  = true;
    this.service.downloadFile({id:val})
     .pipe(first())
     .subscribe(res => {
      this.loading = false;
      this.modalss.close();
      saveAs(new Blob([res],{type:'application/pdf'}),tcfilename);
    },
    error => {
      this.error = error;
      this.loading = false;
      this.modalss.close();
    });
  }

  open(content,action,id) {
    this.model.id = id;	
    this.model.action = action;	
    this.cancelBtn=true;
    this.okBtn=true;
    this.alertErrorMessage = '';
    this.alertSuccessMessage = '';
    
    if(action=='activate'){		
       this.alertInfoMessage='Are you sure, do you want to activate?';
    }else if(action=='deactivate'){		
       this.alertInfoMessage='Are you sure, do you want to deactivate?';
    }else if(action=='delete'){		
       this.alertInfoMessage='Are you sure, do you want to delete?';	
    }else if(action=='copy'){   
       this.alertInfoMessage='Are you sure, do you want to clone this TC Request?';  
    }
    
    this.modalss = this.modalService.open(content, this.modalOptions);
      //this.modalService.open(content, this.modalOptions).result.then((result) => {
    
    this.modalss.result.then((result) => {	
        this.closeResult = `Closed with: ${result}`;	 
      }, (reason) => {
      this.model.id = null;  
      this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;	  
      });
  }

    private getDismissReason(reason: any): string {
      if (reason === ModalDismissReasons.ESC) {
        return 'by pressing ESC';
      } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
        return 'by clicking on a backdrop';
      } else {
        return  `with: ${reason}`;
      }
    }
    
    commonModalAction(){
      this.loading = true;
      this.alertInfoMessage = 'Processing ...';
      this.service.ChangeInvoiceStatus(this.service.selInoviceIds).subscribe(res => {
        this.alertInfoMessage = '';
        if(res.status)
        {
          this.alertSuccessMessage = res.message;
          
          this.service.customSearch();
          setTimeout(() => {					
            this.alertSuccessMessage = '';
            this.alertInfoMessage = '';
            this.loading = false;
            this.modalss.close();
					}, this.errorSummary.redirectTime);		
        }
        else if(res.status == 0){			
          this.alertErrorMessage = res.message;	
          this.loading = false;
        }else{
          this.alertErrorMessage = res.message;
          this.loading = false;
        }				
         
      },
      error => {
      this.alertErrorMessage = error;
      this.loading = false;    
      });
      
    }  
   
    commonUpdateData(actionStatus) {
      
    this.alertInfoMessage='Please wait. Your request is processing';
    this.service.commonActionData({id:this.model.id}).pipe(first())
      .subscribe(res => {
      this.model.id = null;
      this.model.action = null;
      this.cancelBtn=false;
      this.okBtn=false;
      
      if(res.status){
        this.alertInfoMessage='';
              this.alertSuccessMessage = res.message;
        setTimeout(()=>this.modalss.close('deactivate'),this.errorSummary.redirectTime);
        this.service.searchTerm=this.service.searchTerm;
          }else if(res.status == 0){			
        this.alertInfoMessage='';
              this.alertErrorMessage = res.message;	
        }else{
        this.alertInfoMessage='';
              this.alertErrorMessage = res.message;
      }				
      },
      error => {
      this.alertInfoMessage='';
          this.alertErrorMessage = error;
         
      });
    }

    clonerequestData() {
      
      this.alertInfoMessage='Please wait. Your request is processing';
      this.service.clonerequestData({id:this.model.id}).pipe(first())
        .subscribe(res => {
        this.model.id = null;
        this.model.action = null;
        this.cancelBtn=false;
        this.okBtn=false;
        
          if(res.status){
            this.alertInfoMessage='';
            this.alertSuccessMessage = res.message;
            setTimeout(()=>{
              this.modalss.close('');
              this.router.navigateByUrl('/transfer-certificate/request/edit?id='+res.newid); 
              },this.errorSummary.redirectTime);
            this.service.searchTerm=this.service.searchTerm;
          }else if(res.status == 0){      
            this.alertInfoMessage='';
            this.alertErrorMessage = res.message; 
          }else{
            this.alertInfoMessage='';
            this.alertErrorMessage = res.message;
        }       
        },
        error => {
          this.alertInfoMessage='';
          this.alertErrorMessage = error;
         
      });
    }  
   
    getSelectedValue(val)
    {
      return this.standardList.find(x=> x.id==val).code;    
    }
    
    resetBtn()
    {
    this.alertInfoMessage='';
    this.alertSuccessMessage='';
    this.alertErrorMessage='';
    this.cancelBtn=true;
    this.okBtn=true;
    }

    //selInoviceIds = [];
    bulkinvoice(invoice_id,val)
    {
      let indexsel = this.service.selInoviceIds.findIndex(x => x.id == invoice_id);
      if(val!='')
      {
        if(indexsel !== -1)
        {
          this.service.selInoviceIds[indexsel] = {id:invoice_id,value:val};
        }
        else
        {
          this.service.selInoviceIds.push({id:invoice_id,value:val});
        }
      }
      else
      {
        if(indexsel !== -1){
          this.service.selInoviceIds.splice(indexsel,1);
        }
      }

      
    }

    showBtn()
    {
      if(this.service.selInoviceIds.length>0 && this.type==1 && (this.userdetails.resource_access==1 || (this.userType==1 && this.userdetails.rules.includes('generate_tc_bill'))))
      {
        return true;
      }
      else 
      {
        return  false;
      }
    }

    onSubmit()
    {
      this.loading = true;

      this.service.ChangeInvoiceStatus(this.service.selInoviceIds).subscribe(res => {
       
        if(res.status)
        {
          this.success = {summary:res.message};
          this.loading = false;
          setTimeout(() => {					
						this.success = {summary:''};
						this.service.customSearch();
					}, this.errorSummary.redirectTime);		
        }
        else if(res.status == 0){			
          this.error = res.message;	
        }else{
          this.error = res.message;
        }				
        this.loading = false;
      },
      error => {
      this.error = error;
      this.loading = false;    
      });
      
    }

    getSelectedFranchiseValue(val)
    {
      return this.franchiseList.find(x=> x.id==val).osp_details;    
    }

    getSelectedCustomerValue(val)
    {
      return this.appdata.find(x=> x.id==val).company_name;    
    }

    getSelectedInvoiceValue(val)
    {
      return this.paymentstatuslist.find(x=> x.id==val).name;    
    }
    

}
