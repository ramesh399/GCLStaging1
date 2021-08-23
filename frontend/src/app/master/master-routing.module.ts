import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { CustomerAddComponent } from './customer/customer-add/customer-add.component';

import { AddUserComponent } from './user/add-user/add-user.component';
import { EditUserComponent } from './user/edit-user/edit-user.component';
import { ListUserComponent } from './user/list-user/list-user.component';
import { ViewUserComponent } from './user/view-user/view-user.component';

import { QualificationReviewComponent } from './user/qualification-review/qualification-review.component';

import { QualificationViewComponent } from './user/qualification-view/qualification-view.component';

import { AddCountryComponent } from './country/add-country/add-country.component';
import { EditCountryComponent } from './country/edit-country/edit-country.component';
import { ListCountryComponent } from './country/list-country/list-country.component';
import { ViewCountryComponent } from './country/view-country/view-country.component';

import { EditCustomerComponent } from './customer/edit-customer/edit-customer.component';
import { ListCustomerComponent } from './customer/list-customer/list-customer.component';
import { ViewCustomerComponent } from './customer/view-customer/view-customer.component';

import { AddAudittypeComponent } from './audittype/add-audittype/add-audittype.component';
import { EditAudittypeComponent } from './audittype/edit-audittype/edit-audittype.component';
import { ListAudittypeComponent } from './audittype/list-audittype/list-audittype.component';
import { ViewAudittypeComponent } from './audittype/view-audittype/view-audittype.component';

import { AddStateComponent } from './state/add-state/add-state.component';
import { EditStateComponent } from './state/edit-state/edit-state.component';
import { ListStateComponent } from './state/list-state/list-state.component';
import { ViewStateComponent } from './state/view-state/view-state.component';

import { AddProductComponent } from './product/add-product/add-product.component';
import { EditProductComponent } from './product/edit-product/edit-product.component';
import { ListProductComponent } from './product/list-product/list-product.component';
import { ViewProductComponent } from './product/view-product/view-product.component';

import { AddProcessComponent } from './process/add-process/add-process.component';
import { EditProcessComponent } from './process/edit-process/edit-process.component';
import { ListProcessComponent } from './process/list-process/list-process.component';
import { ViewProcessComponent } from './process/view-process/view-process.component';

import { AddStandardComponent } from './standard/add-standard/add-standard.component';
import { EditStandardComponent } from './standard/edit-standard/edit-standard.component';
import { ListStandardComponent } from './standard/list-standard/list-standard.component';
import { ViewStandardComponent } from './standard/view-standard/view-standard.component';

import { AddChecklistComponent } from './checklist/add-checklist/add-checklist.component';
import { EditChecklistComponent } from './checklist/edit-checklist/edit-checklist.component';
import { ListChecklistComponent } from './checklist/list-checklist/list-checklist.component';
import { ViewChecklistComponent } from './checklist/view-checklist/view-checklist.component';

import { AddQualificationChecklistComponent } from './checklist/add-qualification-checklist/add-qualification-checklist.component';
import { EditQualificationChecklistComponent } from './checklist/edit-qualification-checklist/edit-qualification-checklist.component';
import { ListQualificationChecklistComponent } from './checklist/list-qualification-checklist/list-qualification-checklist.component';
import { ViewQualificationChecklistComponent } from './checklist/view-qualification-checklist/view-qualification-checklist.component';

import { AddFranchiseComponent } from './franchise/add-franchise/add-franchise.component';
import { EditFranchiseComponent } from './franchise/edit-franchise/edit-franchise.component';
import { ListFranchiseComponent } from './franchise/list-franchise/list-franchise.component';
import { ViewFranchiseComponent } from './franchise/view-franchise/view-franchise.component';
import { AddRoyaltyFeeComponent } from './franchise/add-royalty-fee/add-royalty-fee.component';

import { AddMandaycostComponent } from './mandaycost/add-mandaycost/add-mandaycost.component';
import { EditMandaycostComponent } from './mandaycost/edit-mandaycost/edit-mandaycost.component';
import { ListMandaycostComponent } from './mandaycost/list-mandaycost/list-mandaycost.component';

import { AddOffertemplateComponent } from './offertemplate/add-offertemplate/add-offertemplate.component';
import { EditOffertemplateComponent } from './offertemplate/edit-offertemplate/edit-offertemplate.component';
import { ListOffertemplateComponent } from './offertemplate/list-offertemplate/list-offertemplate.component';
import { ViewOffertemplateComponent } from './offertemplate/view-offertemplate/view-offertemplate.component';

import { AddStandardreductionComponent } from './standardreduction/add-standardreduction/add-standardreduction.component';
import { EditStandardreductionComponent } from './standardreduction/edit-standardreduction/edit-standardreduction.component';
import { ListStandardreductionComponent } from './standardreduction/list-standardreduction/list-standardreduction.component';
import { ViewStandardreductionComponent } from './standardreduction/view-standardreduction/view-standardreduction.component';

import { AddUserroleComponent } from './userrole/add-userrole/add-userrole.component';
import { EditUserroleComponent } from './userrole/edit-userrole/edit-userrole.component';
import { ListUserroleComponent } from './userrole/list-userrole/list-userrole.component';

import { AddStandardlabelgradeComponent } from './standardlabelgrade/add-standardlabelgrade/add-standardlabelgrade.component';
import { EditStandardlabelgradeComponent } from './standardlabelgrade/edit-standardlabelgrade/edit-standardlabelgrade.component';
import { ListStandardlabelgradeComponent } from './standardlabelgrade/list-standardlabelgrade/list-standardlabelgrade.component';

import { AddProducttypeComponent } from './producttype/add-producttype/add-producttype.component';
import { EditProducttypeComponent } from './producttype/edit-producttype/edit-producttype.component';
import { ListProducttypeComponent } from './producttype/list-producttype/list-producttype.component';

import { InspectiontimeComponent } from './inspectiontime/inspectiontime/inspectiontime.component';

import { LicensefeeComponent } from './licensefee/licensefee/licensefee.component';

import { AddMaterialcompositionComponent } from './materialcomposition/add-materialcomposition/add-materialcomposition.component';
import { EditMaterialcompositionComponent } from './materialcomposition/edit-materialcomposition/edit-materialcomposition.component';
import { ListMaterialcompositionComponent } from './materialcomposition/list-materialcomposition/list-materialcomposition.component';

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
import { ListAuditExecutionChecklistComponent } from './checklist/list-audit-execution-checklist/list-audit-execution-checklist.component';
import { ViewAuditExecutionChecklistComponent } from './checklist/view-audit-execution-checklist/view-audit-execution-checklist.component';


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
import { ListReductionstandardComponent } from './reductionstandard/list-reductionstandard/list-reductionstandard.component';
import { ViewReductionstandardComponent } from './reductionstandard/view-reductionstandard/view-reductionstandard.component';

import { AddApplicationChecklistComponent } from './checklist/add-application-checklist/add-application-checklist.component';
import { EditApplicationChecklistComponent } from './checklist/edit-application-checklist/edit-application-checklist.component';
import { ViewApplicationChecklistComponent } from './checklist/view-application-checklist/view-application-checklist.component';
import { ListApplicationChecklistComponent } from './checklist/list-application-checklist/list-application-checklist.component';

import { StandardReductionMaximumComponent } from './standard-reduction-maximum/standard-reduction-maximum.component';

import { InspectionTimeReductionComponent } from './inspection-time-reduction/inspection-time-reduction.component';

import { StandardCombinationComponent } from './standard-combination/standard-combination.component';

import { SignatureComponent } from './signature/signature.component';
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
import { AuditStandardComponent } from './audit-standard/audit-standard.component';
//import { EnquiryViewComponent } from './enquiry-view/enquiry-view.component';
import { ListAuditStandardComponent } from './audit-standard/list-audit-standard/list-audit-standard.component';

import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';
import { Rule } from '@app/models/rule';
import { BrandComponent } from './brand/brand.component';
import { ListBrandComponent } from './brand/list-brand/list-brand.component';
import { AddComponent } from '@app/master/brand/request/add/add.component';
import { EditComponent } from '@app/master/brand/request/edit/edit.component';
import { ListComponent } from '@app/master/brand/request/list/list.component';
import { ViewComponent } from '@app/master/brand/request/view/view.component';

import { AddBrandGroupComponent } from '@app/master/brand-group/request/add/add-brand-group.component';
import { EditBrandGroupComponent } from '@app/master/brand-group/request/edit/edit-brand-group.component';
import { ListBrandGroupComponent } from '@app/master/brand-group/request/list/list-brand-group.component';
import { ViewBrandGroupComponent } from '@app/master/brand-group/request/view/view-brand-group.component';

const masterRoutes: Routes = [
  
  { path: 'master/audit-interview-sampling-plan/index',  component: AuditInterviewSocialCriteriaComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'audit_interview_sampling_plan_master', roles: [Role.Admin]} },

  { path: 'master/interview-requirement/index',  component: AuditReportCategoryComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],pageType:'interview_requirement' } },
  { path: 'master/client-information/index',  component: AuditReportCategoryComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],pageType:'client_information' } },
  { path: 'master/living-requirement/index',  component: AuditReportCategoryComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],pageType:'living_requirement' } },
  { path: 'master/living-category/index',  component: AuditReportCategoryComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],pageType:'living_category' } },

  { path: 'master/signature/index',  component: SignatureComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'signature_master', roles: [Role.Admin]} },

  { path: 'master/userrole/add',  component: AddUserroleComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'add_user_role',roles: [Role.Admin] } },
  { path: 'master/userrole/edit',  component: EditUserroleComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'edit_user_role', roles: [Role.Admin] } },
  { path: 'master/userrole/list',  component: ListUserroleComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'user_role_master', roles: [Role.Admin] } },
  
  { path: 'master/standardlabelgrade/add',  component: AddStandardlabelgradeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'add_standard_label_grade', roles: [Role.Admin] } },
  { path: 'master/standardlabelgrade/edit',  component: EditStandardlabelgradeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'edit_standard_label_grade', roles: [Role.Admin] } },
  { path: 'master/standardlabelgrade/list',  component: ListStandardlabelgradeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'standard_label_grade_master', roles: [Role.Admin] } },
  
  { path: 'master/producttype/add',  component: AddProducttypeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'add_product_description', roles: [Role.Admin] } },
  { path: 'master/producttype/edit',  component: EditProducttypeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'edit_product_description',  roles: [Role.Admin] } },
  { path: 'master/producttype/list',  component: ListProducttypeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'product_description_master',  roles: [Role.Admin] } },
     
  { path: 'master/user/add',  component: AddUserComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,3],userCanAccess:[3], rules:Rule.AddUser, roles: [Role.Admin] } },
  { path: 'master/user/edit',  component: EditUserComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,3], userCanAccess:[1,3], rules:Rule.UserMaster, roles: [Role.Admin] } },
  { path: 'master/user/list',  component: ListUserComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,3], userCanAccess:[3], rules:Rule.UserMaster, roles: [Role.Admin] } },
  { path: 'master/user/view',  component: ViewUserComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,3], userCanAccess:[1,3], rules:Rule.UserMaster, roles: [Role.Admin] } },
  
  { path: 'master/user/qualification-review',  component: QualificationReviewComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], roles: [Role.Admin] } },
  { path: 'master/user/qualification-view',  component: QualificationViewComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], roles: [Role.Admin] } },
  
  { path: 'master/customer/add',  component: CustomerAddComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'add_customer',roles: [Role.Admin] } },  
  { path: 'master/customer/edit',  component: EditCustomerComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'edit_customer',roles: [Role.Admin] } },
  { path: 'master/customer/list',  component: ListCustomerComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:'customer_master', roles: [Role.Admin] } },
  { path: 'master/customer/view',  component: ViewCustomerComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:'customer_master', roles: [Role.Admin] } },
  
  { path : 'master/brand', component : BrandComponent, canLoad:[AuthGuard],canActivate:[AuthGuard], data : {userType : [1],roles:[Role.Admin]}},
  { path : 'master/list-brand', component : ListBrandComponent, canLoad:[AuthGuard],canActivate:[AuthGuard], data : {userType : [1],roles:[Role.Admin]}},

  { path: 'master/country/add',  component: AddCountryComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:Rule.AddCountry, roles: [Role.Admin] } },
  { path: 'master/country/edit',  component: EditCountryComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:Rule.EditCountry, roles: [Role.Admin] } },
  { path: 'master/country/list',  component: ListCountryComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:Rule.CountryMaster, roles: [Role.Admin] } },
  { path: 'master/country/view',  component: ViewCountryComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:Rule.CountryMaster, roles: [Role.Admin] } },
  
  { path: 'master/franchise/add',  component: AddFranchiseComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'add_franchise',roles: [Role.Admin] } },
  { path: 'master/franchise/edit',  component: EditFranchiseComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:'edit_franchise', roles: [Role.Admin] } },
  { path: 'master/franchise/list',  component: ListFranchiseComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:'franchise_master', roles: [Role.Admin] } },
  { path: 'master/franchise/view',  component: ViewFranchiseComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:'franchise_master', roles: [Role.Admin] } },
  { path: 'master/franchise/add-royalty',  component: AddRoyaltyFeeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:'franchise_master', roles: [Role.Admin] } },

  { path: 'master/brand/request/add',  component: AddComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'add_franchise',roles: [Role.Admin] } },
  { path: 'master/brand/request/edit',  component: EditComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'add_franchise',roles: [Role.Admin] } },
  { path: 'master/brand/request/list',  component: ListComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'add_franchise',roles: [Role.Admin] } },
  { path: 'master/brand/request/view',  component: ViewComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'add_franchise',roles: [Role.Admin] } },

  { path: 'master/brand-group/request/add',  component: AddBrandGroupComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'add_franchise',roles: [Role.Admin] } },
  { path: 'master/brand-group/request/edit',  component: EditBrandGroupComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'add_franchise',roles: [Role.Admin] } },
  { path: 'master/brand-group/request/list',  component: ListBrandGroupComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'add_franchise',roles: [Role.Admin] } },
  { path: 'master/brand-group/request/view',  component: ViewBrandGroupComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:'add_franchise',roles: [Role.Admin] } },
  

  { path: 'master/audittype/add',  component: AddAudittypeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'add_audit_type', roles: [Role.Admin] } },
  { path: 'master/audittype/edit',  component: EditAudittypeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'edit_audit_type', roles: [Role.Admin] } },
  { path: 'master/audittype/list',  component: ListAudittypeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'audit_type_master', roles: [Role.Admin] } },
  { path: 'master/audittype/view',  component: ViewAudittypeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'audit_type_master', roles: [Role.Admin] } },
  
  { path: 'master/state/add',  component: AddStateComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:Rule.AddState, roles: [Role.Admin] } },
  { path: 'master/state/edit',  component: EditStateComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:Rule.EditState, roles: [Role.Admin] } },
  { path: 'master/state/list',  component: ListStateComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:Rule.StateMaster, roles: [Role.Admin] } },
  { path: 'master/state/view',  component: ViewStateComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],rules:Rule.StateMaster, roles: [Role.Admin] } },
  
  { path: 'master/product/add',  component: AddProductComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'add_product_category', roles: [Role.Admin] } },
  { path: 'master/product/edit',  component: EditProductComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'edit_product_category', roles: [Role.Admin] } },
  { path: 'master/product/list',  component: ListProductComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'product_category_master', roles: [Role.Admin] } },
  { path: 'master/product/view',  component: ViewProductComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'product_category_master', roles: [Role.Admin] } },
  
  { path: 'master/process/add',  component: AddProcessComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_process', roles: [Role.Admin] } },
  { path: 'master/process/edit',  component: EditProcessComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'edit_process', roles: [Role.Admin] } },
  { path: 'master/process/list',  component: ListProcessComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'process_master',roles: [Role.Admin] } },
  { path: 'master/process/view',  component: ViewProcessComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'process_master',roles: [Role.Admin] } },
  
  { path: 'master/standard/add',  component: AddStandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_standard', roles: [Role.Admin] } },
  { path: 'master/standard/edit',  component: EditStandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'edit_standard', roles: [Role.Admin] } },
  { path: 'master/standard/list',  component: ListStandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'standard_master', roles: [Role.Admin] } },
  { path: 'master/standard/view',  component: ViewStandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'standard_master', roles: [Role.Admin] } },
  
  { path: 'master/checklist/add',  component: AddChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1] , roles: [Role.Admin] } },
  { path: 'master/checklist/edit',  component: EditChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1] , roles: [Role.Admin] } },
  { path: 'master/checklist/list',  component: ListChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1] , roles: [Role.Admin] } },
  { path: 'master/checklist/view',  component: ViewChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1] , roles: [Role.Admin] } },
  
  { path: 'master/qualification-checklist/add',  component: AddQualificationChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'add_qualification_checklist', roles: [Role.Admin] } },
  { path: 'master/qualification-checklist/edit',  component: EditQualificationChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'edit_qualification_checklist', roles: [Role.Admin] } },
  { path: 'master/qualification-checklist/list',  component: ListQualificationChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'qualification_checklist_master', roles: [Role.Admin] } },
  { path: 'master/qualification-checklist/view',  component: ViewQualificationChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'qualification_checklist_master', roles: [Role.Admin] } },
    
  { path: 'master/mandaycost/add',  component: AddMandaycostComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_man_day_cost', roles: [Role.Admin] } },
  { path: 'master/mandaycost/edit',  component: EditMandaycostComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'edit_man_day_cost', roles: [Role.Admin] } },
  { path: 'master/mandaycost/list',  component: ListMandaycostComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'man_day_cost_tax_master', roles: [Role.Admin] } },
    
  { path: 'master/offertemplate/add',  component: AddOffertemplateComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], roles: [Role.Admin] } },
  { path: 'master/offertemplate/edit',  component: EditOffertemplateComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], roles: [Role.Admin] } },
  { path: 'master/offertemplate/list',  component: ListOffertemplateComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], roles: [Role.Admin] } },
  { path: 'master/offertemplate/view',  component: ViewOffertemplateComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], roles: [Role.Admin] } },
  
  { path: 'master/standardreduction/add',  component: AddStandardreductionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_standard_reduction', roles: [Role.Admin] } },
  { path: 'master/standardreduction/edit',  component: EditStandardreductionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'edit_standard_reduction', roles: [Role.Admin] } },
  { path: 'master/standardreduction/list',  component: ListStandardreductionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'standard_reduction_master', roles: [Role.Admin] } },
  { path: 'master/standardreduction/view',  component: ViewStandardreductionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'standard_reduction_master', roles: [Role.Admin] } },
  
  { path: 'master/standard-reduction-maximum/list',  component: StandardReductionMaximumComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'inspection_time_master', roles: [Role.Admin] } },  
  
  
  { path: 'master/inspectiontime/list',  component: InspectiontimeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'inspection_time_master', roles: [Role.Admin] } },
  { path: 'master/inspection-time-reduction/list',  component: InspectionTimeReductionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'inspection_time_master', roles: [Role.Admin] } },  
  
  { path: 'master/licensefee/list',  component: LicensefeeComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'license_fee_master', roles: [Role.Admin] } },
  
  { path: 'master/materialcomposition/add',  component: AddMaterialcompositionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'add_material', roles: [Role.Admin] } },
  { path: 'master/materialcomposition/edit',  component: EditMaterialcompositionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'edit_material', roles: [Role.Admin] } },
  { path: 'master/materialcomposition/list',  component: ListMaterialcompositionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'material_master', roles: [Role.Admin] } },
  
  { path: 'master/settings',  component: SettingsComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], roles: [Role.Admin] } },
  
  { path: 'master/audit-planning-checklist/add',  component: AddAuditPlanningChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],  roles: [Role.Admin] } },
  { path: 'master/audit-planning-checklist/edit',  component: EditAuditPlanningChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], roles: [Role.Admin] } },
  { path: 'master/audit-planning-checklist/view',  component: ViewAuditPlanningChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],  roles: [Role.Admin] } },
  { path: 'master/audit-planning-checklist/list',  component: ListAuditPlanningChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1],   roles: [Role.Admin] } }, 

  { path: 'master/application-checklist/add',  component: AddApplicationChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_application_checklist', roles: [Role.Admin] } },
  { path: 'master/application-checklist/edit',  component: EditApplicationChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'edit_application_checklist', roles: [Role.Admin] } },
  { path: 'master/application-checklist/view',  component: ViewApplicationChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'application_checklist_master', roles: [Role.Admin] } },
  { path: 'master/application-checklist/list',  component: ListApplicationChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'application_checklist_master', roles: [Role.Admin] } }, 
  
  { path: 'master/audit-reviewer-checklist/add',  component: AddAuditReviewerChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_audit_reviewer_review_checklist', roles: [Role.Admin] } },
  { path: 'master/audit-reviewer-checklist/edit',  component: EditAuditReviewerChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'edit_audit_reviewer_review_checklist', roles: [Role.Admin] } },
  { path: 'master/audit-reviewer-checklist/view',  component: ViewAuditReviewerChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'audit_reviewer_review_checklist_master', roles: [Role.Admin] } },
  { path: 'master/audit-reviewer-checklist/list',  component: ListAuditReviewerChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'audit_reviewer_review_checklist_master', roles: [Role.Admin] } }, 

  { path: 'master/business-sector/add',  component: AddBusinessSectorComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_business_sector', roles: [Role.Admin] } },
  { path: 'master/business-sector/edit',  component: EditBusinessSectorComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'edit_business_sector', roles: [Role.Admin] } },
  { path: 'master/business-sector/list',  component: ListBusinessSectorComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'business_sector_master', roles: [Role.Admin] } },
  
  { path: 'master/business-sector-group/add',  component: AddBusinessSectorGroupComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_business_sector_group', roles: [Role.Admin] } },
  { path: 'master/business-sector-group/edit',  component: EditBusinessSectorGroupComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'edit_business_sector_group', roles: [Role.Admin] } },
  { path: 'master/business-sector-group/list',  component: ListBusinessSectorGroupComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'business_sector_group_master', roles: [Role.Admin] } },
  { path: 'master/business-sector-group/view',  component: ViewBusinessSectorGroupComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'business_sector_group_master', roles: [Role.Admin] } },

  { path: 'master/audit-execution-severity',  component: AuditExecutionSeverityComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'', roles: [Role.Admin] } },

  { path: 'master/audit-execution-checklist/add',  component: AddAuditExecutionChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_audit_execution_checklist', roles: [Role.Admin] } },
  { path: 'master/audit-execution-checklist/edit',  component: EditAuditExecutionChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'edit_audit_execution_checklist', roles: [Role.Admin] } },
  { path: 'master/audit-execution-checklist/list',  component: ListAuditExecutionChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'audit_execution_checklist_master', roles: [Role.Admin] } },
  { path: 'master/audit-execution-checklist/view',  component: ViewAuditExecutionChecklistComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'audit_execution_checklist_master', roles: [Role.Admin] } },

  { path: 'master/sub-topic/add',  component: AddSubTopicComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_sub_topic', roles: [Role.Admin] } },
  { path: 'master/sub-topic/edit',  component: EditSubTopicComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'edit_sub_topic', roles: [Role.Admin] } },
  { path: 'master/sub-topic/list',  component: ListSubTopicComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'sub_topic_master',roles: [Role.Admin] } },
  { path: 'master/sub-topic/view',  component: ViewSubTopicComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'sub_topic_master',roles: [Role.Admin] } },  
  
  { path: 'master/cb/add',  component: AddCbComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_cb', roles: [Role.Admin] } },
  { path: 'master/cb/edit',  component: EditCbComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'edit_cb', roles: [Role.Admin] } },
  { path: 'master/cb/list',  component: ListCbComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'cb_master',roles: [Role.Admin] } },
  { path: 'master/cb/view',  component: ViewCbComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'cb_master',roles: [Role.Admin] } },  

  { path: 'master/reductionstandard/add',  component: AddReductionstandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard] },
  { path: 'master/reductionstandard/edit',  component: EditReductionstandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard] },
  { path: 'master/reductionstandard/list',  component: ListReductionstandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard] },
  { path: 'master/reductionstandard/view',  component: ViewReductionstandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard] },

  { path: 'master/standard-combination/index',  component: StandardCombinationComponent,canLoad: [AuthGuard],canActivate: [AuthGuard] },
  
  { path: 'master/customer-report/index',  component: CustomerReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard] },
  { path: 'master/customer-details/index',  component: CustomerDetailsComponent,canLoad: [AuthGuard],canActivate: [AuthGuard] },

  { path: 'master/client-information-question/add',  component: AddClientInformationQuestionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_client_information_question_checklist', roles: [Role.Admin] } },
  { path: 'master/client-information-question/edit',  component: EditClientInformationQuestionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'edit_client_information_question_checklist', roles: [Role.Admin] } },
  { path: 'master/client-information-question/view',  component: ViewClientInformationQuestionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'client_information_question_checklist_master', roles: [Role.Admin] } },
  { path: 'master/client-information-question/list',  component: ListClientInformationQuestionComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'client_information_question_checklist_master', roles: [Role.Admin] } },

  { path: 'master/clientlogo-checklist-customer/add',  component: AddClientlogoChecklistCustomerComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_audit_reviewer_review_checklist', roles: [Role.Admin] } },
  { path: 'master/clientlogo-checklist-customer/edit',  component: EditClientlogoChecklistCustomerComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'edit_audit_reviewer_review_checklist', roles: [Role.Admin] } },
  { path: 'master/clientlogo-checklist-customer/view',  component: ViewClientlogoChecklistCustomerComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'audit_reviewer_review_checklist_master', roles: [Role.Admin] } },
  { path: 'master/clientlogo-checklist-customer/list',  component: ListClientlogoChecklistCustomerComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'audit_reviewer_review_checklist_master', roles: [Role.Admin] } },
  { path: 'master/audit-standard',  component: AuditStandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'inspection_time_master', roles: [Role.Admin] } },  
  { path: 'master/list-audit-standard',  component: ListAuditStandardComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'inspection_time_master', roles: [Role.Admin] } },  

  { path: 'master/clientlogo-checklist-hq/add',  component: AddClientlogoChecklistHqComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'add_audit_reviewer_review_checklist', roles: [Role.Admin] } },
  { path: 'master/clientlogo-checklist-hq/edit',  component: EditClientlogoChecklistHqComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'edit_audit_reviewer_review_checklist', roles: [Role.Admin] } },
  { path: 'master/clientlogo-checklist-hq/view',  component: ViewClientlogoChecklistHqComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'audit_reviewer_review_checklist_master', roles: [Role.Admin] } },
  { path: 'master/clientlogo-checklist-hq/list',  component: ListClientlogoChecklistHqComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: { usertype:[1], rules:'audit_reviewer_review_checklist_master', roles: [Role.Admin] } },
  
  	
];

@NgModule({
  imports: [ RouterModule.forChild(masterRoutes)],
  exports: [RouterModule]
})
export class MasterRoutingModule { }
