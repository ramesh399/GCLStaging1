import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { GenerateListComponent } from './generate-list/generate-list.component';
import { InvoiceGenerateComponent } from './invoice-generate/invoice-generate.component';
import { ViewInvoiceComponent } from './view-invoice/view-invoice.component';
import { ListInvoiceComponent } from './list-invoice/list-invoice.component';

import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';

const invoiceRoutes: Routes = [
 //{ path: 'invoice/generate-list',  component: GenerateListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },   
 { path: 'invoice/customer-invoice-list',  component: GenerateListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {invoicetype:1,usertype:[1,2,3] } },
 { path: 'invoice/oss-invoice-list',  component: GenerateListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {invoicetype:2,usertype:[1,3] } },
 { path: 'invoice/customer-additional-invoice-list',  component: GenerateListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {invoicetype:3,usertype:[1,2,3]} },
 { path: 'invoice/oss-additional-invoice-list',  component: GenerateListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {invoicetype:4,usertype:[1,3]} },
 
 { path: 'invoice/invoice-generate',  component: InvoiceGenerateComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,3] } },   
 { path: 'invoice/view-invoice',  component: ViewInvoiceComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3] } },   
 //{ path: 'invoice/invoice-list',  component: ListInvoiceComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },   
 
];

@NgModule({
  imports: [ RouterModule.forChild(invoiceRoutes)],
  exports: [RouterModule]
})
export class InvoiceRoutingModule { }
