import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { OfferGenerationListComponent } from './offer-generation-list/offer-generation-list.component';
import { ListOfferComponent } from './list-offer/list-offer.component';
import { ViewOfferComponent } from './view-offer/view-offer.component';
import { GenerateOfferComponent } from './generate-offer/generate-offer.component';
import { ValidateCertifiedStandardComponent } from './validate-certified-standard/validate-certified-standard.component';


import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';

const offerRoutes: Routes = [
 { path: 'offer/generate-list',  component: OfferGenerationListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },  
 { path: 'offer/offer-list',  component: ListOfferComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },  
 { path: 'offer/view-offer',  component: ViewOfferComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },  
 { path: 'offer/offer-generate',  component: GenerateOfferComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },   
 { path: 'offer/validate-certified-standard',  component: ValidateCertifiedStandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { roles: [Role.Admin] } },   
];

@NgModule({
  imports: [ RouterModule.forChild(offerRoutes)],
  exports: [RouterModule]
})
export class OfferRoutingModule { }
