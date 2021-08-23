import { Component, OnInit } from '@angular/core';
import { DashboardService } from '@app/services/dashboard.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';

import * as Highcharts from 'highcharts';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-user-dashboard',
  templateUrl: './user-dashboard.component.html',
  styleUrls: ['./user-dashboard.component.scss']
})
export class UserDashboardComponent implements OnInit {

  
  Highcharts = Highcharts;
  
  /*
  chartOptions = {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: ''
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: false
            },
            showInLegend: true
        }
    },
    series: [{
        name: 'Brands',
        colorByPoint: true,
        data: [{
            name: 'Chrome',
            y: 61.41,
            sliced: true,
            selected: true
        }, {
            name: 'Internet Explorer',
            y: 11.84
        }, {
            name: 'Firefox',
            y: 10.85
        }, {
            name: 'Edge',
            y: 4.67
        }, {
            name: 'Safari',
            y: 4.18
        }, {
            name: 'Other',
            y: 7.05
        }]
    }]
  };
  */
  
  
  title = 'Dashboard';  
  loading = false;
  error:any;
  app_id:number;
  success:any;
  submittedError = false;
  dashboardData:any;
  dashboardChartData:any;
  enquiryChartOptions:any;
  applicationChartOptions:any;
  monthWisEnquiryChartOptions:any;
  monthWisOfferAmountChartOptions:any;
  contractChartOptions:any;
  invoiceChartOptions:any;
  overview_status = true;
  offer_status = false;
  enquiry_status = false;
  pending_users_status = false;
  pending_declaration_approval = false;
  pending_bgroup_approval = false;
  pending_standard_approval = false;
  
  renewal_audit_status = false;
  due_certificate_status = false;
  nc_due_status = false;
  auditChartOptions:any;
  certificationChartOptions:any;
  tcChartOptions:any;
  monthWisTCChartOptions:any;
  
  renewalAuditChartOptions:any;
  dueCertificatetcChartOptions:any;
  pending_actions_status=false;
  pending_actions_btn_status=false;
    
  constructor(private modalService: NgbModal, private http: HttpClient,private router: Router,private dashboardService:DashboardService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
			
	this.dashboardService.getUserData().pipe(first())
    .subscribe(res => {
		this.dashboardData = res['data'];
		//console.log(this.dashboardData);
		
		this.dashboardChartData = res['chartdata'];
		//console.log(this.dashboardChartData);
		
		if(this.dashboardChartData.pending_actions_status)
		{		
			this.pending_actions_status=true;
		}
		if(this.dashboardChartData.pending_actions_btn_status)
		{		
			this.pending_actions_btn_status=true;
		}
		if(this.dashboardChartData['enquiry-count']!==undefined)
		{
			this.enquiryChartOptions = {
				htmlOptions: {
				   style: {
					 width:'50%',
					 height:'20%',
					 
				   }
				 },	
				 chart: {
					type: 'pie',		
					options3d: {
						enabled: true,
						alpha: 45,
						beta: 0
					},
					align: 'left',
					spacingLeft:0,
					spacingRight:0,
					
				},
				legend: {
					spacingBottom:0,
					spacingLeft:0,
					spacingRight:0,
					spacingTop:0,
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'top',
					userHTML: true,
					y:30,
					width:210,		
					symbolHeight: 18,
					symbolWidth: 18,
					symbolRadius: 0,
					itemMarginTop: 7,
					itemMarginBottom: 7,
					floating:false,
					enabled: true,		
					padding: 0,
					itemStyle: { //I even tried this but with no lick
						textAlign: 'right',
					}
				},
				title: {
					text: this.dashboardChartData['total-enquiry-count'],
					align: 'center',
					verticalAlign: 'middle',					
					width:225,
					y:18,
					color: '#666666',
					fontFamily: 'Arial, sans-serif',
					fontSize:'12px'
				},
				tooltip: {enabled: false}, 

				exporting: {enabled: false},

				credits: {enabled: false},
				
				plotOptions: {
					pie: {
						//allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						showInLegend: true,
						innerSize: 125,
						depth: 45
					}
				},
				series: [{
					name: 'Brands',
					colorByPoint: true,
					data: this.dashboardChartData['enquiry-count'],
					//colors: ['#4572A7', '#89A54E', '#AA4643', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					colors: ['#4572A7', '#89A54E', '#ff0000', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					
				}]
			};
		}	
		
		if(this.dashboardChartData['application-count']!==undefined)
		{
			this.applicationChartOptions = {
				htmlOptions: {
				   style: {
					 width:'50%',
					 height:'20%',
					 
				   }
				 },	
				 chart: {
					type: 'pie',		
					options3d: {
						enabled: true,
						alpha: 45,
						beta: 0
					},
					align: 'left',
					spacingLeft:0,
					spacingRight:0,
					
				},
				legend: {
					spacingBottom:0,
					spacingLeft:0,
					spacingRight:0,
					spacingTop:0,
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'top',
					userHTML: true,
					y:0,
					width:210,		
					symbolHeight: 18,
					symbolWidth: 18,
					symbolRadius: 0,
					itemMarginTop: 7,
					itemMarginBottom: 7,
					floating:false,
					enabled: true,		
					padding: 0,
					itemStyle: { //I even tried this but with no lick
						textAlign: 'right',
					}
				},
				title: {
					text: this.dashboardChartData['total-application-count'],
					align: 'center',
					verticalAlign: 'middle',					
					width:225,
					y:18,
					color: '#666666',
					fontFamily: 'Arial, sans-serif',
					fontSize:'12px'
				},
				tooltip: {enabled: false}, 

				exporting: {enabled: false},

				credits: {enabled: false},
				
				plotOptions: {
					pie: {
						//allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						showInLegend: true,
						innerSize: 125,
						depth: 45
					}
				},
				series: [{
					name: 'Brands',
					colorByPoint: true,
					data: this.dashboardChartData['application-count'],
					//colors: ['#4572A7', '#89A54E', '#AA4643', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					colors: ['#4572A7', '#DB843D', '#3D96AE', '#80699B', '#89A54E', '#f15c80', '#ff0000', '#A47D7C', '#ff0000'],
					
					//colors: ['#7cb5ec', '#90ed7d', '#f15c80', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					
				}]
			};
		}
		
		if(this.dashboardChartData['contract-count']!==undefined)
		{
			this.contractChartOptions = {
				htmlOptions: {
				   style: {
					 width:'50%',
					 height:'20%',
					 
				   }
				 },	
				 chart: {
					type: 'pie',		
					options3d: {
						enabled: true,
						alpha: 45,
						beta: 0
					},
					align: 'left',
					spacingLeft:0,
					spacingRight:0,
					
				},
				
				legend: {
					spacingBottom:0,
					spacingLeft:0,
					spacingRight:0,
					spacingTop:0,
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'top',
					userHTML: true,
					y:30,
					width:210,		
					symbolHeight: 18,
					symbolWidth: 18,
					symbolRadius: 0,
					itemMarginTop: 7,
					itemMarginBottom: 7,
					floating:false,
					enabled: true,		
					padding: 0,
					itemStyle: { //I even tried this but with no lick
						textAlign: 'right',
					}
				},
				title: {
					text: this.dashboardChartData['total-contract-count'],
					align: 'center',
					verticalAlign: 'middle',					
					width:225,
					y:18,
					color: '#666666',
					fontFamily: 'Arial, sans-serif',
					fontSize:'12px'
				},
				tooltip: {enabled: false}, 

				exporting: {enabled: false},

				credits: {enabled: false},
				
				plotOptions: {
					pie: {
						//allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						showInLegend: true,
						innerSize: 125,
						depth: 45
					}
				},
				series: [{
					name: 'Brands',
					colorByPoint: true,
					data: this.dashboardChartData['contract-count'],
					//colors: ['#4572A7', '#89A54E', '#AA4643', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					//colors: ['#7cb5ec', '#90ed7d', '#f15c80', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					colors: ['#4572A7', '#89A54E', '#ff0000', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],				
					
					
				}]
			};
		}
		
		if(this.dashboardChartData['invoice-count']!==undefined)
		{
			this.invoiceChartOptions = {
				htmlOptions: {
					style: {
						width:'50%',
						height:'20%',				 
					}
				 },	
				 chart: {
					type: 'pie',		
					options3d: {
						enabled: true,
						alpha: 45,
						beta: 0
					},
					align: 'left',
					spacingLeft:0,
					spacingRight:0,
					
				},
				legend: {
					spacingBottom:0,
					spacingLeft:0,
					spacingRight:0,
					spacingTop:0,
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'top',
					userHTML: true,
					y:30,
					width:210,		
					symbolHeight: 18,
					symbolWidth: 18,
					symbolRadius: 0,
					itemMarginTop: 7,
					itemMarginBottom: 7,
					floating:false,
					enabled: true,		
					padding: 0,
					itemStyle: { //I even tried this but with no lick
						textAlign: 'right',
					}
				},
				title: {
					text: this.dashboardChartData['total-invoice-count'],
					align: 'center',
					verticalAlign: 'middle',					
					width:225,
					y:18,
					color: '#666666',
					fontFamily: 'Arial, sans-serif',
					fontSize:'12px'
				},
				tooltip: {enabled: false}, 

				exporting: {enabled: false},

				credits: {enabled: false},
				
				plotOptions: {
					pie: {
						//allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						showInLegend: true,
						innerSize: 125,
						depth: 45
					}
				},
				series: [{
					name: 'Brands',
					colorByPoint: true,
					data: this.dashboardChartData['invoice-count'],
					//colors: ['#4572A7', '#89A54E', '#AA4643', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					colors: ['#4572A7', '#ff0000', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					
				}]
			};
		}
		
		if(this.dashboardChartData['monthwise_enquiries']!==undefined)
		{			
			this.monthWisEnquiryChartOptions = {
				chart: {
					inverted: false,
					polar: false				
				},			
				title: {
					text: ''
				},
				tooltip: {enabled: false}, 

				exporting: {enabled: false},

				credits: {enabled: false},

				plotOptions: {
					series: {
						pointWidth: 45
					}
				},
					
				xAxis: {
					categories: this.dashboardChartData['monthwise_enquiries']['categories'],
					labels: {
						style: {
							color: '#666666',
							fontFamily: 'Arial, sans-serif',
							fontSize:'12px'
						}
					}
				},
				
				yAxis: {
					allowDecimals: false,
					min: 0,
					title: {
						text: 'Number of Enquiries',
						style: {
							color: '#666666',
							fontFamily: 'Arial, sans-serif',
							fontSize:'12px'
						}
					}
				},

				series: [{
					type: 'column',
					colorByPoint: true,
					data: this.dashboardChartData['monthwise_enquiries']['data'],
					showInLegend: false
				}]
			};
		}
		
		
		
		
		
		
		
		
		if(this.dashboardChartData['audit-count']!==undefined)
		{
			this.auditChartOptions = {
				htmlOptions: {
				   style: {
					 width:'50%',
					 height:'20%',
					 
				   }
				 },	
				 chart: {
					type: 'pie',		
					options3d: {
						enabled: true,
						alpha: 45,
						beta: 0
					},
					align: 'left',
					spacingLeft:0,
					spacingRight:0,
					
				},
				
				title: {
					text: this.dashboardChartData['total-audit-count'],
					align: 'center',
					verticalAlign: 'middle',					
					width:225,
					y:18,
					color: '#666666',
					fontFamily: 'Arial, sans-serif',
					fontSize:'12px'
				},
				
				legend: {
					spacingBottom:0,
					spacingLeft:0,
					spacingRight:0,
					spacingTop:0,
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'top',
					userHTML: true,
					y:30,
					width:210,		
					symbolHeight: 18,
					symbolWidth: 18,
					symbolRadius: 0,
					itemMarginTop: 7,
					itemMarginBottom: 7,
					floating:false,
					enabled: true,		
					padding: 0,
					itemStyle: { //I even tried this but with no lick
						textAlign: 'right',
					}
				},
				
				tooltip: {enabled: false}, 

				exporting: {enabled: false},

				credits: {enabled: false},
				
				plotOptions: {
					pie: {
						//allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						showInLegend: true,
						innerSize: 125,
						depth: 45
					}
				},
				series: [{
					name: 'Brands',
					colorByPoint: true,
					data: this.dashboardChartData['audit-count'],
					//colors: ['#4572A7', '#89A54E', '#AA4643', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					//colors: ['#7cb5ec', '#90ed7d', '#f15c80', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					colors: ['#4572A7', '#89A54E', '#ff0000', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],				
					
					
				}]
			};
		}
		
		if(this.dashboardChartData['certification-count']!==undefined)
		{
			this.certificationChartOptions = {
				htmlOptions: {
					style: {
						width:'50%',
						height:'20%',				 
					}
				 },	
				 chart: {
					type: 'pie',		
					options3d: {
						enabled: true,
						alpha: 45,
						beta: 0
					},
					align: 'left',
					spacingLeft:0,
					spacingRight:0,
					
				},
				legend: {
					spacingBottom:0,
					spacingLeft:0,
					spacingRight:0,
					spacingTop:0,
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'top',
					userHTML: true,
					y:30,
					width:210,		
					symbolHeight: 18,
					symbolWidth: 18,
					symbolRadius: 0,
					itemMarginTop: 7,
					itemMarginBottom: 7,
					floating:false,
					enabled: true,		
					padding: 0,
					itemStyle: { //I even tried this but with no lick
						textAlign: 'right',
					}
				},
				title: {
					text: this.dashboardChartData['total-certification-count'],
					align: 'center',
					verticalAlign: 'middle',					
					width:225,
					y:18,	
					color: '#666666',
					fontFamily: 'Arial, sans-serif',
					fontSize:'12px'
				},
				tooltip: {enabled: false}, 

				exporting: {enabled: false},

				credits: {enabled: false},
				
				plotOptions: {
					pie: {
						//allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						showInLegend: true,
						innerSize: 125,
						depth: 45
					}
				},
				series: [{
					name: 'Brands',
					colorByPoint: true,
					data: this.dashboardChartData['certification-count'],
					//colors: ['#4572A7', '#89A54E', '#AA4643', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					colors: ['#4572A7', '#ff0000', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					
				}]
			};
		}
		
		if(this.dashboardChartData['tcrequest-count']!==undefined)
		{
			this.tcChartOptions = {
				htmlOptions: {
					style: {
						width:'50%',
						height:'20%',				 
					}
				 },	
				 chart: {
					type: 'pie',		
					options3d: {
						enabled: true,
						alpha: 45,
						beta: 0
					},
					align: 'left',
					spacingLeft:0,
					spacingRight:0,
					
				},
				legend: {
					spacingBottom:0,
					spacingLeft:0,
					spacingRight:0,
					spacingTop:0,
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'top',
					userHTML: true,
					y:30,
					width:210,		
					symbolHeight: 18,
					symbolWidth: 18,
					symbolRadius: 0,
					itemMarginTop: 7,
					itemMarginBottom: 7,
					floating:false,
					enabled: true,		
					padding: 0,
					itemStyle: { //I even tried this but with no lick
						textAlign: 'right',
					}
				},
				title: {
					text: this.dashboardChartData['total-tcrequest-count'],
					align: 'center',
					verticalAlign: 'middle',					
					width:225,
					y:18,
					color: '#666666',
					fontFamily: 'Arial, sans-serif',
					fontSize:'12px'
				},
				tooltip: {enabled: false}, 

				exporting: {enabled: false},

				credits: {enabled: false},
				
				plotOptions: {
					pie: {
						//allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						showInLegend: true,
						innerSize: 125,
						depth: 45
					}
				},
				series: [{
					name: 'Brands',
					colorByPoint: true,
					data: this.dashboardChartData['tcrequest-count'],
					//colors: ['#4572A7', '#89A54E', '#AA4643', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					colors: ['#4572A7', '#ff0000', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
					
				}]
			};
		}
		
		
		if(this.dashboardChartData['monthwise_tcrequest']!==undefined)
		{			
			this.monthWisTCChartOptions = {
				chart: {
					inverted: false,
					polar: false				
				},			
				title: {
					text: ''
				},
				tooltip: {enabled: false}, 

				exporting: {enabled: false},

				credits: {enabled: false},

				plotOptions: {
					series: {
						pointWidth: 45
					}
				},
					
				xAxis: {
					categories: this.dashboardChartData['monthwise_tcrequest']['categories'],
					labels: {
						style: {
							color: '#666666',
							fontFamily: 'Arial, sans-serif',
							fontSize:'12px'
						}
					}
				},
				
				yAxis: {
					allowDecimals: false,
					min: 0,
					title: {
						text: 'Number of Request',
						style: {
							color: '#666666',
							fontFamily: 'Arial, sans-serif',
							fontSize:'12px'
						}
					}
				},

				series: [{
					type: 'column',
					colorByPoint: true,
					data: this.dashboardChartData['monthwise_tcrequest']['data'],
					showInLegend: false
				}]
			};
		}
		
		
		if(this.dashboardChartData['renewal-audit-count']!==undefined)
		{
			this.renewalAuditChartOptions = {
				htmlOptions: {
					style: {
						width:'50%',
						height:'20%',				 
					}
				 },	
				 chart: {
					type: 'pie',		
					options3d: {
						enabled: true,
						alpha: 45,
						beta: 0
					},
					align: 'left',
					spacingLeft:0,
					spacingRight:0,
					
				},
				legend: {
					spacingBottom:0,
					spacingLeft:0,
					spacingRight:0,
					spacingTop:0,
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'top',
					userHTML: true,
					y:30,
					width:210,		
					symbolHeight: 18,
					symbolWidth: 18,
					symbolRadius: 0,
					itemMarginTop: 7,
					itemMarginBottom: 7,
					floating:false,
					enabled: true,		
					padding: 0,
					itemStyle: { //I even tried this but with no lick
						textAlign: 'right',
					}
				},
				title: {
					text: this.dashboardChartData['total-renewal-audit-count'],
					align: 'center',
					verticalAlign: 'middle',					
					width:225,
					y:18,
					color: '#666666',
					fontFamily: 'Arial, sans-serif',
					fontSize:'12px'
				},
				tooltip: {enabled: false}, 

				exporting: {enabled: false},

				credits: {enabled: false},
				
				plotOptions: {
					pie: {
						//allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						showInLegend: true,
						innerSize: 125,
						depth: 45
					}
				},
				series: [{
					name: 'Brands',
					colorByPoint: true,
					data: this.dashboardChartData['renewal-audit-count'],
					colors: ['#FF0000', '#F79647', '#4572A7', '#00B050'],					
				}]
			};
		}
		
		if(this.dashboardChartData['due-certificate-count']!==undefined)
		{
			this.dueCertificatetcChartOptions = {
				htmlOptions: {
					style: {
						width:'50%',
						height:'20%',				 
					}
				 },	
				 chart: {
					type: 'pie',		
					options3d: {
						enabled: true,
						alpha: 45,
						beta: 0
					},
					align: 'left',
					spacingLeft:0,
					spacingRight:0,
					
				},
				legend: {
					spacingBottom:0,
					spacingLeft:0,
					spacingRight:0,
					spacingTop:0,
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'top',
					userHTML: true,
					y:30,
					width:210,		
					symbolHeight: 18,
					symbolWidth: 18,
					symbolRadius: 0,
					itemMarginTop: 7,
					itemMarginBottom: 7,
					floating:false,
					enabled: true,		
					padding: 0,
					itemStyle: { //I even tried this but with no lick
						textAlign: 'right',
					}
				},
				title: {
					text: this.dashboardChartData['total-due-certificate-count'],
					align: 'center',
					verticalAlign: 'middle',					
					width:225,
					y:18,
					color: '#666666',
					fontFamily: 'Arial, sans-serif',
					fontSize:'12px'
				},
				tooltip: {enabled: false}, 

				exporting: {enabled: false},

				credits: {enabled: false},
				
				plotOptions: {
					pie: {
						//allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						showInLegend: true,
						innerSize: 125,
						depth: 45
					}
				},
				series: [{
					name: 'Brands',
					colorByPoint: true,
					data: this.dashboardChartData['due-certificate-count'],					
					colors: ['#FF0000', '#F79647', '#4572A7', '#00B050'],
				}]
			};
		}
		
		if(this.dashboardChartData['monthwise_offer_amount']!==undefined)
		{			
			this.monthWisOfferAmountChartOptions = {
				chart: {
					inverted: false,
					polar: false				
				},			
				title: {
					text: ''
				},
				tooltip: {enabled: false}, 

				exporting: {enabled: false},

				credits: {enabled: false},

				plotOptions: {
					series: {
						pointWidth: 45
					}
				},
					
				xAxis: {
					categories: this.dashboardChartData['monthwise_offer_amount']['categories'],
					labels: {
						style: {
							color: '#666666',
							fontFamily: 'Arial, sans-serif',
							fontSize:'12px'
						}
					}
				},
				
				yAxis: {
					allowDecimals: false,
					min: 0,
					title: {
						text: 'Quotation Amount',
						style: {
							color: '#666666',
							fontFamily: 'Arial, sans-serif',
							fontSize:'12px'
						}
					}
				},

				series: [{
					type: 'column',
					colorByPoint: true,
					data: this.dashboardChartData['monthwise_offer_amount']['data'],
					showInLegend: false
				}]
			};
		}


		// renewalAuditChartOptions:any;
		// dueCertificatetcChartOptions:any;
    },
    error => {
        this.error = error;
        this.loading = false;
    });	  
  }
  
  changeDashboardContent(arg)
  {
	this.overview_status = false;
	this.offer_status = false;
	this.enquiry_status = false;
	this.pending_users_status = false;
	this.pending_declaration_approval = false;
	this.pending_bgroup_approval = false;
	this.pending_standard_approval = false;
	this.renewal_audit_status = false;
    this.due_certificate_status = false;
    this.nc_due_status = false;
	this.pending_actions_status = false;
		
	if(arg=='overview'){
		this.overview_status = true;
	}else if(arg=='offer'){	
		this.offer_status = true;
	}else if(arg=='enquiry'){
		this.enquiry_status = true;
	}else if(arg=='pending_users'){
		this.pending_users_status = true;
	}else if(arg=='renewal_audit'){
		this.renewal_audit_status = true;	
	}else if(arg=='due_certificate'){
		this.due_certificate_status = true;	
	}else if(arg=='nc_due'){
		this.nc_due_status = true;
	}else if(arg=='pending_actions'){
		this.pending_actions_status = true;
	}	  
  }

  modalss:any;
  open(content,app_id) {
	this.app_id = app_id;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }
}