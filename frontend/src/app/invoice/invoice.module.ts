import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { InvoiceRoutingModule } from './invoice-routing.module';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { NgbdSortableHeader } from '@app/helpers/sortable.directive';

import {DemoMaterialModule} from '@app/material-module';

import { GenerateListComponent } from './generate-list/generate-list.component';
import { InvoiceGenerateComponent } from './invoice-generate/invoice-generate.component';
import { ViewInvoiceComponent } from './view-invoice/view-invoice.component';
import { ListInvoiceComponent } from './list-invoice/list-invoice.component';
import {CommonmodModule} from '@app/commonmod/commonmod.module';

@NgModule({
  declarations: [GenerateListComponent, InvoiceGenerateComponent, ViewInvoiceComponent, ListInvoiceComponent],
  imports: [
    CommonModule,
    InvoiceRoutingModule,
    FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,
    DemoMaterialModule,
    CommonmodModule
  ]
})
export class InvoiceModule { }
