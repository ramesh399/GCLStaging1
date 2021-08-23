import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { ApplicationChecklist } from '@app/models/master/application-checklist';


@Injectable({
  providedIn: 'root'
})
export class ApplicationChecklistService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  // getApplicationChecklist(): Observable<ApplicationChecklist[]>{
  //   return this.http.get<ApplicationChecklist[]>(`${environment.apiUrl}/master/audit-planning-checklist/risk-category`,{});
  // }   
      
  getApplicationChecklist(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/application-checklist/view`,{id});
  }
  
  getApplicationChecklistList(): Observable<ApplicationChecklist[]>{
    return this.http.get<ApplicationChecklist[]>(`${environment.apiUrl}/master/application-checklist/index`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/application-checklist/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/application-checklist/create`, data);
  }
}
