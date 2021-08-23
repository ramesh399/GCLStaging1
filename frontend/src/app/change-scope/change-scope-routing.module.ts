import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ProcessAdditionComponent } from './process-addition/process-addition.component';


import { RequestStandardAdditionComponent } from './standard-addition/request-standard-addition/request-standard-addition.component';
import { AddStandardAdditionComponent } from './standard-addition/add-standard-addition/add-standard-addition.component';
import { EditStandardAdditionComponent } from './standard-addition/edit-standard-addition/edit-standard-addition.component';
import { ListStandardAdditionComponent } from './standard-addition/list-standard-addition/list-standard-addition.component';
import { ViewStandardAdditionComponent } from './standard-addition/view-standard-addition/view-standard-addition.component';



import { ProcessRequestComponent } from './process-request/process-request.component';
import { ProcessListComponent } from './process-list/process-list.component';
import { ProcessViewComponent } from './process-view/process-view.component';

import { AddUnitAdditionComponent } from './unit-addition/add-unit-addition/add-unit-addition.component';
import { ViewUnitAdditionComponent } from './unit-addition/view-unit-addition/view-unit-addition.component';
import { ListUnitAdditionComponent } from './unit-addition/list-unit-addition/list-unit-addition.component';
import { EditUnitAdditionComponent } from './unit-addition/edit-unit-addition/edit-unit-addition.component';
import { RequestUnitAdditionComponent } from './unit-addition/request-unit-addition/request-unit-addition.component';

import { AddProductAdditionComponent } from './product-addition/add-product-addition/add-product-addition.component';
import { ViewProductAdditionComponent } from './product-addition/view-product-addition/view-product-addition.component';
import { ListProductAdditionComponent } from './product-addition/list-product-addition/list-product-addition.component';
import { EditProductAdditionComponent } from './product-addition/edit-product-addition/edit-product-addition.component';
import { RequestProductAdditionComponent } from './product-addition/request-product-addition/request-product-addition.component';

import { RequestWithdrawUnitComponent } from './withdraw/request-withdraw-unit/request-withdraw-unit.component';
import { ViewWithdrawUnitComponent } from './withdraw/view-withdraw-unit/view-withdraw-unit.component';
import { ListWithdrawUnitComponent } from './withdraw/list-withdraw-unit/list-withdraw-unit.component';

import { AddChangeAddressComponent } from './add-change-address/add-change-address.component';
import { ListChangeAddressComponent } from './list-change-address/list-change-address.component';
import { EditChangeAddressComponent } from './edit-change-address/edit-change-address.component';
import { ViewChangeAddressComponent } from './view-change-address/view-change-address.component';



import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';
import { Rule } from '@app/models/rule';

const changeScopeRoutes: Routes = [
 { path: 'change-scope/process-addition/request',  component: ProcessAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },  
 { path: 'change-scope/process-addition/add',  component: ProcessRequestComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },  
 { path: 'change-scope/process-addition/list',  component: ProcessListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} }, 
 { path: 'change-scope/process-addition/view',  component: ProcessViewComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} }, 

 { path: 'change-scope/standard-addition/request',  component: RequestStandardAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },   
 { path: 'change-scope/standard-addition/add',  component: AddStandardAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },   
 { path: 'change-scope/standard-addition/view',  component: ViewStandardAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },   
 { path: 'change-scope/standard-addition/list',  component: ListStandardAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },   
 { path: 'change-scope/standard-addition/edit',  component: EditStandardAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },
 
 { path: 'change-scope/unit-addition/request',  component: RequestUnitAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },   
 { path: 'change-scope/unit-addition/add',  component: AddUnitAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },   
 { path: 'change-scope/unit-addition/view',  component: ViewUnitAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },   
 { path: 'change-scope/unit-addition/list',  component: ListUnitAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },   
 { path: 'change-scope/unit-addition/edit',  component: EditUnitAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },

 { path: 'change-scope/product-addition/request',  component: RequestProductAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]  } },   
 { path: 'change-scope/product-addition/add',  component: AddProductAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3] } },   
 { path: 'change-scope/product-addition/view',  component: ViewProductAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },   
 { path: 'change-scope/product-addition/list',  component: ListProductAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },   
 { path: 'change-scope/product-addition/edit',  component: EditProductAdditionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },    
 
 { path: 'change-scope/withdraw-unit/request',  component: RequestWithdrawUnitComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]  } },   
 { path: 'change-scope/withdraw-unit/view',  component: ViewWithdrawUnitComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },   
 { path: 'change-scope/withdraw-unit/list',  component: ListWithdrawUnitComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },
 
 { path: 'change-scope/change-address/add',  component: AddChangeAddressComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },
 { path: 'change-scope/change-address/edit',  component: EditChangeAddressComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },
 { path: 'change-scope/change-address/list',  component: ListChangeAddressComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },
 { path: 'change-scope/change-address/view',  component: ViewChangeAddressComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]} },
 

  
];

@NgModule({
  imports: [RouterModule.forChild(changeScopeRoutes)],
  exports: [RouterModule]
})
export class ChangeScopeRoutingModule { }
