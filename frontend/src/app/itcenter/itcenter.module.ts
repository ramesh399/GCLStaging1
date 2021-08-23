import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import {DemoMaterialModule} from '@app/material-module';
 
import { ListItCenterComponent } from './list-it-center/list-it-center.component';
import { AddItCenterComponent } from './add-it-center/add-it-center.component';
import { EditItCenterComponent } from './edit-it-center/edit-it-center.component';
import {CommonmodModule} from '@app/commonmod/commonmod.module';
import { ItcenterRoutingModule } from './itcenter-routing.module';

@NgModule({
  declarations: [ListItCenterComponent, AddItCenterComponent, EditItCenterComponent ],
  imports: [
    CommonModule,
    FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,
    DemoMaterialModule,
    ItcenterRoutingModule,
    CommonmodModule 
  ]
})
   
export class ItcenterModule { }
