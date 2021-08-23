import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuditNcnReportService {

  constructor(private http: HttpClient) { }
  
  getNcn(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-ncn-report/get-ncn`,data);
  }

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-ncn-report/create`, data);    
  }

  
}
