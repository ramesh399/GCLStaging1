import { Component, OnInit } from '@angular/core';
import { DashboardService } from '@app/services/dashboard.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';

import * as Highcharts from 'highcharts';
import { HttpClient } from '@angular/common/http';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-customer-dashboard',
  templateUrl: './customer-dashboard.component.html',
  styleUrls: ['./customer-dashboard.component.scss']
})
export class CustomerDashboardComponent implements OnInit {
    
  title = 'Dashboard';  
  loading = false;
  error:any;
  success:any;
  submittedError = false;
  dashboardData:any;
  dataLoading = false;
  my_action_status=true;
  certificate_status=false;
  application_status=false;
  quotation_status=false;
  panelOpenState = false;
  

  constructor(private modalService: NgbModal,private http: HttpClient,private router: Router,private dashboardService:DashboardService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
			
	this.dashboardService.getCustomerData().pipe(first())
    .subscribe(res => {
		this.dashboardData = res['data'];	
		this.dataLoading = true;		
    },
    error => {
        this.error = error;
        this.loading = false;
    });	  
  }

  changeUserTab(arg)
  {
    this.my_action_status=false;
    this.certificate_status=false;
    this.application_status=false;
    this.quotation_status=false;

    if(arg=='my_action'){
      this.my_action_status=true;
    }else if(arg=='certificate'){
      this.certificate_status=true;
    }else if(arg=='application'){
      this.application_status=true;
    }else if(arg=='quotation'){
      this.quotation_status=true;
    }
  }

  modalss:any;
  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  downloadFile(fileid,filetype,filename){
    this.dashboardService.downloadFile({id:fileid,filetype})
    .subscribe(res => {
      this.modalss.close();
      
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }
}