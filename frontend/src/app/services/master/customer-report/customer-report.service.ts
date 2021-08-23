import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Application} from '@app/models/application/application';



@Injectable({
  providedIn: 'root'
})
export class CustomerReportService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getApplication(data){
    return this.http.post<any>(`${environment.apiUrl}/application/apps/get-applications`, data);    
  }


  getAudit(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/get-audits`, data);    
  }
	
}
