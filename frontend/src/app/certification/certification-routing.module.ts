import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { CertificateGenerationListComponent } from './certificate-generation-list/certificate-generation-list.component';
import { ListCertificateComponent } from './list-certificate/list-certificate.component';
import { ViewAuditPlanComponent } from './view-audit-plan/view-audit-plan.component';
import { CertificationReviewerChecklistComponent } from './certification-reviewer-checklist/certification-reviewer-checklist.component';
import { ViewCertificateComponent } from './view-certificate/view-certificate.component';
import { DueCertificateComponent } from './due-certificate/due-certificate.component';


import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';

const certificationRoutes: Routes = [
 { path: 'certification/generate-list',  component: CertificateGenerationListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },  
 { path: 'certification/certificate-list',  component: ListCertificateComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },    
 { path: 'certification/view-audit-plan',  component: ViewAuditPlanComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },    
 { path: 'certification/certification-reviewer-checklist',  component: CertificationReviewerChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },   
 { path: 'certification/view-certificate',  component: ViewCertificateComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },    
 { path: 'certification/due-certificate/list',  component: DueCertificateComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },    
];

@NgModule({
  imports: [ RouterModule.forChild(certificationRoutes) ],
  exports: [ RouterModule ]
})
export class CertificationRoutingModule { }
