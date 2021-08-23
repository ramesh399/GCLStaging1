import { Component, OnInit, Input } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { FormGroup, FormBuilder, Validators, FormControl, FormArray, NgForm, NgControl, Form } from '@angular/forms';
import { GenerateDetailService } from '@app/services/offer/generate-detail.service';
import { Offer } from '@app/models/offer/offer';
import { Observable } from 'rxjs';
import { first } from 'rxjs/operators';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/services';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { saveAs } from 'file-saver';
import { Application } from '@app/models/application/application';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { environment } from '@environments/environment';

@Component({
  selector: 'app-offerdetail',
  templateUrl: './offerdetail.component.html',
  styleUrls: ['./offerdetail.component.scss']
})
export class OfferdetailComponent implements OnInit {

  constructor(public errorSummary:ErrorSummaryService,private fb:FormBuilder,private activatedRoute:ActivatedRoute,private generateDetail:GenerateDetailService, private modalService: NgbModal,private authservice:AuthenticationService,private applicationDetail:ApplicationDetailService) { }
  @Input() id: number;
  @Input() offer_id: number;
  @Input() display_type: string;
  offerdata:Offer;
  error = '';
  success = '';
  loading = false;
  userdecoded:any;
  is_headquarters:number;
  userType:number;
  userdetails:any;
  panelOpenState:any=false;
  weburl:any;
  ngOnInit() {
    const id = this.id?this.id:'';
    const offer_id = this.offer_id?this.offer_id:'';
    this.weburl = environment.apiUrl;
    const urldata = `id=${id}&offer_id=${offer_id}`;
    this.generateDetail.getOfferByGet(urldata).pipe(first())
    .subscribe(res => {
      this.offerdata = res;
    },
    error => {
        this.error = error;
        this.loading = false;
    });   
  
      
    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.is_headquarters = user.decodedToken.is_headquarters;
        this.userdetails= user.decodedToken;
      }
    });
  }

  modalss:any;
  openmodal(content,arg='') {
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

  downloadFile(app_id,offer_id){
    this.generateDetail.downloadOfferFile({app_id,offer_id})
    .subscribe(res => {
        this.modalss.close();
        saveAs(new Blob([res],{type:'application/pdf'}),'offer_'+offer_id+'.pdf');
    },
    error => {
        this.error = error;
        this.modalss.close();
    });
  }

}
