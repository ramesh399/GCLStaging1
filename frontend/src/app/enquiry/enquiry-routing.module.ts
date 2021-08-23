import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { EnquiryListComponent } from './enquiry-list/enquiry-list.component';
import { EnquiryViewComponent } from './enquiry-view/enquiry-view.component';

import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';

const enquiryRoutes: Routes = [
  { path: 'enquiry/list',  component: EnquiryListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,3]} },
  { path: 'enquiry',  component: EnquiryListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,3]} },
  { path: 'enquiry/:id',  component: EnquiryViewComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,3]} },
  //{ path: 'enquiry',  component: EnquiryListComponent },
  //{ path: 'hero/:id', component: HeroDetailComponent }
];

@NgModule({
  imports: [ RouterModule.forChild(enquiryRoutes)],
  exports: [RouterModule]
})
export class EnquiryRoutingModule { }
