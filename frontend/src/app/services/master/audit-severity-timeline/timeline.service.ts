import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {AuditSeverityTimeline} from '@app/models/master/audit-severity-timeline';


@Injectable({
  providedIn: 'root'
})
export class SeverityTimeline {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };

  getTimelines(): Observable<any>{
    return this.http.get<any>(`${environment.apiUrl}/master/audit-severity-timeline/index`,this.httpOptions);
  }
  
  getSeverityTimeline(): Observable<any>{
    return this.http.get<any>(`${environment.apiUrl}/master/audit-severity-timeline/get-timeline`,this.httpOptions);
  }
  
  updateSeverityTimeline(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/audit-severity-timeline/update`, formData,this.httpOptions);
  }
  
  
  
}
