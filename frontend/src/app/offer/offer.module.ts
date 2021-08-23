import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { OfferRoutingModule } from './offer-routing.module';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { NgbdSortableHeader } from '@app/helpers/sortable.directive';

import { OfferGenerationListComponent } from './offer-generation-list/offer-generation-list.component';
import { ListOfferComponent } from './list-offer/list-offer.component';
import { ViewOfferComponent } from './view-offer/view-offer.component';
import { GenerateOfferComponent } from './generate-offer/generate-offer.component';
import {CommonmodModule} from '@app/commonmod/commonmod.module';
import { ValidateCertifiedStandardComponent } from './validate-certified-standard/validate-certified-standard.component';


@NgModule({
  declarations: [OfferGenerationListComponent, ListOfferComponent, ViewOfferComponent, GenerateOfferComponent, ValidateCertifiedStandardComponent],
  imports: [
    CommonModule,
	  OfferRoutingModule,
	  FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,
    CommonmodModule
  ]
})
export class OfferModule { }
