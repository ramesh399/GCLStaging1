import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LibraryRoutingModule } from './library-routing.module';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { NgbdSortableHeader } from '@app/helpers/sortable.directive';

import {DemoMaterialModule} from '@app/material-module';

import {CommonmodModule} from '@app/commonmod/commonmod.module';

import { MeetingComponent } from './meeting/meeting.component';
import { FaqComponent } from './faq/faq.component';
import { LegislationComponent } from './legislation/legislation.component';
import { GislogsComponent } from './gislogs/gislogs.component';
import { ManualComponent } from './manual/manual.component';
import { DocumentComponent } from './document/document.component';

import { ApprovedSuppliersComponent } from './approved-suppliers/approved-suppliers.component';
import { AuditReportComponent } from './audit-report/audit-report.component';
import { RiskassessmentComponent } from './riskassessment/riskassessment.component';
import { MailComponent } from './mail/mail.component';
import { ScopeGroupsComponent } from './scope-groups/scope-groups.component';
import { ListTranslatorComponent } from './translator/list-translator/list-translator.component';

@NgModule({

  declarations: [  GislogsComponent, MeetingComponent, FaqComponent, LegislationComponent, ManualComponent, DocumentComponent, ApprovedSuppliersComponent, AuditReportComponent,RiskassessmentComponent, MailComponent, ScopeGroupsComponent, ListTranslatorComponent],
 
  imports: [
    CommonModule,
    LibraryRoutingModule,
    FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,
    DemoMaterialModule,
    CommonmodModule
  ]
})
export class LibraryModule { }
