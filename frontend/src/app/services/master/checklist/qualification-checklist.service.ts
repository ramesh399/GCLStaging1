import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { QualificationChecklist } from '@app/models/master/qualification-checklist';


@Injectable({
  providedIn: 'root'
})
export class QualificationChecklistService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getQualificationChecklistRecurringPeriod(): Observable<QualificationChecklist[]>{
    return this.http.get<QualificationChecklist[]>(`${environment.apiUrl}/master/qualification-checklist/recurring-period`,{});
  }   
      
  getQualificationChecklist(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/qualification-checklist/view`,{id});
  }
  
  getQualificationChecklistList(): Observable<QualificationChecklist[]>{
    return this.http.get<QualificationChecklist[]>(`${environment.apiUrl}/master/qualification-checklist/index`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/qualification-checklist/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/qualification-checklist/create`, data);
  }
}
