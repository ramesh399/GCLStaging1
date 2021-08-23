import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Process} from '@app/models/master/process';


@Injectable({
  providedIn: 'root'
})
export class ProcessService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getProcess(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/process/view`,{id});
  }
  
  getProcessList(): Observable<Process[]>{
    return this.http.get<Process[]>(`${environment.apiUrl}/master/process/get-process`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/process/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/process/create`, data);
  }
}
