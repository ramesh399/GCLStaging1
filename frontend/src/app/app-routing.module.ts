import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { AuditEnquiryComponent } from './audit-enquiry/audit-enquiry.component';
import { LoginComponent } from './login/login.component';
import { ForgotPasswordComponent } from './forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import { ChangeUsernamePasswordComponent } from './change-username-password/change-username-password.component';
import { ChangePasswordComponent } from './change-password/change-password.component';
import { ErrorComponent } from './common/error/error.component';
import { UserDashboardComponent } from './dashboard/user-dashboard/user-dashboard.component';
import { FranchiseDashboardComponent } from './dashboard/franchise-dashboard/franchise-dashboard.component';
import { CustomerDashboardComponent } from './dashboard/customer-dashboard/customer-dashboard.component';
import { ViewProfileComponent } from './profile/view-profile/view-profile.component';
import { LogoutComponent } from './logout/logout.component';
import { NotificationComponent } from './notification/notification.component';

import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';

const routes: Routes = [
  {path:'',component:AuditEnquiryComponent},
  {path:'error404',component:ErrorComponent},
  {path:'login',component:LoginComponent},
  {path:'logout',component:LogoutComponent},
  {path:'forgot-password',component:ForgotPasswordComponent},
  {path:'reset-password',component:ResetPasswordComponent},
  {path:'change-username-password',component:ChangeUsernamePasswordComponent},
  //{path:'change-username-password',component:ChangeUsernamePasswordComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },
  {path:'change-password',component:ChangePasswordComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },
  {path:'customer/dashboard',component:CustomerDashboardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },
  {path:'user/dashboard',component:UserDashboardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },
  {path:'franchise/dashboard',component:FranchiseDashboardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },
  {path:'profile/view',component:ViewProfileComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },
  {path:'notification',component:NotificationComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },
 /* 
  {
    path: 'audit',
    loadChildren: () => import('./audit/audit.module').then(m => m.AuditModule)
  },
  */
  //{ path: 'enquiry',   redirectTo: '/enquiry/list', pathMatch: 'full' }
  //{path:'enquiry-list',component:EnquiryListComponent,canActivate: [AuthGuard],data: { roles: [Role.Admin] }}
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],  
  exports: [RouterModule]
})
export class AppRoutingModule { }