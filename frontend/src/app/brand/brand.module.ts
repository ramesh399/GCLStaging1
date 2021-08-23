import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { BrandRoutingModule } from './brand-routing.module';


import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';


import { AddComponent } from './request/add/add.component';
import { EditComponent } from './request/edit/edit.component';
import { ViewComponent } from './request/view/view.component';
import { ListComponent } from './request/list/list.component';

import {BrowserAnimationsModule} from '@angular/platform-browser/animations';

import {CommonmodModule} from '@app/commonmod/commonmod.module';


//import {FileUploadModule} from 'ng2-file-upload';


@NgModule({
  declarations: [AddComponent, EditComponent, ViewComponent,  ListComponent],
  imports: [
    CommonModule,
	  BrandRoutingModule,
	  FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,
  
    CommonmodModule
    //FileUploadModule
  ]
})
export class BrandModule { }
