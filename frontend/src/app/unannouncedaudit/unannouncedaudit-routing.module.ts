import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ListUnannouncedAuditComponent } from './list-unannounced-audit/list-unannounced-audit.component';
import { CompanyListComponent } from './company-list/company-list.component';
import { ViewUnannouncedAuditComponent } from './view-unannounced-audit/view-unannounced-audit.component';

import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';
import { Rule } from '@app/models/rule';

const tcRoutes: Routes = [
  { path: 'unannounced-audit/list',  component:ListUnannouncedAuditComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  { path: 'unannounced-audit/view',  component:ViewUnannouncedAuditComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  { path: 'unannounced-audit/company-list',  component:CompanyListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } }
];

@NgModule({
  imports: [ RouterModule.forChild(tcRoutes)],
  exports: [RouterModule]
})
export class UnannouncedauditRoutingModule { }
