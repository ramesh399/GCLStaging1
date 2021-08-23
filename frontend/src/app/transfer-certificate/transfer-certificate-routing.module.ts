import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { BuyerComponent } from './buyer/buyer.component';
import { ConsigneeComponent } from './consignee/consignee.component';
import { RawMaterialComponent } from './raw-material/raw-material.component';
import { InspectionBodyComponent } from './inspection-body/inspection-body.component';

import { AddRequestComponent } from './request/add-request/add-request.component';
import { ListRequestComponent } from './request/list-request/list-request.component';
import { EditRequestComponent } from './request/edit-request/edit-request.component';
import { ViewRequestComponent } from './request/view-request/view-request.component';

import { RawMaterialStandardComponent } from './raw-material-standard/raw-material-standard.component';
import { RawMaterialStandardLabelGradeComponent } from './raw-material-standard-label-grade/raw-material-standard-label-grade.component';

import { RawMaterialStandardCombinationComponent } from './raw-material-standard-combination/raw-material-standard-combination.component';

import { IfoamStandardComponent } from './ifoam-standard/ifoam-standard.component';
import { TcInvoiceComponent } from './tc-invoice/tc-invoice.component';

import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';
import { Rule } from '@app/models/rule';

const tcRoutes: Routes = [
 { path: 'transaction-certificate/buyer/index',  component: BuyerComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2],rules:'buyer',rulewithtype:1,pageType:'buyer' } },
 { path: 'transaction-certificate/seller/index',  component: BuyerComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2],rules:'seller',rulewithtype:1,pageType:'seller' } },
 { path: 'transaction-certificate/consignee/index',  component: BuyerComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2],rules:'consignee',rulewithtype:1,pageType:'consignee' } },
 { path: 'transaction-certificate/raw-material/index',  component: RawMaterialComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2],rules:'raw_material',rulewithtype:1 } },  
 { path: 'transaction-certificate/certification-body/index',  component: InspectionBodyComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'certification_body',rulewithtype:1,pageType:'certification' } },   
 { path: 'transaction-certificate/inspection-body/index',  component: InspectionBodyComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'inspection_body',rulewithtype:1,pageType:'inspection' } },   

 { path: 'transaction-certificate/request/add',  component: AddRequestComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3],rules:'add_tc_application',rulewithtype:1  } }, 
 { path: 'transaction-certificate/request/edit',  component: AddRequestComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3],rules:'edit_tc_application',secrules:['assign_as_oss_review_for_tc'],rulewithtype:1   } }, 
 { path: 'transaction-certificate/request/view',  component: ViewRequestComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]  }}, //,rulewithtype:1
 { path: 'transaction-certificate/request/list',  component: ListRequestComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1,2,3]  }},   //,rules:'tc_application',rulewithtype:1
 
 { path: 'transaction-certificate/standard/index',  component: RawMaterialStandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'tc_standard' } },
 { path: 'transaction-certificate/ifoam-standard/index',  component: IfoamStandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'tc_standard' } },
 { path: 'transaction-certificate/standard-label-grade/index',  component: RawMaterialStandardLabelGradeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'tc_standard_label_grade' } },
 
 { path: 'transaction-certificate/standard-combination/index',  component: RawMaterialStandardCombinationComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'tc_standard_combination' } },

 { path: 'transaction-certificate/approved-tc-applications',  component: TcInvoiceComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],pageType:1 } },
 { path: 'transaction-certificate/invoices-for-generation',  component: TcInvoiceComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],pageType:2 } },
 
 
];

@NgModule({
  imports: [ RouterModule.forChild(tcRoutes)],
  exports: [RouterModule]
})
export class TransferCertificateRoutingModule { }
