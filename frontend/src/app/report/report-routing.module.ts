import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';
import { Rule } from '@app/models/rule';

import { ClientReportComponent } from './client-report/client-report.component';
import { UnannouncedAuditReportComponent } from './unannounced-audit-report/unannounced-audit-report.component';
import { TcReportComponent } from './tc-report/tc-report.component';
import { PersonnelReportComponent } from './personnel-report/personnel-report.component';
import { ReviewerPerformanceReportComponent } from './reviewer-performance-report/reviewer-performance-report.component';
import { UnitReportComponent } from './unit-report/unit-report.component';
import { StandardMonthlyReportComponent } from './standard-monthly-report/standard-monthly-report.component';
import { AuditorKpiReportComponent } from './auditor-kpi-report/auditor-kpi-report.component';
import { QuotationReportComponent } from './quotation-report/quotation-report.component';
import { InvoiceReportComponent } from './invoice-report/invoice-report.component';
import { NcReportComponent } from './nc-report/nc-report.component';
import { CustomerProgramReportComponent } from './customer-program-report/customer-program-report.component';
import { ProgramAuditReportComponent } from './program-audit-report/program-audit-report.component';
import { CertificationDetailsReportComponent } from './certification-details-report/certification-details-report.component';
import { GotsCertifiedClientComponent } from './gots-certified-client/gots-certified-client.component';
import { CdsReportComponent } from './cds-report/cds-report.component';



const reportRoutes: Routes = [ 
  { path: 'reports/certified-client-report',  component: ClientReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/unannounced-audit-report',  component: UnannouncedAuditReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/tc-report',  component: TcReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/personnel-report',  component: PersonnelReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/reviewer-report',  component: ReviewerPerformanceReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/unit-report',  component: UnitReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/standard-monthly-report',  component: StandardMonthlyReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/auditor-kpi-report',  component: AuditorKpiReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/quotation-report',  component: QuotationReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/invoice-report',  component: InvoiceReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/nc-report',  component: NcReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },

  { path: 'reports/customer-program-report',  component: CustomerProgramReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/program-audit-report',  component: ProgramAuditReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/cetificateion-details-report',  component: CertificationDetailsReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/yearly-certified-client',  component: GotsCertifiedClientComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
  { path: 'reports/cds-report',  component: CdsReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },
];

@NgModule({
  imports: [ RouterModule.forChild(reportRoutes)],
  exports: [RouterModule]
})
export class ReportRoutingModule { }
