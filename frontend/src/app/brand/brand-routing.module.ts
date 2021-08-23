import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AddComponent } from './request/add/add.component';
import { EditComponent } from './request/edit/edit.component';
import { ListComponent } from './request/list/list.component';
import { ViewComponent } from './request/view/view.component';

import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';
import { Rule } from '@app/models/rule';

const brandRoutes: Routes = [
 { path: 'brand/add-request',  component: AddComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3],rules:Rule.CreateApplication,rulewithtype:1 } },  
 { path: 'brand/edit-request',  component: EditComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3],rules:'update_application',rulewithtype:1 } },  
 { path: 'brand/list',  component: ListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {  usertype:[1,2,3] } },  
 { path: 'brand/view',  component: ViewComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]  } },  

 
 
 
];

@NgModule({
  imports: [ RouterModule.forChild(brandRoutes)],
  exports: [RouterModule]
})
export class BrandRoutingModule { }
