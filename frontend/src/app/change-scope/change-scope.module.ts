import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ChangeScopeRoutingModule } from './change-scope-routing.module';
import { CommonmodModule } from '@app/commonmod/commonmod.module';


import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';


import { ProcessAdditionComponent } from './process-addition/process-addition.component';
import { ProcessRequestComponent } from './process-request/process-request.component';

import { AddUnitAdditionComponent } from './unit-addition/add-unit-addition/add-unit-addition.component';
import { ViewUnitAdditionComponent } from './unit-addition/view-unit-addition/view-unit-addition.component';
import { ListUnitAdditionComponent } from './unit-addition/list-unit-addition/list-unit-addition.component';
import { EditUnitAdditionComponent } from './unit-addition/edit-unit-addition/edit-unit-addition.component';
import { ProcessListComponent } from './process-list/process-list.component';

import { AddProductAdditionComponent } from './product-addition/add-product-addition/add-product-addition.component';
import { ViewProductAdditionComponent } from './product-addition/view-product-addition/view-product-addition.component';
import { ListProductAdditionComponent } from './product-addition/list-product-addition/list-product-addition.component';
import { EditProductAdditionComponent } from './product-addition/edit-product-addition/edit-product-addition.component';
import { RequestUnitAdditionComponent } from './unit-addition/request-unit-addition/request-unit-addition.component';

import { ProcessViewComponent } from './process-view/process-view.component';


import { RequestStandardAdditionComponent } from './standard-addition/request-standard-addition/request-standard-addition.component';
import { AddStandardAdditionComponent } from './standard-addition/add-standard-addition/add-standard-addition.component';
import { EditStandardAdditionComponent } from './standard-addition/edit-standard-addition/edit-standard-addition.component';
import { ListStandardAdditionComponent } from './standard-addition/list-standard-addition/list-standard-addition.component';
import { ViewStandardAdditionComponent } from './standard-addition/view-standard-addition/view-standard-addition.component';
import { RequestProductAdditionComponent } from './product-addition/request-product-addition/request-product-addition.component';
import { RequestWithdrawUnitComponent } from './withdraw/request-withdraw-unit/request-withdraw-unit.component';
import { ViewWithdrawUnitComponent } from './withdraw/view-withdraw-unit/view-withdraw-unit.component';
import { ListWithdrawUnitComponent } from './withdraw/list-withdraw-unit/list-withdraw-unit.component';
import { ListChangeAddressComponent } from './list-change-address/list-change-address.component';
import { AddChangeAddressComponent } from './add-change-address/add-change-address.component';
import { EditChangeAddressComponent } from './edit-change-address/edit-change-address.component';
import { ViewChangeAddressComponent } from './view-change-address/view-change-address.component';






@NgModule({
  declarations: [ProcessAdditionComponent, ProcessRequestComponent, ProcessListComponent, AddUnitAdditionComponent, ViewUnitAdditionComponent, ListUnitAdditionComponent, EditUnitAdditionComponent, AddProductAdditionComponent, ViewProductAdditionComponent, ListProductAdditionComponent, EditProductAdditionComponent, RequestUnitAdditionComponent, ProcessViewComponent,  RequestStandardAdditionComponent, AddStandardAdditionComponent, EditStandardAdditionComponent, ListStandardAdditionComponent, ViewStandardAdditionComponent, RequestProductAdditionComponent, RequestWithdrawUnitComponent, ViewWithdrawUnitComponent, ListWithdrawUnitComponent, ListChangeAddressComponent, AddChangeAddressComponent, EditChangeAddressComponent, ViewChangeAddressComponent],
  imports: [
    CommonModule,
	  ChangeScopeRoutingModule,
	  FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,  
    CommonmodModule
  ]
})
export class ChangeScopeModule { }
