import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AddComponent } from './request/add/add.component';
import { EditComponent } from './request/edit/edit.component';
import { ListComponent } from './request/list/list.component';
import { ViewComponent } from './request/view/view.component';
import { ReviewchecklistComponent } from './review/reviewchecklist/reviewchecklist.component';

import { RenewalChecklistComponent } from './renewal-checklist/renewal-checklist.component';
import { ListRenewalRequestComponent } from './list-renewal-request/list-renewal-request.component';
import { ViewRenewalRequestComponent } from './view-renewal-request/view-renewal-request.component';


import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';
import { Rule } from '@app/models/rule';

const applicationRoutes: Routes = [
 { path: 'application/add-request',  component: AddComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3],rules:Rule.CreateApplication,rulewithtype:1 } },  
 { path: 'application/edit-request',  component: EditComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3],rules:'update_application',rulewithtype:1 } },  
 { path: 'application/list',  component: ListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },  
 { path: 'application/apps/view',  component: ViewComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]  } },  
 { path: 'application/review/checklist',  component: ReviewchecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,3],rules:Rule.ApplicationReview } },   
 
 { path: 'application/renewal-checklist',  component: RenewalChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3] } },
 { path: 'application/renewal-request/list',  component: ListRenewalRequestComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3] } }, 
 { path: 'application/renewal-request/view',  component: ViewRenewalRequestComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3] } },   
 
];

@NgModule({
  imports: [ RouterModule.forChild(applicationRoutes)],
  exports: [RouterModule]
})
export class ApplicationRoutingModule { }
