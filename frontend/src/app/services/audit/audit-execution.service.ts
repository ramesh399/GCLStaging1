import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuditExecutionService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }

  public docsContentType = {
    'pdf': 'application/pdf', 'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    , 'doc': 'application/msword'
    , 'txt': 'text/plain'
    , 'png': 'image/png'
    , 'jpeg': 'image/jpeg'
    , 'jpg': 'image/jpeg'
  };


  getReviewQuestions(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution/get-questions`, data);
  }

  getReviewQuestionsByGet(data): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/audit/audit-execution/get-questions?${data}`);
  }

  getRemediation(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution/get-remediation`, data);
  }
  
  getReviewerHistroy(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution-review/reviewer-histroy`, { id: data });
  }

  getQuestionStandards(): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/master/audit-execution-checklist/index`, { access: "without_hasrights" });
  }

  getReviewerQuestions(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution-review/get-questions`, data);
  }

  saveAuditAnswers(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution/audit-execution`, data);
  }

  downloadInspectionPlan(data) {
    return this.http.post(`${environment.apiUrl}/audit/audit-inspection-plan/generate`, data,
      { responseType: 'arraybuffer' }
    );
  }

  downloadEvidenceFile(data) {
    return this.http.post(`${environment.apiUrl}/audit/audit-execution/evidencefile`, data,
      { responseType: 'arraybuffer' }
    );
  }

  saveReviewAuditAnswers(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution-review/audit-execution-review`, data);
  }

  saveReviewerCustomerReview(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution-review/reviewer-customer-review`, data);
  }

  ReviewerCloseFindings(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution-review/reviewer-close-finding`, data);
  }

  saveAuditorFindingReview(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution-review/auditor-finding-review`, data);
  }
  /*
  saveReviewerFindingApproval(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution-review/reviewer-finding-approval`,data);
  }
  */

  getApplicationDetails(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution-review/getapplicationdetails`, data);
  }

  downloadNCreport(data) {
    return this.http.post(`${environment.apiUrl}/audit/audit-execution/generate-pdf`, data,
      { responseType: 'arraybuffer' }
    );
  }

  downloadAuditreport(data) {
    return this.http.post(`${environment.apiUrl}/audit/audit-execution/generate-auditreport`, data,
      { responseType: 'arraybuffer' }
    );
  }

  downloadunitNCreport(data) {
    return this.http.post(`${environment.apiUrl}/audit/audit-execution/generate-pdf-unit`, data,
      { responseType: 'arraybuffer' }
    );
  }

  geAuditReportDisplayStatus(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution/getreportlist`, data);
  }

  saveFollowupFindingReview(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution-review/savefollowupfindingreview`, data);
  }

}
