import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { AuditPlanningChecklist } from '@app/models/master/audit-planning-checklist';


@Injectable({
  providedIn: 'root'
})
export class AuditPlanningChecklistService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getAuditPlanningChecklistRiskCategory(): Observable<AuditPlanningChecklist[]>{
    return this.http.get<AuditPlanningChecklist[]>(`${environment.apiUrl}/master/audit-planning-checklist/risk-category`,{});
  }   
      
  getAuditPlanningChecklist(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/audit-planning-checklist/view`,{id});
  }
  
  getAuditPlanningChecklistList(): Observable<AuditPlanningChecklist[]>{
    return this.http.get<AuditPlanningChecklist[]>(`${environment.apiUrl}/master/audit-planning-checklist/index`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/audit-planning-checklist/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/audit-planning-checklist/create`, data);
  }
}
