import { Component, OnInit, Input } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { Application } from '@app/models/application/application';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';
import { AuthenticationService } from '@app/services';
import {Observable,Subject} from 'rxjs';
import { first, debounceTime, distinctUntilChanged, map,tap } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {saveAs} from 'file-saver';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import { NgForm } from '@angular/forms';

@Component({
  selector: 'app-application-product-detail-list',
  templateUrl: './application-product-detail-list.component.html',
  styleUrls: ['./application-product-detail-list.component.scss']
})
export class ApplicationProductDetailListComponent implements OnInit {

  @Input() id: number;
  @Input() standard_id: number;
  @Input() product_addition_id: any;
  app_id:number;
  loading:any=[];
  buttonDisable = false;
  applicationdata:Application;
  productListDetails:any=[];
  certificate_id:number;
  pageloading:any = false;
  constructor(private modalService: NgbModal,private router:Router,private activatedRoute:ActivatedRoute,public errorSummary:ErrorSummaryService,private applicationDetail:ApplicationDetailService) { }

  ngOnInit() {
     
    this.pageloading = true;
    this.certificate_id = this.activatedRoute.snapshot.queryParams.certificate_id;

    this.applicationDetail.getProductDetailsBasedOnStandard({product_addition_id:this.product_addition_id,id:this.id,standard_id:this.standard_id}).
    subscribe(res => {
      this.applicationdata = res;
      this.productListDetails = this.applicationdata.productDetails;
      this.pageloading = false;
    });  
  }

  selectedProductIds:any = [];
  selProductList:Array<any> = [];
  unselectedProductIds:any = [];
  
  onChange(id: any, isChecked: boolean) {
    //let productDetails = this.productListDetails.find(x => x.id == id);
    if (isChecked) 
    {
      this.selectedProductIds.push(""+id+"");
      
      let unindexsel = this.unselectedProductIds.findIndex(x => x == ""+id+"");
      if(unindexsel !== -1){
        this.unselectedProductIds.splice(unindexsel,1);
      }
      //this.selProductList.push({id:productDetails.id,name:productDetails.name,label_grade:productDetails.label_grade,label_grade_name:productDetails.label_grade_name,materialcompositionname:productDetails.materialcompositionname,standard_id:productDetails.standard_id,standard_name:productDetails.standard_name,wastage:productDetails.wastage});
    } 
    else 
    {

      this.unselectedProductIds.push(""+id+"");

      let indexsel = this.selectedProductIds.findIndex(x => x == ""+id+"");
      if(indexsel !== -1){
        this.selectedProductIds.splice(indexsel,1);
      }
      this.selProductList = this.selProductList.filter(x => x.id != id);
    }
    //console.log(this.selectedProductIds);
  }

  


  productsuccess:any;
  producterror:any;
  submittedloading:any = false;
  addUnitProductFromPop()
  { 
    let selectedProductIds = this.selectedProductIds;
    let unselectedProductIds = this.unselectedProductIds;
    
    let checkboxpdt:number = document.querySelectorAll('input[class=checkboxpdt]:checked').length
    //console.log(checkboxpdt);
    ///return false;
    if(checkboxpdt<=0){
      this.producterror = {summary:'Please select any product(s)'};
      setTimeout(()=>{ },this.errorSummary.errormessageTimeoutTime);
      return false;
    }
    this.submittedloading = true;
    this.applicationDetail.updateApplicationProductReviewer({product_addition_id:this.product_addition_id,certificate_id:this.certificate_id,selectedProductIds,unselectedProductIds,app_id:this.id}).
    subscribe(res => {
      if(res.status){
        this.selectedProductIds =[];
        this.unselectedProductIds =[];
        this.productsuccess = {summary:res.message};
        
        setTimeout(()=>{ },this.errorSummary.redirectTime);
      }else{
        this.producterror = {summary:res.message};
        setTimeout(()=>{ },this.errorSummary.redirectTime);
      }
      this.submittedloading = false;
      //this.applicationdata = res;
      ///this.productListDetails = this.applicationdata.productDetails;
    }); 
     		
	  //console.log(this.selectedProductIds);
  }

}
