import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { AuditReportQbsScopeHolder } from '@app/models/audit/audit-qbs-scopeholder';

@Injectable({
  providedIn: 'root'
})

export class AuditQbsScopeholderService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  
  getQBSdescription(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-qbs-scopeholder/view`,data);
  }
  
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-qbs-scopeholder/create`, data);
  }
}
