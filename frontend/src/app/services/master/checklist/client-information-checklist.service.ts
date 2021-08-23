import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {ClientInformationChecklist} from '@app/models/master/client-information-checklist';

@Injectable({
  providedIn: 'root'
})

export class ClientInformationChecklistService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getClientInformationChecklistRiskCategory(): Observable<ClientInformationChecklist[]>{
    return this.http.get<ClientInformationChecklist[]>(`${environment.apiUrl}/master/information-question-checklist/risk-category`,{});
  }   
      
  getClientInformationChecklist(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/information-question-checklist/view`,{id});
  }

  getClientInformations(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/audit-category/get-client-information`,{});
  }
  
  getClientInformationChecklistList(): Observable<ClientInformationChecklist[]>{
    return this.http.get<ClientInformationChecklist[]>(`${environment.apiUrl}/master/information-question-checklist/index`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/information-question-checklist/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/information-question-checklist/create`, data);
  }
}
