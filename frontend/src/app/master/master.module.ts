import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MasterRoutingModule } from './master-routing.module';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { NgbdSortableHeader } from '@app/helpers/sortable.directive';

import {CommonmodModule} from '@app/commonmod/commonmod.module';

import { CustomerAddComponent } from './customer/customer-add/customer-add.component';
import { AddCountryComponent } from './country/add-country/add-country.component';
import { EditCountryComponent } from './country/edit-country/edit-country.component';
import { ListCountryComponent } from './country/list-country/list-country.component';
import { ViewCountryComponent } from './country/view-country/view-country.component';
import { EditProductComponent } from './product/edit-product/edit-product.component';
import { AddProductComponent } from './product/add-product/add-product.component';
import { ListProductComponent } from './product/list-product/list-product.component';
import { ViewProductComponent } from './product/view-product/view-product.component';
import { ViewProcessComponent } from './process/view-process/view-process.component';
import { AddProcessComponent } from './process/add-process/add-process.component';
import { EditProcessComponent } from './process/edit-process/edit-process.component';
import { ListProcessComponent } from './process/list-process/list-process.component';
import { ListStateComponent } from './state/list-state/list-state.component';
import { AddStateComponent } from './state/add-state/add-state.component';
import { EditStateComponent } from './state/edit-state/edit-state.component';
import { ViewStateComponent } from './state/view-state/view-state.component';
import { ViewStandardComponent } from './standard/view-standard/view-standard.component';
import { AddStandardComponent } from './standard/add-standard/add-standard.component';
import { EditStandardComponent } from './standard/edit-standard/edit-standard.component';
import { ListStandardComponent } from './standard/list-standard/list-standard.component';
import { ListUserComponent } from './user/list-user/list-user.component';
import { AddUserComponent } from './user/add-user/add-user.component';
import { EditUserComponent } from './user/edit-user/edit-user.component';
import { ViewUserComponent } from './user/view-user/view-user.component';
import { EditCustomerComponent } from './customer/edit-customer/edit-customer.component';
import { ListCustomerComponent } from './customer/list-customer/list-customer.component';
import { ViewCustomerComponent } from './customer/view-customer/view-customer.component';
import { ViewAudittypeComponent } from './audittype/view-audittype/view-audittype.component';
import { ListAudittypeComponent } from './audittype/list-audittype/list-audittype.component';
import { EditAudittypeComponent } from './audittype/edit-audittype/edit-audittype.component';
import { AddAudittypeComponent } from './audittype/add-audittype/add-audittype.component';
import { AddChecklistComponent } from './checklist/add-checklist/add-checklist.component';
import { EditChecklistComponent } from './checklist/edit-checklist/edit-checklist.component';
import { ListChecklistComponent } from './checklist/list-checklist/list-checklist.component';
import { ViewChecklistComponent } from './checklist/view-checklist/view-checklist.component';
import { AddFranchiseComponent } from './franchise/add-franchise/add-franchise.component';
import { EditFranchiseComponent } from './franchise/edit-franchise/edit-franchise.component';
import { ListFranchiseComponent } from './franchise/list-franchise/list-franchise.component';
import { ViewFranchiseComponent } from './franchise/view-franchise/view-franchise.component';
import { AddMandaycostComponent } from './mandaycost/add-mandaycost/add-mandaycost.component';
import { EditMandaycostComponent } from './mandaycost/edit-mandaycost/edit-mandaycost.component';
import { ListMandaycostComponent } from './mandaycost/list-mandaycost/list-mandaycost.component';
import { AddOffertemplateComponent } from './offertemplate/add-offertemplate/add-offertemplate.component';
import { EditOffertemplateComponent } from './offertemplate/edit-offertemplate/edit-offertemplate.component';
import { ListOffertemplateComponent } from './offertemplate/list-offertemplate/list-offertemplate.component';
import { ViewOffertemplateComponent } from './offertemplate/view-offertemplate/view-offertemplate.component';
import { AddStandardreductionComponent } from './standardreduction/add-standardreduction/add-standardreduction.component';
import { ListStandardreductionComponent } from './standardreduction/list-standardreduction/list-standardreduction.component';
import { EditStandardreductionComponent } from './standardreduction/edit-standardreduction/edit-standardreduction.component';
import { ViewStandardreductionComponent } from './standardreduction/view-standardreduction/view-standardreduction.component';

import { TreeviewModule } from 'ngx-treeview';
import { AddUserroleComponent } from './userrole/add-userrole/add-userrole.component';
import { EditUserroleComponent } from './userrole/edit-userrole/edit-userrole.component';
import { ListUserroleComponent } from './userrole/list-userrole/list-userrole.component';
import { ViewUserroleComponent } from './userrole/view-userrole/view-userrole.component';
import { InspectiontimeComponent } from './inspectiontime/inspectiontime/inspectiontime.component';
import { LicensefeeComponent } from './licensefee/licensefee/licensefee.component';
import { AddStandardlabelgradeComponent } from './standardlabelgrade/add-standardlabelgrade/add-standardlabelgrade.component';
import { EditStandardlabelgradeComponent } from './standardlabelgrade/edit-standardlabelgrade/edit-standardlabelgrade.component';
import { ListStandardlabelgradeComponent } from './standardlabelgrade/list-standardlabelgrade/list-standardlabelgrade.component';
import { ListProducttypeComponent } from './producttype/list-producttype/list-producttype.component';
import { AddProducttypeComponent } from './producttype/add-producttype/add-producttype.component';
import { EditProducttypeComponent } from './producttype/edit-producttype/edit-producttype.component';
import { AddMaterialcompositionComponent } from './materialcomposition/add-materialcomposition/add-materialcomposition.component';
import { ListMaterialcompositionComponent } from './materialcomposition/list-materialcomposition/list-materialcomposition.component';
import { ViewMaterialcompositionComponent } from './materialcomposition/view-materialcomposition/view-materialcomposition.component';
import { EditMaterialcompositionComponent } from './materialcomposition/edit-materialcomposition/edit-materialcomposition.component';
import { AddQualificationChecklistComponent } from './checklist/add-qualification-checklist/add-qualification-checklist.component';
import { ViewQualificationChecklistComponent } from './checklist/view-qualification-checklist/view-qualification-checklist.component';
import { EditQualificationChecklistComponent } from './checklist/edit-qualification-checklist/edit-qualification-checklist.component';
import { ListQualificationChecklistComponent } from './checklist/list-qualification-checklist/list-qualification-checklist.component';
import { QualificationReviewComponent } from './user/qualification-review/qualification-review.component';
import { QualificationViewComponent } from './user/qualification-view/qualification-view.component';
import { SettingsComponent } from './settings/settings.component';

import { AddAuditPlanningChecklistComponent } from './checklist/add-audit-planning-checklist/add-audit-planning-checklist.component';
import { EditAuditPlanningChecklistComponent } from './checklist/edit-audit-planning-checklist/edit-audit-planning-checklist.component';
import { ViewAuditPlanningChecklistComponent } from './checklist/view-audit-planning-checklist/view-audit-planning-checklist.component';
import { ListAuditPlanningChecklistComponent } from './checklist/list-audit-planning-checklist/list-audit-planning-checklist.component';
import { ListAuditReviewerChecklistComponent } from './checklist/list-audit-reviewer-checklist/list-audit-reviewer-checklist.component';
import { AddAuditReviewerChecklistComponent } from './checklist/add-audit-reviewer-checklist/add-audit-reviewer-checklist.component';
import { EditAuditReviewerChecklistComponent } from './checklist/edit-audit-reviewer-checklist/edit-audit-reviewer-checklist.component';
import { ViewAuditReviewerChecklistComponent } from './checklist/view-audit-reviewer-checklist/view-audit-reviewer-checklist.component';
import { AddBusinessSectorComponent } from './business-sector/add-business-sector/add-business-sector.component';
import { EditBusinessSectorComponent } from './business-sector/edit-business-sector/edit-business-sector.component';
import { ListBusinessSectorComponent } from './business-sector/list-business-sector/list-business-sector.component';

import { AddBusinessSectorGroupComponent } from './business-sector-group/add-business-sector-group/add-business-sector-group.component';
import { EditBusinessSectorGroupComponent } from './business-sector-group/edit-business-sector-group/edit-business-sector-group.component';
import { ListBusinessSectorGroupComponent } from './business-sector-group/list-business-sector-group/list-business-sector-group.component';
import { ViewBusinessSectorGroupComponent } from './business-sector-group/view-business-sector-group/view-business-sector-group.component';

import { AuditExecutionSeverityComponent } from './audit-execution-severity/audit-execution-severity.component';

import { AddAuditExecutionChecklistComponent } from './checklist/add-audit-execution-checklist/add-audit-execution-checklist.component';
import { EditAuditExecutionChecklistComponent } from './checklist/edit-audit-execution-checklist/edit-audit-execution-checklist.component';
import { ViewAuditExecutionChecklistComponent } from './checklist/view-audit-execution-checklist/view-audit-execution-checklist.component';
import { ListAuditExecutionChecklistComponent } from './checklist/list-audit-execution-checklist/list-audit-execution-checklist.component';
import { AddSubTopicComponent } from './sub-topic/add-sub-topic/add-sub-topic.component';
import { EditSubTopicComponent } from './sub-topic/edit-sub-topic/edit-sub-topic.component';
import { ViewSubTopicComponent } from './sub-topic/view-sub-topic/view-sub-topic.component';
import { ListSubTopicComponent } from './sub-topic/list-sub-topic/list-sub-topic.component';
import { AddCbComponent } from './cb/add-cb/add-cb.component';
import { EditCbComponent } from './cb/edit-cb/edit-cb.component';
import { ViewCbComponent } from './cb/view-cb/view-cb.component';
import { ListCbComponent } from './cb/list-cb/list-cb.component';
import { AddReductionstandardComponent } from './reductionstandard/add-reductionstandard/add-reductionstandard.component';
import { EditReductionstandardComponent } from './reductionstandard/edit-reductionstandard/edit-reductionstandard.component';
import { ViewReductionstandardComponent } from './reductionstandard/view-reductionstandard/view-reductionstandard.component';
import { ListReductionstandardComponent } from './reductionstandard/list-reductionstandard/list-reductionstandard.component';

import { AddApplicationChecklistComponent } from './checklist/add-application-checklist/add-application-checklist.component';
import { ViewApplicationChecklistComponent } from './checklist/view-application-checklist/view-application-checklist.component';
import { EditApplicationChecklistComponent } from './checklist/edit-application-checklist/edit-application-checklist.component';
import { ListApplicationChecklistComponent } from './checklist/list-application-checklist/list-application-checklist.component';
import { SignatureComponent } from './signature/signature.component';
import { InspectionTimeReductionComponent } from './inspection-time-reduction/inspection-time-reduction.component';

import { StandardReductionMaximumComponent } from './standard-reduction-maximum/standard-reduction-maximum.component';
import { StandardCombinationComponent } from './standard-combination/standard-combination.component';
import { CustomerReportComponent } from './customer-report/customer-report.component';
import { CustomerDetailsComponent } from './customer-details/customer-details.component';

import { AddClientInformationQuestionComponent } from './checklist/add-client-information-question/add-client-information-question.component';
import { EditClientInformationQuestionComponent } from './checklist/edit-client-information-question/edit-client-information-question.component';
import { ViewClientInformationQuestionComponent } from './checklist/view-client-information-question/view-client-information-question.component';
import { ListClientInformationQuestionComponent } from './checklist/list-client-information-question/list-client-information-question.component';
import { AuditReportCategoryComponent } from './audit-report-category/audit-report-category.component';
import { AuditInterviewSocialCriteriaComponent } from './audit-interview-social-criteria/audit-interview-social-criteria.component';
import { AddClientlogoChecklistCustomerComponent } from './checklist/add-clientlogo-checklist-customer/add-clientlogo-checklist-customer.component';
import { EditClientlogoChecklistCustomerComponent } from './checklist/edit-clientlogo-checklist-customer/edit-clientlogo-checklist-customer.component';
import { ListClientlogoChecklistCustomerComponent } from './checklist/list-clientlogo-checklist-customer/list-clientlogo-checklist-customer.component';
import { ViewClientlogoChecklistCustomerComponent } from './checklist/view-clientlogo-checklist-customer/view-clientlogo-checklist-customer.component';
import { AddClientlogoChecklistHqComponent } from './checklist/add-clientlogo-checklist-hq/add-clientlogo-checklist-hq.component';
import { EditClientlogoChecklistHqComponent } from './checklist/edit-clientlogo-checklist-hq/edit-clientlogo-checklist-hq.component';
import { ListClientlogoChecklistHqComponent } from './checklist/list-clientlogo-checklist-hq/list-clientlogo-checklist-hq.component';
import { ViewClientlogoChecklistHqComponent } from './checklist/view-clientlogo-checklist-hq/view-clientlogo-checklist-hq.component';
import { AddRoyaltyFeeComponent } from './franchise/add-royalty-fee/add-royalty-fee.component';
import { StandardRoyaltyFeeComponent } from './franchise/add-royalty-fee/standard-royalty-fee/standard-royalty-fee.component';
import { TcRoyaltyFeeComponent } from './franchise/add-royalty-fee/tc-royalty-fee/tc-royalty-fee.component';
import { AuditStandardComponent } from './audit-standard/audit-standard.component';

import { AddComponent} from './brand/request/add/add.component';
import { EditComponent} from './brand/request/edit/edit.component';
import { ListComponent} from './brand/request/list/list.component';
import { ViewComponent} from './brand/request/view/view.component';
// import { AddSubStandardComponent } from './sub-standard/add-sub-standard/add-sub-standard.component';
// import { EditSubStandardComponent } from './sub-standard/edit-sub-standard/edit-sub-standard.component';
// import { ListSubStandardComponent } from './sub-standard/list-sub-standard/list-sub-standard.component';
// import { ViewSubStandardComponent } from './sub-standard/view-sub-standard/view-sub-standard.component';


// import { ViewSubDescriptionComponent } from './sub-description/view-sub-description/view-sub-description.component';
// import { AddSubDescriptionComponent } from './sub-description/add-sub-description/add-sub-description.component';
// import { ListSubDescriptionComponent } from './sub-description/list-sub-description/list-sub-description.component';
// import { EditSubDescriptionComponent } from './sub-description/edit-sub-description/edit-sub-description.component';
import { ListAuditStandardComponent } from './audit-standard/list-audit-standard/list-audit-standard.component';
import { BrandComponent } from './brand/brand.component';
import { ListBrandComponent } from './brand/list-brand/list-brand.component';
import { AddBrandGroupComponent } from './brand-group/request/add/add-brand-group.component';
import { EditBrandGroupComponent } from './brand-group/request/edit/edit-brand-group.component';
import { ListBrandGroupComponent } from './brand-group/request/list/list-brand-group.component';
import { ViewBrandGroupComponent } from './brand-group/request/view/view-brand-group.component';


@NgModule({

  declarations: [ CustomerAddComponent, 
    // AddSubStandardComponent,
    // EditSubStandardComponent,
    // ListSubStandardComponent,
    // ViewSubStandardComponent,
    // ViewSubDescriptionComponent,
    // AddSubDescriptionComponent,
    // ListSubDescriptionComponent,
    // EditSubDescriptionComponent,
    AddComponent,
    EditComponent,
    ListComponent,
    ViewComponent,
    AuditStandardComponent,  AddCountryComponent, EditCountryComponent, ListCountryComponent, ViewCountryComponent, EditProductComponent, AddProductComponent, ListProductComponent, ViewProductComponent, ViewProcessComponent, AddProcessComponent, EditProcessComponent, ListProcessComponent, ListStateComponent, AddStateComponent, EditStateComponent, ViewStateComponent, ViewStandardComponent, AddStandardComponent, EditStandardComponent, ListStandardComponent, ListUserComponent, AddUserComponent, EditUserComponent, ViewUserComponent, EditCustomerComponent, ListCustomerComponent, ViewCustomerComponent, ViewAudittypeComponent, ListAudittypeComponent, EditAudittypeComponent, AddAudittypeComponent, AddChecklistComponent, EditChecklistComponent, ListChecklistComponent, ViewChecklistComponent, AddFranchiseComponent, EditFranchiseComponent, ListFranchiseComponent, ViewFranchiseComponent, AddMandaycostComponent, EditMandaycostComponent, ListMandaycostComponent, AddOffertemplateComponent, EditOffertemplateComponent, ListOffertemplateComponent, ViewOffertemplateComponent, AddUserroleComponent, EditUserroleComponent, ListUserroleComponent, ViewUserroleComponent, AddStandardreductionComponent, ListStandardreductionComponent, EditStandardreductionComponent, ViewStandardreductionComponent, InspectiontimeComponent, LicensefeeComponent, AddStandardlabelgradeComponent, EditStandardlabelgradeComponent, ListStandardlabelgradeComponent, ListProducttypeComponent, AddProducttypeComponent, EditProducttypeComponent, AddMaterialcompositionComponent, ListMaterialcompositionComponent, ViewMaterialcompositionComponent, EditMaterialcompositionComponent, AddQualificationChecklistComponent, ViewQualificationChecklistComponent, EditQualificationChecklistComponent, ListQualificationChecklistComponent, QualificationReviewComponent, QualificationViewComponent, SettingsComponent, AddAuditPlanningChecklistComponent, EditAuditPlanningChecklistComponent, ViewAuditPlanningChecklistComponent, ListAuditPlanningChecklistComponent, ListAuditReviewerChecklistComponent, AddAuditReviewerChecklistComponent, EditAuditReviewerChecklistComponent, ViewAuditReviewerChecklistComponent, AddBusinessSectorComponent, EditBusinessSectorComponent, ListBusinessSectorComponent, AddBusinessSectorGroupComponent, EditBusinessSectorGroupComponent, ListBusinessSectorGroupComponent, ViewBusinessSectorGroupComponent, AuditExecutionSeverityComponent, AddAuditExecutionChecklistComponent, EditAuditExecutionChecklistComponent, ViewAuditExecutionChecklistComponent, ListAuditExecutionChecklistComponent,AddSubTopicComponent, EditSubTopicComponent, ViewSubTopicComponent, ListSubTopicComponent, AddCbComponent, EditCbComponent, ViewCbComponent, ListCbComponent, AddReductionstandardComponent, EditReductionstandardComponent, ViewReductionstandardComponent, ListReductionstandardComponent, AddApplicationChecklistComponent, ViewApplicationChecklistComponent, EditApplicationChecklistComponent, ListApplicationChecklistComponent, SignatureComponent, InspectionTimeReductionComponent, StandardReductionMaximumComponent, StandardCombinationComponent, CustomerReportComponent, CustomerDetailsComponent,  AddClientInformationQuestionComponent, EditClientInformationQuestionComponent, ViewClientInformationQuestionComponent, ListClientInformationQuestionComponent, AuditReportCategoryComponent, AuditInterviewSocialCriteriaComponent, AddClientlogoChecklistCustomerComponent, EditClientlogoChecklistCustomerComponent, ListClientlogoChecklistCustomerComponent, ViewClientlogoChecklistCustomerComponent, AddClientlogoChecklistHqComponent, EditClientlogoChecklistHqComponent, ListClientlogoChecklistHqComponent, ViewClientlogoChecklistHqComponent, AddRoyaltyFeeComponent, StandardRoyaltyFeeComponent, TcRoyaltyFeeComponent, ListAuditStandardComponent, BrandComponent, ListBrandComponent, AddBrandGroupComponent, EditBrandGroupComponent, ListBrandGroupComponent, ViewBrandGroupComponent],

  imports: [
    CommonModule,
	  MasterRoutingModule,
	  FormsModule,
    NgbModule,
    ReactiveFormsModule,
    BrowserModule,
	  CommonmodModule,
    TreeviewModule.forRoot()
  ]
})
export class MasterModule { }
