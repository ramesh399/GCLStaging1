import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { Application } from '@app/models/application/application';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import { AuthenticationService } from '@app/services';
import {Observable,Subject} from 'rxjs';
import { first, debounceTime, distinctUntilChanged, map,tap } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {saveAs} from 'file-saver';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Process } from '@app/models/master/process';
import { NgForm } from '@angular/forms';
import { ProcessService } from '@app/services/master/process/process.service';
import { Country } from '@app/services/country';
import { State } from '@app/services/state';
import { CountryService } from '@app/services/country.service';
import { StandardService } from '@app/services/standard.service';

import { BusinessSector } from '@app/models/master/business-sector';
import { BusinessSectorService } from '@app/services/master/business-sector/business-sector.service';
import { WithdrawUnitService } from '@app/services/change-scope/withdraw-unit.service';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';

@Component({
  selector: 'app-view-withdraw-unit',
  templateUrl: './view-withdraw-unit.component.html',
  styleUrls: ['./view-withdraw-unit.component.scss']
})
export class ViewWithdrawUnitComponent implements OnInit {

  constructor(private enquiryDetail:EnquiryDetailService, private userservice: UserService,private activatedRoute:ActivatedRoute,
    private applicationDetail:ApplicationDetailService, private modalService: NgbModal
    ,private router:Router,private authservice:AuthenticationService,private errorSummary: ErrorSummaryService, private processService:ProcessService, private withdrawservice: WithdrawUnitService) {  }
  
    processList: Process[];
    unitprocessList:any = [];
    reviewerstatus:any = [];
    process_ids=[];
    buttonDisable = false;
    userdecoded:any;
    id:number;
    app_id:number;
    new_app_id:number;
    withdraw_status:number;
    error:any;
    success:any;
    loading:any=[];
    reviewdata:any=[];
    applicationdata:any=[];
    panelOpenState = true;
    approvalStatusList = [];//[{id:'1',name:'Accept'},{id:'2',name:'Reject'}];
    userList:User[];
    modalss:any;
    productEntries:any=[];
    processerror:any=[];
    units:any;
    model:any = {user_id:'',approver_user_id:'',status:'',comment:'',reject_comment:''};
    review_comments:any;
    review_status = '';
    reviewForm : any = {};
  
    userType:number;
    userdetails:any;
    resource_access:any;
    arrEnumStatus:any[];
    
    countryList:Country[];
    stateList:State[];
    bsectorList:BusinessSector[];
    appdata:any=[];
     unitwithdrawdetails:any;
     unitProductList:any=[];
     unitstandard:any=[];
     selUnitStandardList:any=[];
     productListDetails:any=[];
     enumstatus:any=[]
    ngOnInit() {
      this.app_id = this.activatedRoute.snapshot.queryParams.app;
      this.id = this.activatedRoute.snapshot.queryParams.id;
      this.review_status = '';

      this.authservice.currentUser.subscribe(x => {
        if(x){
          let user = this.authservice.getDecodeToken();
          this.userType= user.decodedToken.user_type;
          this.userdetails= user.decodedToken;
          this.resource_access = this.userdetails.resource_access;
        }
      });

      
      this.loadadditiondetails();
      
  
    }

    loadadditiondetails(){
      this.loading['data'] = true;
       this.withdrawservice.getAppData({id:this.id})
        .pipe(
        tap(res=>{
            this.withdraw_status = res.unitwithdrawdetails.status;
            this.withdrawservice.getStatusList({status:this.withdraw_status})
            .pipe(first())
            .subscribe(res => {
              this.reviewerstatus = res.data;
            },
            error => {
                this.error = {summary:error};
                this.loading['reviewstatus'] = false;
            });
        },
          first())
        ).subscribe(res => {
          this.productEntries = res.unitwithdrawdetails.products;
          this.reviewdata = res.reviewdetails;
          this.enumstatus = res.enumstatus;
          if(res.status)
          {
            this.appdata = res.appdata;
            this.applicationdata = res.appdetails;

            this.unitwithdrawdetails = res.unitwithdrawdetails;

                     
            //this.productListDetails = res.unitwithdrawdetails.unitwithdrawdetails;
            //this.productEntries = res.productdetails.products;
            
			/*
            let unitids = [];
             this.applicationdata.units.forEach(unit=>{
                
                this.selUnitStandardList[unit.id] = [];
                unitids.push(unit.id);
                if( res.units &&  res.units[unit.id] && res.units[unit.id].product_details){
                  this.unitProductList[unit.id] = [...res.units[unit.id].product_details];
                }else{
                  this.unitProductList[unit.id] = [];
                }
                let appstandards = unit.standards; 
                this.unitstandard[unit.id] = [...unit.standards];
                if(appstandards.length>0){
                  if( this.productListDetails){
                    let selunitlist = this.productListDetails.filter(x =>  appstandards.includes(x.standard_id));
                     
                    if(selunitlist){

                     this.selUnitStandardList[unit.id] = selunitlist;
                    }
                  }
                }
                

            })
			*/
			
             this.loading['data'] = false;
            //this.getPdtStandardList(unitids);
          }else
          {           
             this.loading['data'] = false;
            this.error = {summary:res};
          }
        },
        error => {
           this.loading['data'] = false;
            this.error = error;
            
        });
    }

    openmodal(content)
    {
      this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    }

    assignReviewer()
    {
      this.modalss.close('');
      this.loading['assignReviewer'] = true;
      this.withdrawservice.assignReviewer({id:this.id,app_id:this.app_id}).pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          //this.withdraw_status = res.withdraw_status;
         
          this.success = {summary:res.message};

           setTimeout(()=>{
           this.success = {summary:''};
            this.loading['assignReviewer'] = false;
            this.loadadditiondetails();
           },this.errorSummary.redirectTime);

          
        }
      },
      error => {
          this.error = error;
          this.loading['assignReviewer'] = false;
      });
    }
	
	assignCertificationReviewer()
    {
      this.modalss.close('');
      this.loading['assignReviewer'] = true;
      this.withdrawservice.assignCertificationReviewer({id:this.id,app_id:this.app_id}).pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.withdraw_status = res.withdraw_status;
          this.success = {summary:res.message};
          this.loading['assignReviewer'] = false;
        }
      },
      error => {
          this.error = error;
          this.loading['assignReviewer'] = false;
      });
    }
    

    onSubmit(f:NgForm) 
    {
      f.controls["review_status"].markAsTouched();
      f.controls["review_comments"].markAsTouched();

      if (f.valid) 
      {
        let reviewdata={
          id:this.id,
          status:f.value.review_status,
          comment:f.value.review_comments
        }
        

        this.loading['reviewstatus']  = true;

        if(this.withdraw_status==this.enumstatus['review_in_process'])
        {
          this.withdrawservice.addReviewerchecklist(reviewdata).pipe(first())
          .subscribe(res => {
            if(res.status)
            {
                this.success = {summary:res.message};
                this.buttonDisable = true;           
                setTimeout(() => {
                  this.router.navigateByUrl('/change-scope/withdraw-unit/list'); 
                }, this.errorSummary.redirectTime);       
            }else
            {			      
              this.error = {summary:res};
              this.loading['reviewstatus']  = false; 
            } 
                  
          },
          error => {
              this.error = {summary:error};
              this.loading['reviewstatus'] = false;
          });
        }
        else
        {
          this.withdrawservice.addOspchecklist(reviewdata).pipe(first())
          .subscribe(res => {
            if(res.status)
            {
                this.success = {summary:res.message};
                this.buttonDisable = true;           
                setTimeout(() => {
                  this.router.navigateByUrl('/change-scope/withdraw-unit/list'); 
                }, this.errorSummary.redirectTime);       
            }else
            {			      
              this.error = {summary:res};
              this.loading['reviewstatus']  = false; 
            } 
                  
          },
          error => {
              this.error = {summary:error};
              this.loading['reviewstatus'] = false;
          });
        }
        
      }
    }

  


}

