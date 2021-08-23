import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { AuditReviewerChecklist } from '@app/models/master/audit-reviewer-checklist';

@Injectable({
  providedIn: 'root'
})

export class AuditReviewerChecklistService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getAuditReviewerChecklistRiskCategory(): Observable<AuditReviewerChecklist[]>{
    return this.http.get<AuditReviewerChecklist[]>(`${environment.apiUrl}/master/audit-reviewer-checklist/risk-category`,{});
  }   
      
  getAuditReviewerChecklist(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/audit-reviewer-checklist/view`,{id});
  }
  
  getAuditReviewerChecklistList(): Observable<AuditReviewerChecklist[]>{
    return this.http.get<AuditReviewerChecklist[]>(`${environment.apiUrl}/master/audit-reviewer-checklist/index`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/audit-reviewer-checklist/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/audit-reviewer-checklist/create`, data);
  }
}
