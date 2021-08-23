import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {CustomerClientlogoChecklist} from '@app/models/master/customer-clientlogo-checklist';

@Injectable({
  providedIn: 'root'
})

export class CustomerClientlogoChecklistService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  
  getCustomerClientlogoChecklist(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/customer-clientlogo-checklist/view`,{id});
  }

  getCustomerClientlogoChecklistList(): Observable<CustomerClientlogoChecklist[]>{
    return this.http.get<CustomerClientlogoChecklist[]>(`${environment.apiUrl}/master/customer-clientlogo-checklist/index`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/customer-clientlogo-checklist/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/customer-clientlogo-checklist/create`, data);
  }
}
