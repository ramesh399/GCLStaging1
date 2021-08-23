import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AuditClientinformationComponent } from './audit-clientinformation/audit-clientinformation.component';
import { AuditEnvironmentComponent } from './audit-environment/audit-environment.component';
import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';
const commonmodRoutes: Routes = [
  
  { path: 'audit/audit-clientinformation/index',  component:AuditClientinformationComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  { path: 'audit/audit-environment/list',  component:AuditEnvironmentComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  rules:'', roles: [Role.Admin] } },
  	
];

@NgModule({
  imports: [ RouterModule.forChild(commonmodRoutes)],
  exports: [RouterModule]
})
export class CommonmodRoutingModule { }
