import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { UnannouncedauditRoutingModule } from './unannouncedaudit-routing.module';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { NgbdSortableHeader } from '@app/helpers/sortable.directive';

import {CommonmodModule} from '@app/commonmod/commonmod.module';

import { ListUnannouncedAuditComponent } from './list-unannounced-audit/list-unannounced-audit.component';
import { CompanyListComponent } from './company-list/company-list.component';
import { ViewUnannouncedAuditComponent } from './view-unannounced-audit/view-unannounced-audit.component';

@NgModule({
  declarations: [ListUnannouncedAuditComponent, CompanyListComponent, ViewUnannouncedAuditComponent],
  imports: [
    CommonModule,
	UnannouncedauditRoutingModule,
	FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,
	CommonmodModule    
  ]
})
export class UnannouncedauditModule { }
