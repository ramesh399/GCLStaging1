import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AppdetailComponent } from './appdetail/appdetail.component';
import {DemoMaterialModule} from '@app/material-module';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { NgSelectModule } from '@ng-select/ng-select';
import { NgbdSortableHeader } from '@app/helpers/sortable.directive';
import { UsermessageComponent } from './usermessage/usermessage.component';
import { DatePickerModule } from '@app/date-picker/date-picker.module';
import { MatSelectSearchComponent } from './mat-select-search/mat-select-search.component';
import { PopupmodalmessageComponent } from './popupmodalmessage/popupmodalmessage.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { AuditdetailComponent } from './auditdetail/auditdetail.component';
import { ProductadditiondetailComponent } from './productadditiondetail/productadditiondetail.component';
import { AuditClientinformationComponent } from './audit-clientinformation/audit-clientinformation.component';
import { AuditEnvironmentComponent } from './audit-environment/audit-environment.component';
import { AuditClientinformationProductcontrolsComponent } from './audit-clientinformation-productcontrols/audit-clientinformation-productcontrols.component';
import { AuditClientinformationSupplierComponent } from './audit-clientinformation-supplier/audit-clientinformation-supplier.component';
import { AuditClientinformationGeneralinfoComponent } from './audit-clientinformation-generalinfo/audit-clientinformation-generalinfo.component';
import { AuditClientinformationChecklistComponent } from './audit-clientinformation-checklist/audit-clientinformation-checklist.component';
import { AuditClientinformationViewchecklistComponent } from './audit-clientinformation-viewchecklist/audit-clientinformation-viewchecklist.component';
import { ApplicationReportDetailsComponent } from './application-report-details/application-report-details.component';
import { ViewAuditreportFilesComponent } from './view-auditreport-files/view-auditreport-files.component';
import { OfferdetailComponent } from './offerdetail/offerdetail.component';
import { ApplicationProductListComponent } from './application-product-list/application-product-list.component';
import { ApplicationProductDetailListComponent } from './application-product-detail-list/application-product-detail-list.component';
import { ProductAdditionProductEditComponent } from './product-addition-product-edit/product-addition-product-edit.component';
import { UserdetailComponent } from './userdetail/userdetail.component';
import { AppAuditConsentComponent } from './app-audit-consent/app-audit-consent.component';

@NgModule({
  declarations: [AppdetailComponent,NgbdSortableHeader, UsermessageComponent, MatSelectSearchComponent, PopupmodalmessageComponent, AuditdetailComponent, ProductadditiondetailComponent, AuditClientinformationComponent, AuditEnvironmentComponent, AuditClientinformationProductcontrolsComponent, AuditClientinformationSupplierComponent, AuditClientinformationGeneralinfoComponent, AuditClientinformationChecklistComponent, AuditClientinformationViewchecklistComponent, ApplicationReportDetailsComponent, ViewAuditreportFilesComponent, OfferdetailComponent, ApplicationProductListComponent, ApplicationProductDetailListComponent, ProductAdditionProductEditComponent, UserdetailComponent, AppAuditConsentComponent],
  exports: [RouterModule,DatePickerModule,AppdetailComponent,DemoMaterialModule,NgbModule,NgbdSortableHeader, UsermessageComponent,NgSelectModule,MatSelectSearchComponent,PopupmodalmessageComponent,AuditdetailComponent,ProductadditiondetailComponent, AuditClientinformationComponent, AuditEnvironmentComponent, AuditClientinformationProductcontrolsComponent, AuditClientinformationSupplierComponent, AuditClientinformationGeneralinfoComponent, AuditClientinformationChecklistComponent, AuditClientinformationViewchecklistComponent, ApplicationReportDetailsComponent, ViewAuditreportFilesComponent,OfferdetailComponent, ApplicationProductListComponent, ApplicationProductDetailListComponent,ProductAdditionProductEditComponent,UserdetailComponent,AppAuditConsentComponent],
  imports: [
    RouterModule,
    CommonModule,
    DemoMaterialModule,
    NgbModule,
    NgSelectModule,
    DatePickerModule,
    FormsModule,
    ReactiveFormsModule
  ]
})
export class CommonmodModule { }
