import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {DemoMaterialModule} from '@app/material-module';
import { ReportRoutingModule } from './report-routing.module';
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


@NgModule({
  declarations: [ClientReportComponent, UnannouncedAuditReportComponent, TcReportComponent, PersonnelReportComponent, ReviewerPerformanceReportComponent, UnitReportComponent, StandardMonthlyReportComponent, AuditorKpiReportComponent, QuotationReportComponent, InvoiceReportComponent, NcReportComponent, CustomerProgramReportComponent, ProgramAuditReportComponent, CertificationDetailsReportComponent, GotsCertifiedClientComponent, CdsReportComponent],
  imports: [
    CommonModule,
    ReportRoutingModule,
    FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,
    DemoMaterialModule
  ]
})
export class ReportModule { }
