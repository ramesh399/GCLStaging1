import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

//import { GenerateListComponent } from './generate-list/generate-list.component';
import { ListItCenterComponent } from './list-it-center/list-it-center.component';
import { AddItCenterComponent } from './add-it-center/add-it-center.component';
import { EditItCenterComponent } from './edit-it-center/edit-it-center.component';

import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';
import { RouterModule, Routes } from '@angular/router';

const itcenterRoutes: Routes = [
 //{ path: 'invoice/oss-additional-invoice-list',  component: GenerateListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {invoicetype:4,usertype:[1,3]} },
 { path: 'itcenter/list-it-center',  component: ListItCenterComponent },
 { path: 'itcenter/add-it-center',  component: AddItCenterComponent },
 { path: 'itcenter/edit-it-center',  component: EditItCenterComponent },
 
];

@NgModule({
  imports: [ RouterModule.forChild(itcenterRoutes)],
  exports: [RouterModule]
})
export class ItcenterRoutingModule { }
