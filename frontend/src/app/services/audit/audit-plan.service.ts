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

  getAuditReviewer(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-audit-reviewer`,{});
  }
  
  getAuditPlan(data): Observable<AuditPlan>{
    //return this.http.post<AuditPlan>(`${environment.apiUrl}/audit/audit-plan/view-audit-plan`,data);
	  return this.http.post<AuditPlan>(`${environment.apiUrl}/audit/audit-plan/view-audit-plan`,data);
  }

  getAuditPlanDetails(plan_id): Observable<AuditPlan>{
    return this.http.post<AuditPlan>(`${environment.apiUrl}/audit/audit-plan/view`,{id:plan_id});
  }
  
  createAuditPlan(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/create-audit-plan`,data);
  } 
  saveSubtopics(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/save-subtopic`,data);
  } 
  getAuditors(datedata): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/getauditors`,datedata);
  } 

  savetempauditors(datedata): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/save-temp-auditors`,datedata);
  } 

  removetempauditors(datedata): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/remove-auditors`,datedata);
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

  generateCertificate(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/change-generate-certificate`,data);
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
  changeNCoverdueStatus(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/change-overdue-status`,data);
  }
  getUnitSubtopic(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/get-subtopic`,data);
  }
  getUnitAssignSubtopic(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/get-assignsubtopic`,data);
  }
  getFollowupUnitSubtopic(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/get-followupsubtopic`,data);
  }

  getAssignReviewerDetails(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/get-reviewer-groups`,data);
  }

  getAddAssignReviewerDetails(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/addassignreviewer`,data);
  }

  assignReviewer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/assign-reviewer`,data);
  }

  changeReviewer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan-review/change-reviewer`,data);
  }

  sendToCustomer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/sendtocustomer`,data);
  }

  sendAudit(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/sendaudit`,data);
  }

  submitForAuditFollowup(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/submitforauditfollowup`,data);
  }
  
  sendToLeadAuditor(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/sendtoleadauditor`,data);
  }

  followupsendToLeadAuditor(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/followupsendtoleadauditor`,data);
  }
  

  sendToReviewer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/sendtoreviewer`,data);
  }
  
  checkAuditReport(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/validate-audit-report`,data);
  }

  getAuditReportDisplayStatus(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/getreportlist`,data);
  }

  getApplicationUnit(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/getapplicationunit`,data);
  }

  getInspectors(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-inspection-plan/get-inspector`,data);
  }

}
