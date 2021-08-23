import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Checklist} from '@app/models/master/checklist';


@Injectable({
  providedIn: 'root'
})
export class ChecklistService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
    
  getChecklist(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/checklist/view`,{id});
  }
  
  getChecklistList(): Observable<Checklist[]>{
    return this.http.get<Checklist[]>(`${environment.apiUrl}/master/checklist/index`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/checklist/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/checklist/create`, data);
  }
}
