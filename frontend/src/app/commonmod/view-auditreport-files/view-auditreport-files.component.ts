import { Component, OnInit, Input } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { FormGroup, FormBuilder} from '@angular/forms';
import { GenerateDetailService } from '@app/services/offer/generate-detail.service';
import { Offer } from '@app/models/offer/offer';
import { first } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/services';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import {saveAs} from 'file-saver';
import { Application } from '@app/models/application/application';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';

@Component({
  selector: 'app-view-auditreport-files',
  templateUrl: './view-auditreport-files.component.html',
  styleUrls: ['./view-auditreport-files.component.scss']
})
export class ViewAuditreportFilesComponent implements OnInit {

  constructor(public errorSummary:ErrorSummaryService,private fb:FormBuilder,private activatedRoute:ActivatedRoute,private generateDetail:GenerateDetailService, private modalService: NgbModal,private authservice:AuthenticationService,private applicationDetail:ApplicationDetailService) { }

  @Input() id: number;
  @Input() offer_id: number;
  @Input() audit_id: number;
  error = '';
  success = '';
  loading = false;
  offerdata:any=[];
  confirmText = '';
  buttonDisable='';

  modals:any;
  model: any = {comments:''};
  modaltitle='';
  arg = '';
  comments_error = '';
  dataloaded = false;
  panelOpenState = false;
  offerenumstatus = '';
  userdecoded:any;
  is_headquarters:number;
  userType:number;
  userdetails:any;
  offerForm : FormGroup;
  auditreportForm : FormGroup;
  public validDocs = ['pdf','docx','doc','jpeg','jpg','png'];
  applicationdata:Application;
  quotation_file = '';
  
  processorAgreementFileList=[];
  applicableforms:any = [];
  apploaded:any=false;

  ngOnInit() {

    this.generateDetail.getAuditFiles({id:this.id,offer_id:this.offer_id,audit_id:this.audit_id}).pipe(first())
    .subscribe(res => {
      this.offerdata = res;
      
      if(this.offerdata && this.offerdata.quotation_file){
        this.quotation_file = this.offerdata.quotation_file;
      }

      if(this.offerdata && this.offerdata.processor_files){
        if(this.offerdata.processor_files.length>0){
          this.offerdata.processor_files.forEach(element => {
            this.processorfile[element.unit_id] = {name:element.processor_file};
          });
        }
      }
      this.dataloaded = true;

    },
    error => {
        this.error = error;
        this.loading = false;
    });   
  }

  modalss:any;
  openmodal(content,arg='') 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
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


  downloadUploadedProcessorFile(offerlist_id,unitid,fileid,filename){
    this.generateDetail.downloadUploadedProcessorFile({offerlist_id:offerlist_id,unit_id:unitid,file_id:fileid})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    
    });
  }

  processorfile = [];

}
