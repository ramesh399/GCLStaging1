import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { AuditPlan } from '@app/models/audit/audit-plan';

@Injectable({
  providedIn: 'root'
})
export class FollowupAuditPlanService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getAuditPlan(data): Observable<AuditPlan>{
    return this.http.post<AuditPlan>(`${environment.apiUrl}/audit/followup-audit-plan/view-audit-plan`,data);
  }

  getAuditPlanDetails(plan_id): Observable<AuditPlan>{
    return this.http.post<AuditPlan>(`${environment.apiUrl}/audit/followup-audit-plan/view`,{id:plan_id});
  }
  
  createAuditPlan(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan/create-audit-plan`,data);
  } 
  getAuditors(datedata): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan/getauditors`,datedata);
  } 
  
  getReviewQuestions(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan-review/index`,data);
  }

  getStdCertificateDetails(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/certificate/generate-certificate/index`,data);
  }

  downloadCertificateFile(data){
    return this.http.post(`${environment.apiUrl}/certificate/generate-certificate/index`,data,
      {responseType:'arraybuffer'}
    );
  }

  generateCertificate(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan/change-generate-certificate`,data);
  }

  changetoAuditReview(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan/change-audit-review`,data);
  }

  

  addReviewchecklist(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan-review/create`,data);
  }
  changeStatus(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan/change-status`,data);
  }
  getUnitSubtopic(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan/get-subtopic`,data);
  }

  assignReviewer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan/assign-reviewer`,data);
  }

  sendToCustomer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan/sendtocustomer`,data);
  }

  sendAudit(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan/sendaudit`,data);
  }

  
  sendToLeadAuditor(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan/sendtoleadauditor`,data);
  }
  sendToReviewer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan/sendtoreviewer`,data);
  }
  


  getAuditPlanLoadDetails(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/followup-audit-plan/getauditloaddetails`,data);
  }
  
  createNormalAuditPlan(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/create-audit-plan`,data);
  } 
}
