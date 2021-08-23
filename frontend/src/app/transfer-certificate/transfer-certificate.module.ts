import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TransferCertificateRoutingModule } from './transfer-certificate-routing.module';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { NgbdSortableHeader } from '@app/helpers/sortable.directive';

import {CommonmodModule} from '@app/commonmod/commonmod.module';


import { BuyerComponent } from './buyer/buyer.component';
import { ConsigneeComponent } from './consignee/consignee.component';
import { RawMaterialComponent } from './raw-material/raw-material.component';
import { InspectionBodyComponent } from './inspection-body/inspection-body.component';

import { TreeviewModule } from 'ngx-treeview';
import { AddRequestComponent } from './request/add-request/add-request.component';
import { ListRequestComponent } from './request/list-request/list-request.component';
import { EditRequestComponent } from './request/edit-request/edit-request.component';
import { ViewRequestComponent } from './request/view-request/view-request.component';
import { RawMaterialStandardComponent } from './raw-material-standard/raw-material-standard.component';
import { RawMaterialStandardLabelGradeComponent } from './raw-material-standard-label-grade/raw-material-standard-label-grade.component';
import { RawMaterialStandardCombinationComponent } from './raw-material-standard-combination/raw-material-standard-combination.component';
import { IfoamStandardComponent } from './ifoam-standard/ifoam-standard.component';
import { TcInvoiceComponent } from './tc-invoice/tc-invoice.component';

@NgModule({
  declarations: [BuyerComponent, ConsigneeComponent, RawMaterialComponent, InspectionBodyComponent, AddRequestComponent, ListRequestComponent, EditRequestComponent, ViewRequestComponent, RawMaterialStandardComponent, RawMaterialStandardLabelGradeComponent, RawMaterialStandardCombinationComponent, IfoamStandardComponent, TcInvoiceComponent],
  imports: [
    CommonModule,
	TransferCertificateRoutingModule,
	FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,
	CommonmodModule,
    TreeviewModule.forRoot()
  ]
})
export class TransferCertificateModule { }
