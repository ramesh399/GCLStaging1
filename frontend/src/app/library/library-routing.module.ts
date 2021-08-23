import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

// import { AddFaqComponent } from './faq/add-faq/add-faq.component';
// import { EditFaqComponent } from './faq/edit-faq/edit-faq.component';
// import { ViewFaqComponent } from './faq/view-faq/view-faq.component';
// import { ListFaqComponent } from './faq/list-faq/list-faq.component';

// import { AddLegistlationComponent } from './legislation/add-legistlation/add-legistlation.component';
// import { EditLegistlationComponent } from './legislation/edit-legistlation/edit-legistlation.component';
// import { ViewLegistlationComponent } from './legislation/view-legistlation/view-legistlation.component';
// import { ListLegistlationComponent } from './legislation/list-legistlation/list-legistlation.component';

import { GislogsComponent } from './gislogs/gislogs.component';
import { MeetingComponent } from './meeting/meeting.component';
import { FaqComponent } from './faq/faq.component';
import { LegislationComponent } from './legislation/legislation.component';
import { DocumentComponent } from './document/document.component';

import { ManualComponent } from './manual/manual.component';
import { ApprovedSuppliersComponent } from './approved-suppliers/approved-suppliers.component';
import { AuditReportComponent } from './audit-report/audit-report.component';
import { RiskassessmentComponent } from './riskassessment/riskassessment.component';
import { MailComponent } from './mail/mail.component';
import { ScopeGroupsComponent } from './scope-groups/scope-groups.component';

import { ListTranslatorComponent } from './translator/list-translator/list-translator.component';


import { AuthGuard } from '@app/helpers';
import { Role } from '@app/models';
import { Rule } from '@app/models/rule';

const libraryRoutes: Routes = [

  // { path: 'library/faq/add',  component: AddFaqComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'add_user_role',roles: [Role.Admin] } },
  // { path: 'library/faq/edit',  component: EditFaqComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'edit_user_role', roles: [Role.Admin] } },
  // { path: 'library/faq/view',  component: ViewFaqComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1]} },
  // { path: 'library/faq/list',  component: ListFaqComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1]} },

  // { path: 'library/legislation/add',  component: AddLegistlationComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'add_user_role',roles: [Role.Admin] } },
  // { path: 'library/legislation/edit',  component: EditLegistlationComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1], rules:'edit_user_role', roles: [Role.Admin] } },
  // { path: 'library/legislation/view',  component: ViewLegistlationComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1]} },
  // { path: 'library/legislation/list',  component: ListLegistlationComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1]} },

  { path: 'library/gis/list',  component: GislogsComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:'gis'} },

  { path: 'library/meetings/list',  component: MeetingComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:'ic_mrm_minutes'} },

  { path: 'library/faq/list',  component: FaqComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3]} },

  { path: 'library/legislation/list',  component: LegislationComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,3]} },

  { path: 'library/document/list',  component: DocumentComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1],rules:'osp_documents'} },

  { path: 'library/approved-suppliers/list',  component: ApprovedSuppliersComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,3]} },
  { path: 'library/list-translator',  component: ListTranslatorComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,3]} },

  { path: 'library/audit-report/list',  component: AuditReportComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3]} },

  { path: 'library/mail/list',  component: MailComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3]} },

  { path: 'library/scopes-groups/list',  component: ScopeGroupsComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3]} },
      
  { path: 'library/handbooks/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'handbooks' } },
  { path: 'library/training-mat/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'training_mat' } },
  { path: 'library/artwork/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'artwork' } },
  { path: 'library/client-logos/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'client_logos' } },
  { path: 'library/manual/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'manual' } },
  { path: 'library/procedures/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'procedures' } },
  { path: 'library/competence-criteria/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'competence_criteria' } },
  { path: 'library/instructions/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'instructions' } },
  { path: 'library/templates/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'templates' } },
  { path: 'library/application-forms/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'application_forms' } },
  { path: 'library/polices/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'polices' } },
  { path: 'library/standards/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'standards' } },
  { path: 'library/webinars/index',  component: ManualComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,2,3],pageType:'webinars' } },

  
  { path: 'library/risk-assessment/list',  component: RiskassessmentComponent,canLoad: [AuthGuard],canActivate: [AuthGuard],data: {usertype:[1,3]} },
    	
];

@NgModule({
  imports: [ RouterModule.forChild(libraryRoutes)],
  exports: [RouterModule]
})
export class LibraryRoutingModule { }
