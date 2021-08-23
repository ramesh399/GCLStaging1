import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AuditRoutingModule } from './audit-routing.module';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { NgbdSortableHeader } from '@app/helpers/sortable.directive';

import { DemoMaterialModule } from '@app/material-module';
import { CommonmodModule } from '@app/commonmod/commonmod.module';

import { AuditPlanComponent } from './audit-plan/audit-plan.component';
import { FollowupAuditPlanComponent } from './followup-audit-plan/followup-audit-plan.component';
import { ViewAuditPlanComponent } from './view-audit-plan/view-audit-plan.component';
import { ListAuditPlanComponent } from './list-audit-plan/list-audit-plan.component';
import { InspectionPlanComponent } from './inspection-plan/inspection-plan.component';
import { ViewInspectionPlanComponent } from './view-inspection-plan/view-inspection-plan.component';
import { AuditPlanChecklistComponent } from './audit-plan-checklist/audit-plan-checklist.component';
import { AuditExecutionComponent } from './audit-execution/audit-execution.component';
import { AuditReportReviewComponent } from './audit-report-review/audit-report-review.component';
import { AuditFindingsComponent } from './audit-findings/audit-findings.component';
import { AuditFindingsRemediationComponent } from './audit-findings-remediation/audit-findings-remediation.component';
import { AuditReviewerRemediationComponent } from './audit-reviewer-remediation/audit-reviewer-remediation.component';
import { AuditReviewerChecklistComponent } from './audit-reviewer-checklist/audit-reviewer-checklist.component';
import { ListRenewalAuditComponent } from './list-renewal-audit/list-renewal-audit.component';
import { AuditAttendanceSheetComponent } from './audit-reports/audit-attendance-sheet/audit-attendance-sheet.component';
//import { AuditInterviewComponent } from './audit-reports/audit-interview/audit-interview.component';
import { AuditRaScopeholderComponent } from './audit-reports/audit-ra-scopeholder/audit-ra-scopeholder.component';
import { AuditChemicalListComponent } from './audit-reports/audit-chemical-list/audit-chemical-list.component';
//import { AuditEnvironmentComponent } from './audit-reports/audit-environment/audit-environment.component';
import { AuditSamplingComponent } from './audit-reports/audit-sampling/audit-sampling.component';
import { AuditInterviewEmployeeComponent } from './audit-reports/audit-interview-employee/audit-interview-employee.component';
import { AuditQbsScopeholderComponent } from './audit-reports/audit-qbs-scopeholder/audit-qbs-scopeholder.component';
import { AuditLivingwageChecklistComponent } from './audit-reports/audit-livingwage-checklist/audit-livingwage-checklist.component';
//import { AuditClientinformationComponent } from './audit-reports/audit-clientinformation/audit-clientinformation.component';
import { AuditNcComponent } from './audit-reports/audit-nc/audit-nc.component';
import { AuditLivingwageViewchecklistComponent } from './audit-reports/audit-livingwage-viewchecklist/audit-livingwage-viewchecklist.component';
import { AuditInterviewViewchecklistComponent } from './audit-reports/audit-interview-viewchecklist/audit-interview-viewchecklist.component';

import { ListUnannouncedAuditComponent } from './list-unannounced-audit/list-unannounced-audit.component';


//, AuditClientinformationComponent ,AuditEnvironmentComponent,, AuditInterviewComponent
@NgModule({
  declarations: [ListUnannouncedAuditComponent, AuditPlanComponent, FollowupAuditPlanComponent, ViewAuditPlanComponent, ListAuditPlanComponent, InspectionPlanComponent, ViewInspectionPlanComponent, AuditPlanChecklistComponent, AuditExecutionComponent, AuditReportReviewComponent,AuditFindingsComponent,AuditReviewerChecklistComponent,AuditFindingsRemediationComponent,AuditReviewerRemediationComponent, ListRenewalAuditComponent, AuditAttendanceSheetComponent, AuditRaScopeholderComponent, AuditChemicalListComponent,  AuditSamplingComponent, AuditInterviewEmployeeComponent, AuditQbsScopeholderComponent, AuditLivingwageChecklistComponent, AuditNcComponent, AuditLivingwageViewchecklistComponent, AuditInterviewViewchecklistComponent],
  imports: [
    CommonModule,
  	AuditRoutingModule,
  	FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,
  	DemoMaterialModule,
    CommonmodModule
  ]
})
export class AuditModule { }
