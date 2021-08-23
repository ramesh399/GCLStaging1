import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { AppRoutingModule } from './app-routing.module';
import { EnquiryModule }     from './enquiry/enquiry.module';
import { MasterModule }     from './master/master.module';
import { ApplicationModule }     from './application/application.module';
import { OfferModule }     from './offer/offer.module';
import { InvoiceModule }     from './invoice/invoice.module';
import { AuditModule }     from './audit/audit.module';
import { CertificationModule }     from './certification/certification.module';
import { TransferCertificateModule }     from './transfer-certificate/transfer-certificate.module';
import { LibraryModule }     from './library/library.module';
import { ChangeScopeModule }     from './change-scope/change-scope.module';
import { UnannouncedauditModule }     from './unannouncedaudit/unannouncedaudit.module';
import { ReportModule }     from './report/report.module';
import { BrandModule }     from './brand/brand.module';

import { AppComponent } from './app.component';
import { AuditEnquiryComponent } from './audit-enquiry/audit-enquiry.component';
import { LoginComponent } from './login/login.component';

import { HeaderComponent } from './common/header/header.component';
import { LeftmenuComponent } from './common/leftmenu/leftmenu.component';
import { FooterComponent } from './common/footer/footer.component';

import { JwtInterceptor, ErrorInterceptor } from './helpers';

//import { AuthenticationService } from '@app/services';
//const currentUser = this.authenticationService.currentUserValue;
//import { JwtModule } from "@auth0/angular-jwt";
import { HttpClientModule,HTTP_INTERCEPTORS } from '@angular/common/http';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
//import { NgbdSortableHeader } from '@app/helpers/sortable.directive';
import { RightmenuComponent } from './common/rightmenu/rightmenu.component';
import { EnquiryHeaderComponent } from './common/enquiry-header/enquiry-header.component';
import { ForgotPasswordComponent } from './forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import { ChangeUsernamePasswordComponent } from './change-username-password/change-username-password.component';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { ChangePasswordComponent } from './change-password/change-password.component';
import { ErrorComponent } from './common/error/error.component';
import {CommonmodModule} from '@app/commonmod/commonmod.module';
//import { DashboardComponent } from './dashboard/dashboard.component';

import { HighchartsChartModule } from 'highcharts-angular';
import { UserDashboardComponent } from './dashboard/user-dashboard/user-dashboard.component';
import { CustomerDashboardComponent } from './dashboard/customer-dashboard/customer-dashboard.component';
 
//import { RecaptchaModule } from 'ng-recaptcha';
import { ViewProfileComponent } from './profile/view-profile/view-profile.component';
import { LogoutComponent } from './logout/logout.component';
import { NotificationComponent } from './notification/notification.component';
import { FranchiseDashboardComponent } from './dashboard/franchise-dashboard/franchise-dashboard.component';
import { RouterModule } from '@angular/router';

import { RECAPTCHA_V3_SITE_KEY, RecaptchaV3Module } from 'ng-recaptcha';
import { environment } from '../environments/environment';
import { ServiceWorkerModule } from '@angular/service-worker';
import { ItcenterModule } from './itcenter/itcenter.module';


/*
export function tokenGetter() {
  //console.log(localStorage.getItem("currentUser"));
  return 'eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIiwianRpIjoiNGYxZzIzYTEyYWEifQ.eyJpc3MiOiIiLCJhdWQiOiIiLCJqdGkiOiI0ZjFnMjNhMTJhYSIsImlhdCI6MTU3MjQ5NTM4OSwibmJmIjoxNTcyNDk1NDQ5LCJleHAiOjE1NzI0OTg5ODksInVpZCI6MX0.';//localStorage.getItem("currentUser.token");
}
*/

@NgModule({
  declarations: [
    AppComponent,
	  AuditEnquiryComponent,
    LoginComponent,
    HeaderComponent,
    FooterComponent,
    LeftmenuComponent,
    RightmenuComponent,
    EnquiryHeaderComponent,
    ForgotPasswordComponent,
    ResetPasswordComponent,
    ChangeUsernamePasswordComponent,
    ChangePasswordComponent,
    ErrorComponent,
    //DashboardComponent,
    //CustomerComponent,
    //UserComponent,
    UserDashboardComponent,
    CustomerDashboardComponent,
    ViewProfileComponent,
    LogoutComponent,
    NotificationComponent,
    FranchiseDashboardComponent
  ],
  imports: [
    RouterModule,
    BrowserModule,
    AppRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    HttpClientModule,
    EnquiryModule,
	  MasterModule,
    NgbModule,
    ApplicationModule,
    OfferModule,
    InvoiceModule,
    ItcenterModule,
    AuditModule,
    CertificationModule,
    TransferCertificateModule,
    LibraryModule,
    ChangeScopeModule,
	UnannouncedauditModule,
	ReportModule,
  BrandModule,
    BrowserAnimationsModule,
  	CommonmodModule,
    HighchartsChartModule,
    RecaptchaV3Module,
    ServiceWorkerModule.register('ngsw-worker.js', { enabled: environment.production }),
    
    //ServiceWorkerModule.register('service-worker.js', { enabled: environment.production }),    
  	//RecaptchaModule,
    /*JwtModule.forRoot({
      config: {
        tokenGetter: tokenGetter,
        whitelistedDomains: ['localhost','localhost:4200','yii72.aescorp.in'],
        //skipWhenExpired: true,
        //blacklistedRoutes: ['http://yii72.aescorp.in/yii19102301_gcl/ver1/web/login/authenticate']
      }
    })
    */

  ],
  providers: [
    { provide: HTTP_INTERCEPTORS, useClass: JwtInterceptor, multi: true },
    { provide: HTTP_INTERCEPTORS, useClass: ErrorInterceptor, multi: true },
    { provide: RECAPTCHA_V3_SITE_KEY, useValue: environment.reCaptchaSiteKey  }, //Local AES
    //{ provide: RECAPTCHA_V3_SITE_KEY, useValue: '6LfBCL4ZAAAAAHP4iNHhfde9ULSCDfwb8hnhr0Q-' }, //GCL LIVE Server
    
    // provider used to create fake backend
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
