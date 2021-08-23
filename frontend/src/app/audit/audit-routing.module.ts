import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AuditPlanComponent } from './audit-plan/audit-plan.component';

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
import { FollowupAuditPlanComponent } from './followup-audit-plan/followup-audit-plan.component';

import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';


const auditRoutes: Routes = [
 
 { path: 'audit/audit-plan-checklist',  component: AuditPlanChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },  
 { path: 'audit/audit-plan',  component: AuditPlanComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },
 { path: 'audit/followup-audit-plan',  component: FollowupAuditPlanComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },   
 //{ path: 'view-audit-plan',  component: ViewAuditPlanComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },   
 //{ path: 'list-audit-plan',  component: ListAuditPlanComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } }, 
 { path: 'audit/view-audit-plan',  component: ViewAuditPlanComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },   
 { path: 'audit/list-audit-plan',  component: ListAuditPlanComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } }, 
 { path: 'audit/inspection-plan',  component: InspectionPlanComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],roles: [Role.Admin] } }, 
 { path: 'audit/view-inspection-plan',  component: ViewInspectionPlanComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } }, 
//,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } 
 { path: 'audit/audit-execution',  component: AuditExecutionComponent},
 { path: 'audit/audit-report-review',  component: AuditReportReviewComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },

 { path: 'audit/audit-findings',  component: AuditFindingsComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  { path: 'audit/audit-findings-remediation',  component:AuditFindingsRemediationComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  { path: 'audit/audit-reviewer-remediation',  component:AuditReviewerRemediationComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  { path: 'audit/renewal-audit/list',  component:ListRenewalAuditComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  { path: 'audit/audit-attendance-sheet/list',  component:AuditAttendanceSheetComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  //{ path: 'audit/audit-interview/list',  component:AuditInterviewComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  { path: 'audit/audit-ra-scopeholder/list',  component:AuditRaScopeholderComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  { path: 'audit/audit-chemical/list',  component:AuditChemicalListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  //{ path: 'audit/audit-environment/list',  component:AuditEnvironmentComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  { path: 'audit/audit-sampling/list',  component:AuditSamplingComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  { path: 'audit/audit-interview-employee/list',  component:AuditInterviewEmployeeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  { path: 'audit/audit-qbs-scopeholder/index',  component:AuditQbsScopeholderComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },

  { path: 'audit/audit-livingwage-checklist/list',  component:AuditLivingwageChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  //{ path: 'audit/audit-clientinformation/index',  component:AuditClientinformationComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  
  { path: 'audit/audit-non-conformity/index',  component:AuditNcComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },  
];

@NgModule({
  imports: [ RouterModule.forChild(auditRoutes)],
  exports: [RouterModule]
})
export class AuditRoutingModule { }
