import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApplicationRoutingModule } from './application-routing.module';


import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';


import { AddComponent } from './request/add/add.component';
import { EditComponent } from './request/edit/edit.component';
import { ViewComponent } from './request/view/view.component';
import { ReviewchecklistComponent } from './review/reviewchecklist/reviewchecklist.component';
import { ListComponent } from './request/list/list.component';

import {BrowserAnimationsModule} from '@angular/platform-browser/animations';

import {CommonmodModule} from '@app/commonmod/commonmod.module';

import { RenewalChecklistComponent } from './renewal-checklist/renewal-checklist.component';
import { ListRenewalRequestComponent } from './list-renewal-request/list-renewal-request.component';
import { ViewRenewalRequestComponent } from './view-renewal-request/view-renewal-request.component';

//import {FileUploadModule} from 'ng2-file-upload';


@NgModule({
  declarations: [AddComponent, EditComponent, ViewComponent, ReviewchecklistComponent, ListComponent, RenewalChecklistComponent, ListRenewalRequestComponent, ViewRenewalRequestComponent],
  imports: [
    CommonModule,
	  ApplicationRoutingModule,
	  FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,
  
    CommonmodModule
    //FileUploadModule
  ]
})
export class ApplicationModule { }
