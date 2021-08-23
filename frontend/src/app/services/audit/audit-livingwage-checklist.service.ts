import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})

export class AuditLivingWageChecklistService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  
  getChecklist(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/audit-category/get-livingwage-checklist`,{});
  }

  getChecklistQuestions(audit_id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-livingwage/get-checklist`,audit_id);
  }

  addRemark(remarkData)
  {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/add-remark`, remarkData);
  }

  getRemarkData(data):Observable<any>{    
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/get-applicable-data`,data);    
  }
  
  
  addChecklist(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-livingwage/create`, data);
  }

  addCategory(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-livingwage/save-category`, data);
  }


  getchecklistAnswer(audit_id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-livingwage/get-answer`,audit_id);
  }
}
