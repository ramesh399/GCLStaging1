import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EnquiryRoutingModule } from './enquiry-routing.module';


import { FormsModule, ReactiveFormsModule } from '@angular/forms';


import { EnquiryListComponent } from './enquiry-list/enquiry-list.component';
import { EnquiryViewComponent } from './enquiry-view/enquiry-view.component';
//import { NgbdSortableHeader } from '@app/helpers/sortable.directive';
import {CommonmodModule} from '@app/commonmod/commonmod.module';

@NgModule({
 
  imports: [
    CommonModule,
    EnquiryRoutingModule,
    FormsModule,
    
    ReactiveFormsModule,
    BrowserModule,
    CommonmodModule
  ],
  declarations: [EnquiryListComponent, EnquiryViewComponent],
})
export class EnquiryModule { }
