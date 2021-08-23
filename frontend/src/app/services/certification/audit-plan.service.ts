import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { AuditPlan } from '@app/models/audit/audit-plan';

@Injectable({
  providedIn: 'root'
})
export class AuditPlanService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getAuditPlan(data): Observable<AuditPlan>{
    //return this.http.post<AuditPlan>(`${environment.apiUrl}/audit/audit-plan/view-audit-plan`,data);
	  return this.http.post<AuditPlan>(`${environment.apiUrl}/audit/audit-plan/view-audit-plan`,data);
  }

  getAuditPlanDetails(data): Observable<AuditPlan>{
    return this.http.post<AuditPlan>(`${environment.apiUrl}/certificate/generate-certificate/view`,data);
  }

  getStatusList(data){
    return this.http.post<any>(`${environment.apiUrl}/certificate/generate-certificate/review-status`, data);
  }

  createAuditPlan(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/create-audit-plan`,data);
  } 

  approveCertificate(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/certificate/generate-certificate/save-review`,data);
  } 

  getAuditors(datedata): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/getauditors`,datedata);
  } 
  
  getReviewQuestions(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan-review/index`,data);
  }

  getStdCertificateDetails(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/certificate/generate-certificate/index`,data);
  }

  downloadCertificateFile(data){
    return this.http.post(`${environment.apiUrl}/certificate/generate-certificate/index`,data,
      {responseType:'arraybuffer'}
    );
  }

  downloadcbFile(data){
    return this.http.post(`${environment.apiUrl}/certificate/generate-certificate/download-cbfile`,data,
      {responseType:'arraybuffer'}
    );
  }

  generateCertificate(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/certificate/generate-certificate/generate-certificate`,data);
  }

  changetoAuditReview(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/change-audit-review`,data);
  }

  

  addReviewchecklist(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan-review/create`,data);
  }
  changeStatus(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/change-status`,data);
  }
  getUnitSubtopic(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/get-subtopic`,data);
  }

  assignReviewer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/assign-reviewer`,data);
  }

  sendToCustomer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/sendtocustomer`,data);
  }
  
  sendToLeadAuditor(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/sendtoleadauditor`,data);
  }
  sendToReviewer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/sendtoreviewer`,data);
  }
  
  assignCertificationReviewer(data){
    return this.http.post<any>(`${environment.apiUrl}/certificate/generate-certificate/assign-certification-reviewer`, data);
  }
  
}
