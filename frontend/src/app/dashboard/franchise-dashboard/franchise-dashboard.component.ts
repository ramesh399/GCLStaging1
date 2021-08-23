import { Component, OnInit } from '@angular/core';
import { DashboardService } from '@app/services/dashboard.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';

import * as Highcharts from 'highcharts';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-franchise-dashboard',
  templateUrl: './franchise-dashboard.component.html',
  styleUrls: ['./franchise-dashboard.component.scss']
})
export class FranchiseDashboardComponent implements OnInit {

  title = 'Dashboard';  
  loading = false;
  error:any;
  success:any;
  submittedError = false;
  dashboardData:any;
  dashboardCustomerData:any;
  dataLoading = false;

  constructor(private http: HttpClient,private router: Router,private dashboardService:DashboardService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
			
	this.dashboardService.getFranchiseData().pipe(first())
    .subscribe(res => {
    this.dashboardData = res['data'];
    
    this.dashboardCustomerData = res['customerdata'];	
    this.dataLoading = true;	
      },
      error => {
          this.error = error;
          this.loading = false;
      });	  
  }

  setStorageVal()
  {
	  localStorage.setItem('fromdashboard', '1');
  }

}
