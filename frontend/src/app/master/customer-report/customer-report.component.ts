import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { CustomerReportService } from '@app/services/master/customer-report/customer-report.service';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import { Enquiry } from '@app/models/enquiry';
import {Observable} from 'rxjs';
import { tap,first } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';

@Component({
  selector: 'app-customer-report',
  templateUrl: './customer-report.component.html',
  styleUrls: ['./customer-report.component.scss']
})
export class CustomerReportComponent implements OnInit {

  id:number;
  customer_id:number;
  error:any;
  success:any;
  loading:any=[];
  buttonDisable = false;
  showDetails = false;
  enquirydata:Enquiry;
  type:number;
  sel_franchise:number;
  fromdashboard:any;
  dashboardlink:any;
  applicationlist:any=[];
  arrEnumStatus:any;
  statuslist:any=[];

  userType:number;
  userdetails:any;
  userdecoded:any;

  form : FormGroup; 
  customerList:User[];
  title = 'Customer Report';

  constructor(private router: Router,private applicationDetail:ApplicationDetailService, private userservice: UserService,private activatedRoute:ActivatedRoute,private fb:FormBuilder,private reportservice:CustomerReportService, private modalService: NgbModal,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {

    this.form = this.fb.group({	  
      customer_id:['',[Validators.required]]
    });

    this.userservice.getCustomer().pipe(first())
    .subscribe(res => {
      this.customerList = res.customers;
    },
    error => {
        this.error = {summary:error};
    });

    this.applicationDetail.getApplicationStatusList().pipe(first())
    .subscribe(res => {
      this.arrEnumStatus = res.enumstatus;
      this.statuslist = res.statuslist;
    },
    error => {
        //this.error = {summary:error};
        //this.loading = false;
    }); 

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

  get f() { return this.form.controls; }

  onSubmit()
  {
    if(this.form.valid)
    {
      
      this.customer_id = this.form.get('customer_id').value;

      if(1)
      {
        this.buttonDisable = true;
        this.loading['button'] = true;
        this.reportservice.getApplication({id:this.customer_id})
          .pipe(first())
          .subscribe(res => {

          
              if(res.status){
                this.applicationlist = res.applications;
                this.buttonDisable = false;
                this.showDetails = true;
              }else if(res.status == 0){
                this.error = {summary:res};
              }
              this.loading['button'] = false;
              this.buttonDisable = false;
          },
          error => {
              this.error = {summary:error};
              this.loading['button'] = false;
          });
      } else {
        
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.form); 
        
      }  
       
    }
  }

  
}
