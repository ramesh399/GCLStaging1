import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';
import { CustomerReportService } from '@app/services/master/customer-report/customer-report.service';
import { GenerateDetailService } from '@app/services/offer/generate-detail.service';
import {ListOfferService} from '@app/services/offer/list-offer.service';
import {ListAuditPlanService} from '@app/services/audit/list-audit-plan.service';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import { Offer } from '@app/models/offer/offer';
import { Enquiry } from '@app/models/enquiry';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import { tap,first } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';

@Component({
  selector: 'app-customer-details',
  templateUrl: './customer-details.component.html',
  styleUrls: ['./customer-details.component.scss']
})
export class CustomerDetailsComponent implements OnInit {

  id:number;
  audit_id:number;
  offer_id:number;
  invoice_id:number;
  error:any;
  success:any;
  loading:any=[];
  offerdata:Offer;
  buttonDisable = false;
  panelOpenState = true;
  enquirydata:Enquiry;
  type:number;
  sel_franchise:number;
  fromdashboard:any;
  dashboardlink:any;
  applicationlist:any=[];
  offerlist:any=[];
  auditlist:any=[];
  arrEnumStatus:any;
  auditStatus:any;
  statuslist:any=[];

  userType:number;
  userdetails:any;
  userdecoded:any;

  customerList:User[];
  title = 'Customer Details';
  quotation_file = '';
  processorfile = [];

  constructor(private router: Router,private enquiryDetail:EnquiryDetailService, public offerservice: ListOfferService,public auditservice: ListAuditPlanService,private generateDetail:GenerateDetailService, private userservice: UserService,private activatedRoute:ActivatedRoute,private reportservice:CustomerReportService, private modalService: NgbModal,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {
    
    this.id = this.activatedRoute.snapshot.queryParams.id;

    

    this.reportservice.getAudit({id:this.id})
    .pipe(tap(res=>{
      if(res.status){
        
        this.offer_id = res.offer_id;

        this.generateDetail.getOffer({id:this.id,offer_id:this.offer_id}).pipe(first())
        .subscribe(res => {
          this.offerdata = res;

          //this.processorfile[stdqid]
          //quotation_file
          ///this.offerdata
          if(this.offerdata.offer && this.offerdata.offer.quotation_file){
            this.quotation_file = this.offerdata.offer.quotation_file;
          }

          if(this.offerdata.offer && this.offerdata.offer.processor_files){
            if(this.offerdata.offer.processor_files.length>0){
              this.offerdata.offer.processor_files.forEach(element => {
                this.processorfile[element.unit_id] = {name:element.processor_file};
              });
            }
          }

        },
        error => {
            this.error = error;
            this.loading = false;
        });   

      }else if(res.status == 0){
        this.error = {summary:res};
      }
    },first()))
    .subscribe(res => {

    
        if(res.status){
          this.audit_id = res.audit_id;
          this.invoice_id = res.invoice_id;
          
        }else if(res.status == 0){
          this.error = {summary:res};
        }
        
    },
    error => {
        this.error = {summary:error};
        this.loading['button'] = false;
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

  modalss:any;
  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  downloadCompanyFile(filename){
    this.enquiryDetail.downloadCompanyFile({id:this.id})
    .subscribe(res => {
      
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
      this.modalss.close('');
    });
  }


  downloadFile(fileid,filename,type){
    this.generateDetail.downloadUploadedFile({offerlist_id:fileid,type})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    
    });
  }

  downloadUploadedFile(fileid,filename,type){
    this.generateDetail.downloadUploadedFile({offerlist_id:fileid,type})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    
    });
  }
  
  downloadProcessorFile(offerlist_id,unitid,fileid,filename){
    this.generateDetail.downloadUploadedProcessorFile({offerlist_id:offerlist_id,unit_id:unitid,file_id:fileid})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    
    });
  }

  onSubmit() { }

}
