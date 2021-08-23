import { Component, OnInit, ViewChild } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl, FormArray, NgForm, NgControl, Form } from '@angular/forms';
import { AuthenticationService } from '@app/services/authentication.service';
import { BuyerListService } from '@app/services/transfer-certificate/buyer/buyer-list.service';
import { InspectionBodyListService } from '@app/services/transfer-certificate/inspection-body/inspection-body-list.service';
import { RequestListService } from '@app/services/transfer-certificate/request/request-list.service';
import { RawMaterialListService } from '@app/services/transfer-certificate/raw-material/raw-material-list.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Router } from '@angular/router';
import { Request } from '@app/models/transfer-certificate/request';
import {saveAs} from 'file-saver';
import { NgbdSortableHeader, SortEvent,PaginationList,commontxt } from '@app/helpers/sortable.directive';

import { first,tap } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import { BrandService } from '@app/services/master/brand/brand.service';

@Component({
  selector: 'app-view-request',
  templateUrl: './view-request.component.html',
  styleUrls: ['./view-request.component.scss']
})
export class ViewRequestComponent implements OnInit {

 title = 'TC Application';	
  form : FormGroup;
  productForm : FormGroup;
  inputForm : FormGroup;
  formData:FormData = new FormData();
  loading:any={};
  buttonDisable = false;
  id:number;
  error:any;

  success:any;
  alertSuccessMessage:any;
  buyerlist:any=[];
  reviewdata:any=[];
  reviewerstatus:any=[];
  sellerlist:any=[];
  inspectionlist:any=[];
  certificationlist:any=[];
  consigneelist:any=[];
  transportlist:any=[];
  standardlist:any=[];
  requestdata:any=[];
  resultdata:any=[];
  appdata:any=[];
  unitlist:any=[];
  request_status:number;
  review_comments:any;
  declaration_comments:any;
  additional_comments:any;
  standard_declaration:any;
  review_status = '';
  reviewForm : any = {};
  declarationForm : any = {};
  modalOptions:NgbModalOptions;

  descriptionErrors = '';
  userType:number;
  userdetails:any;
  userdecoded:any;
  resource_access:any;
  

  tc_request_view = true;
  tc_request_edit = false;
  tc_product_view = false;
  tc_product_edit = false;
  
  modalss:any;
  productEntries:any=[];
  inputEntries:any=[];
  model: any = {id:null,action:null};
  viewproductData:any=[];
  viewinputmaterialdata:any=[];
  inputMaterialForm : any = {};
  inputmaterialweightlist=[];
  reviewer_details:any=[];
  remainingWeightError:any;
  remainingWeightSuccess:any;
  commontxt = commontxt;
  enableSumbitforApprovalButton = false;
  arrEnumStatus:any[];
  enumstatus:any=[];
  panelOpenState:any = true;  


  alertInfoMessage:any;
  alertErrorMessage:any;
  cancelBtn=true;
  okBtn=true;

  type:number;

  constructor(public brandService: BrandService,public service: RawMaterialListService, private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private buyerservice: BuyerListService,private inspectionservice: InspectionBodyListService,private requestservice: RequestListService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.type = this.activatedRoute.snapshot.queryParams.type;
    this.loading['status'] = true;
    this.review_status = '';
    this.declaration_comments = '';
    this.additional_comments = '';
    this.standard_declaration = '';
    if(this.id)
    {
     this.getRequestData();
    }

    this.authservice.currentUser.subscribe(x => {
			if(x){
				let user = this.authservice.getDecodeToken();
				this.userType= user.decodedToken.user_type;
				this.userdetails= user.decodedToken;
        this.resource_access = this.userdetails.resource_access;
			}else{
				this.userdecoded=null;
			}
    });  

  }
  overall_input_status:any;
  getRequestData()
  {
  this.resultdata = [];
    this.requestservice.getData({id:this.id})
      .pipe(
        tap(res=>{
          this.request_status = res.data.requestdata.request_status;
          this.requestservice.getStatusList({status:this.request_status, fromOSS: (this.userType == 3 || this.userdetails.role_name == 'TC Reviewer')})
          .pipe(first())
          .subscribe(res => {
            this.reviewerstatus = res.data;
            this.loading['status'] = false;
          },
          error => {
              this.error = {summary:error};
              this.loading['reviewstatus'] = false;
          });
        },
        first())
      ).subscribe(res => {
        let result = res.data;
        this.resultdata=res.data;
        this.reviewdata = res.reviewdetails;
        this.enumstatus = res.data.enumstatus;
        this.overall_input_status = this.resultdata.requestdata.overall_input_status;

        this.declaration_comments = this.resultdata.requestdata.declaration;
        this.additional_comments = this.resultdata.requestdata.additional_declaration;
        this.standard_declaration = this.resultdata.requestdata.standard_declaration;
        
        this.loading['assignReviewer'] = false;
        this.loading['reviewstatus'] = false;
      },
      error => {
          this.error = error;
          
      });
  }

  view(content)
  {
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }
  viewProduct(content,data)
  {
    this.viewproductData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  } 

  openmodal(content)
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  downloadEvidenceFile(fileid='',filetype='',filename='')
  {
    this.requestservice.downloadEvidenceFile({id:fileid,filetype})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }
  downloadRawmaterialFile(fileid='',filetype='',filename='')
  {
    this.service.downloadFile({id:fileid,filetype:filetype})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }
  assignReviewer()
  {
    this.modalss.close('');
    this.loading['assignReviewer'] = true;
    this.requestservice.assignReviewer({id:this.id}).pipe(first())
    .subscribe(res => {
      if(res.status)
      {
       
       
        this.success = {summary:res.message};

         setTimeout(()=>{
          this.getRequestData();
         this.success = {summary:''};
         // this.loading['assignReviewer'] = false;
          //this.loadadditiondetails();
         },this.errorSummary.redirectTime);

        
      }
    },
    error => {
        this.error = error;
        this.loading['assignReviewer'] = false;
    });
  }

  onSubmit(f:NgForm,type) 
  {

    if(type=='review')
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
  
        if(this.request_status==this.enumstatus['review_in_process'])
        {
          this.requestservice.addReviewerchecklist(reviewdata).pipe(first())
          .subscribe(res => {
            if(res.status)
            {
                this.success = {summary:res.message};  				
                setTimeout(() => {
                  this.loading['reviewstatus']  = false; 
                  this.buttonDisable = false;  
                  this.getRequestData();
                  // this.router.navigateByUrl('/transaction-certificate/request/list'); 
                  this.resetStatusDetails();
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
          this.requestservice.addOspchecklist(reviewdata).pipe(first())
          .subscribe(res => {
            if(res.status)
            {
                this.success = {summary:res.message};              
                setTimeout(() => {
                  this.loading['reviewstatus']  = false; 
                  this.buttonDisable = false;           
                  this.getRequestData();
                  this.resetStatusDetails();
                  //this.router.navigateByUrl('/transaction-certificate/request/list'); 
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
    else
    {
      f.controls["declaration_comments"].setValidators([Validators.required,Validators.maxLength(588)]);
	  f.controls["declaration_comments"].updateValueAndValidity();
    
    if(this.resultdata.requestdata.show_additional_declaration){
      f.controls["additional_comments"].setValidators([Validators.required,Validators.maxLength(310)]);
      f.controls["additional_comments"].updateValueAndValidity();
      f.controls["additional_comments"].markAsTouched();
    }
	  
	  
	  f.controls["declaration_comments"].markAsTouched();
      
      f.controls["standard_declaration"].markAsTouched();
      
      if (f.valid) 
      {
        let additional_comments:any='';
        if(this.resultdata.requestdata.show_additional_declaration){
          additional_comments = f.value.additional_comments;
        }
        let declarationdata={
          id:this.id,
          declaration:f.value.declaration_comments,
          additional_declaration: additional_comments,
          standard_declaration:f.value.standard_declaration
        }
        
  
        this.loading['declaration']  = true;

        this.requestservice.addDeclaration(declarationdata).pipe(first())
        .subscribe(res => {
          if(res.status)
          {
              this.success = {summary:res.message};              
              setTimeout(() => {
              this.loading['declaration']  = false; 
              this.buttonDisable = false ;  
              this.getRequestData(); 
              }, this.errorSummary.redirectTime);       
          }
          else
          {			      
            this.error = {summary:res};
            this.loading['declaration']  = false; 
          } 
                
        },
        error => {
            this.error = {summary:error};
            this.loading['declaration'] = false;
        });
      }
    }
    
  }
  
  downloadblFile(fileid='',filetype='',filename='')
  {
    this.requestservice.downloadBLFile({id:fileid,filetype})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }

  DownloadFile(val,tcfilename)
  {
    //this.loading  = true;
    this.requestservice.downloadFile({id:val})
     .pipe(first())
     .subscribe(res => {
      //this.loading = false;
      this.modalss.close();
      saveAs(new Blob([res],{type:'application/pdf'}),tcfilename);
    },
    error => {
      this.error = error;
      //this.loading = false;
      this.modalss.close();
    });
  }


  open(content,action,id) {
    this.model.id = id; 
    this.model.action = action; 
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
          
      }, (reason) => {
        this.model.id = null;  
         
      });
  }

    
    
    commonModalAction(){
      let reason = this.model.action; 
      
      let actionStatus=0;
      if(reason=='activate'){   
        actionStatus=0;
      }else if(reason=='deactivate'){   
        actionStatus=1;
      }else if(reason=='delete'){   
        actionStatus=2; 
      }

      if(reason=='copy'){
        this.clonerequestData();
      }else{
        this.commonUpdateData(actionStatus);
      }
      
    }  
   
    commonUpdateData(actionStatus) {
      
    this.alertInfoMessage='Please wait. Your request is processing';
    this.requestservice.commonActionData({id:this.model.id}).pipe(first())
      .subscribe(res => {
      this.model.id = null;
      this.model.action = null;
      this.cancelBtn=false;
      this.okBtn=false;
      
      if(res.status){
        this.alertInfoMessage='';
              this.alertSuccessMessage = res.message;
        setTimeout(()=>this.modalss.close('deactivate'),this.errorSummary.redirectTime);
        this.requestservice.searchTerm=this.requestservice.searchTerm;
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

    resetStatusDetails(){
      this.review_status = '';
      this.review_comments = '';
    }
    clonerequestData() {
      
      this.alertInfoMessage='Please wait. Your request is processing';
      this.requestservice.clonerequestData({id:this.model.id}).pipe(first())
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
            this.router.navigateByUrl('/transaction-certificate/request/edit?id='+res.newid); 
            },this.errorSummary.redirectTime);
             
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
}
