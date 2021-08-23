import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { AuditExecutionChecklist } from '@app/models/master/audit-execution-checklist';


@Injectable({
  providedIn: 'root'
})
export class AuditExecutionChecklistService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  
      
  getAuditExecutionChecklist(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/audit-execution-checklist/view`,{id});
  }
  
  getAuditExecutionChecklistList(): Observable<AuditExecutionChecklist[]>{
    return this.http.get<AuditExecutionChecklist[]>(`${environment.apiUrl}/master/audit-execution-checklist/index`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/audit-execution-checklist/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/audit-execution-checklist/create`, data);
  }
}
