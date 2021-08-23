import { Component, OnInit, Input } from '@angular/core';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { DashboardService } from '@app/services/dashboard.service';
import { Application } from '@app/models/application/application';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-application-report-details',
  templateUrl: './application-report-details.component.html',
  styleUrls: ['./application-report-details.component.scss']
})
export class ApplicationReportDetailsComponent implements OnInit {

  @Input() userdecoded: any;
  @Input() app_id: number;
  panelOpenState = false;
  apploading = false;
  error:any;
  app_ids:any;
  application_status = true;
  offer_status = false;
  certificate_status = false;
  tc_status = false;

  constructor(private modalService: NgbModal, private applicationDetail:ApplicationDetailService, private service:DashboardService, private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
    this.apploading = true;
    this.service.getapp_id(this.app_id)
    .pipe(first())
    .subscribe(res => {
       this.apploading=false;
       this.app_ids = res.app_id;
    },
    error => {
        this.error = {summary:error};
        this.apploading=false;
    });
  }

  changeDashboardContent(arg)
  {
    this.application_status = false;
    this.offer_status = false;
    this.certificate_status = false;
    this.tc_status = false;

    if(arg=='application'){
      this.application_status = true;
    }else if(arg=='offer'){	
      this.offer_status = true;
    }else if(arg=='certificate'){
      this.certificate_status = true;
    }else if(arg=='tc'){
      this.tc_status = true;
    }
  }

}
