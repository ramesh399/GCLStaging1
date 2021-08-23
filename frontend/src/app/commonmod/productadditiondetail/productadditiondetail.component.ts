import { Component, OnInit, Input } from '@angular/core';
import { AuditPlan } from '@app/models/audit/audit-plan';
import { AuditPlanService } from '@app/services/audit/audit-plan.service';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/services';
import { first, debounceTime, distinctUntilChanged, map,tap } from 'rxjs/operators';
import { ProductAdditionService } from '@app/services/change-scope/product-addition.service';

@Component({
  selector: 'app-productadditiondetail',
  templateUrl: './productadditiondetail.component.html',
  styleUrls: ['./productadditiondetail.component.scss']
})
export class ProductadditiondetailComponent implements OnInit {

  
  @Input() id:number;

  
  
  panelOpenState = false;
  childmodel:any = {user_bsector_group_id:''};
  detailForm : any = {};
  planloading:any;
   
  userType:number;
  userdetails:any;
  

  constructor(private modalService: NgbModal,private additionservice: ProductAdditionService,private authservice:AuthenticationService) { }

 	
    reviewerstatus:any = [];
    process_ids=[];
    buttonDisable = false;
    userdecoded:any;
    
    app_id:number;
    new_app_id:number;
    product_status:number;
    error:any;
    success:any;
    loading:any=[];
    reviewdata:any=[];
    applicationdata:any=[];
    
    approvalStatusList = [];//[{id:'1',name:'Accept'},{id:'2',name:'Reject'}];
     
    modalss:any;
    productEntries:any=[];
    processerror:any=[];
    units:any;
    
    review_comments:any;
    review_status = '';
    reviewForm : any = {};
   	
    resource_access:any;
    arrEnumStatus:any[];
    
     
    appdata:any=[];
     productdetails:any;
     unitProductList:any=[];
     unitstandard:any=[];
     selUnitStandardList:any=[];
     productListDetails:any=[];
     enumstatus:any=[]
    ngOnInit() {
     
      this.review_status = '';

      this.authservice.currentUser.subscribe(x => {
        if(x){
          let user = this.authservice.getDecodeToken();
          this.userType= user.decodedToken.user_type;
          this.userdetails= user.decodedToken;
          this.resource_access = this.userdetails.resource_access;
        }
      });

      

      this.additionservice.getAppData({id:this.id})
      .pipe(
      tap(res=>{
          this.product_status = res.productdetails.status;
          this.additionservice.getStatusList({status:this.product_status})
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
        this.productEntries = res.productdetails.products;
        this.reviewdata = res.reviewdetails;
        this.enumstatus = res.enumstatus;
        if(res.status)
        {
          this.appdata = res.appdata;
          this.applicationdata = res.appdetails;

          this.productdetails = res.productdetails;

         

        
          this.productListDetails = res.productdetails.productDetails;
          this.productEntries = res.productdetails.products;
          
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

          //this.getPdtStandardList(unitids);
        }else
        {           
          this.error = {summary:res};
        }
      },
      error => {
          this.error = error;
          this.loading.company= false;
      });

      
  
    }


}
