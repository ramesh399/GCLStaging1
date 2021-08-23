import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {HqClientlogoChecklist} from '@app/models/master/hq-clientlogo-checklist';

@Injectable({
  providedIn: 'root'
})

export class HqClientlogoChecklistService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getHqClientlogoChecklistRiskCategory(): Observable<HqClientlogoChecklist[]>{
    return this.http.get<HqClientlogoChecklist[]>(`${environment.apiUrl}/master/hq-clientlogo-checklist/risk-category`,{});
  }   
      
  getHqClientlogoChecklist(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/hq-clientlogo-checklist/view`,{id});
  }


  getHqClientlogoChecklistList(): Observable<HqClientlogoChecklist[]>{
    return this.http.get<HqClientlogoChecklist[]>(`${environment.apiUrl}/master/hq-clientlogo-checklist/index`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/hq-clientlogo-checklist/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/hq-clientlogo-checklist/create`, data);
  }
}
