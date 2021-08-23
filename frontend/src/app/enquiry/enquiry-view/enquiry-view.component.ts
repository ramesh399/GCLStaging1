import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import { Enquiry } from '@app/models/enquiry';
import {Observable} from 'rxjs';
import { tap,first } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';

@Component({
  selector: 'app-enquiry-view',
  templateUrl: './enquiry-view.component.html',
  styleUrls: ['./enquiry-view.component.css']
})
export class EnquiryViewComponent implements OnInit {
  model: any = {franchise_id:null,customer_id:null};
  franchiseList:User[];
  customerList:User[];

  constructor(private router: Router,private userservice: UserService,private activatedRoute:ActivatedRoute,private enquiryDetail:EnquiryDetailService, private modalService: NgbModal,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }
  id:number;
  error:any;
  success:any;
  loading = false;
  enquirydata:Enquiry;
  type:number;
  sel_franchise:number;
  fromdashboard:any;
  dashboardlink:any;

  userType:number;
  userdetails:any;
  userdecoded:any;
  
  ngOnInit() {

    this.model.sel_franchise=1;
    this.type = this.activatedRoute.snapshot.queryParams.type;

    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.enquiryDetail.getEnquiry(this.id).pipe(first())
    .subscribe(res => {
      this.enquirydata = res;
    },
    error => {
        this.error = {summary:error};
        this.loading = false;
    });


    this.userservice.getAllUser({type:2}).pipe(first())
    .subscribe(res => {
      this.customerList = res.users;
    },
    error => {
        this.error = {summary:error};
    });

    this.userservice.getAllUser({type:3}).pipe(first())
    .subscribe(res => {
      this.franchiseList = res.users;
    },
    error => {
        this.error = {summary:error};
    });

    this.authservice.currentUser.subscribe(x => {
      if(x)
      {  
        let user = this.authservice.getDecodeToken();
        this.userType = user.decodedToken.user_type;
        this.userdetails = user.decodedToken;

        this.fromdashboard = localStorage.getItem('fromdashboard');
        if(this.userType == 1)
        {
          this.dashboardlink = '/user/dashboard';
        }
        else
        {
          this.dashboardlink = '/franchise/dashboard';
        }
        
        
      }else{
        this.userdecoded=null;
      }
    });
    
    

     
  }

  clrStorageVal()
  {
	  localStorage.removeItem('fromdashboard');
  }

  logForm(val) {
    //console.log(JSON.stringify(this.model));
  }

  checkUserSel(){
    if(this.model.customer_id =='' || this.model.customer_id==null){
      this.customer_id_error ='true';
    }else{
      this.modalss.close('Save');
    }
  }
  closeResult = '';
  sel_franchise_error = '';
  franchise_id_error = '';
  customer_id_error='';
  

  modalss:any;
  open(content,arg='') {
    this.sel_franchise_error = '';
    this.franchise_id_error = '';
    if(arg!='archive')
    {
      if(this.model.sel_franchise==undefined || this.model.sel_franchise=='' ){
        this.sel_franchise_error = 'true';
        return false;
      }
      
      if(this.model.sel_franchise=='1' && (this.model.franchise_id =='' || this.model.franchise_id ==null )){
        this.franchise_id_error = 'true';
        return false;
      }
    }	    
    
    //, { centered: true }
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    
    this.modalss.result.then((result) => {
      if(result =='Create'){
        this.addCustomer();
      }else  if(result =='Save'){
        this.addToExisting();
      }else  if(result =='Archive'){
        this.archiveEnquiry();
      }
      
    }, (reason) => {
      //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
    });
  }
  //modal.close('Save')

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return  `with: ${reason}`;
    }
  }


  archiveEnquiry(){
    this.loading  = true;
    this.enquiryDetail.archiveEnquiry(this.id)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status){
            this.enquirydata.status = res.enquirystatus;
            this.enquirydata.status_updated_date = res.status_updated_date;
            //this.enquirydata.status_updated_by = res.status_update_by;
            this.success = {summary:res.message};
            setTimeout(()=>this.router.navigate(['/enquiry/list']),this.errorSummary.redirectTime);
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

  updateEnquiryDetails(){
    this.enquiryDetail.getEnquiry(this.id).pipe(first())
    .subscribe(res => {
      this.enquirydata = res;
    },
    error => {
        this.error = {summary:error};
        this.loading = false;
    });
  }
  addToExisting(){
    this.loading  = true;
    this.enquiryDetail.addExistingCustomer({id:this.id,franchise_id:this.model.franchise_id,sel_franchise:this.model.sel_franchise,customer_id:this.model.customer_id})
     .pipe(first(),tap(res=>this.updateEnquiryDetails()))
     .subscribe(res => {
           
          if(res.status){
            this.success = {summary:res.message};
            setTimeout(()=>this.router.navigate(['/enquiry/list']),this.errorSummary.redirectTime);
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


  addCustomer(){
	   this.loading  = true;
	  this.enquiryDetail.addCustomer({id:this.id,franchise_id:this.model.franchise_id,sel_franchise:1})
      .pipe(first(),tap(res=>this.updateEnquiryDetails()))
      .subscribe(res => {
		  		  
          if(res.status){
            this.success = {summary:res.message};
            setTimeout(()=>this.router.navigate(['/enquiry/list']),this.errorSummary.redirectTime);
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
}
