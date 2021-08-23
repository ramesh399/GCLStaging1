import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CertificationRoutingModule } from './certification-routing.module';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { NgbdSortableHeader } from '@app/helpers/sortable.directive';

import { DemoMaterialModule } from '@app/material-module';
import { CommonmodModule } from '@app/commonmod/commonmod.module';

import { CertificateGenerationListComponent } from './certificate-generation-list/certificate-generation-list.component';
import { ListCertificateComponent } from './list-certificate/list-certificate.component';
import { ViewAuditPlanComponent } from './view-audit-plan/view-audit-plan.component';

import { CertificationReviewerChecklistComponent } from './certification-reviewer-checklist/certification-reviewer-checklist.component';
import { ViewCertificateComponent } from './view-certificate/view-certificate.component';
import { DueCertificateComponent } from './due-certificate/due-certificate.component';

@NgModule({
  declarations: [CertificateGenerationListComponent, ListCertificateComponent, ViewAuditPlanComponent, CertificationReviewerChecklistComponent, ViewCertificateComponent, DueCertificateComponent],
  imports: [
    CommonModule,
	CertificationRoutingModule,
	FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,
	DemoMaterialModule,
	CommonmodModule
  ]
})
export class CertificationModule { }
