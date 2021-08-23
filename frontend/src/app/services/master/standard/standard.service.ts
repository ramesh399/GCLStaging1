import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Standard} from '@app/models/master/standard';


@Injectable({
  providedIn: 'root'
})
export class StandardService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
      
  getStandard(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/standard/view`,{id});
  }
  
  getStandardList(): Observable<Standard[]>{
    return this.http.get<Standard[]>(`${environment.apiUrl}/master/standard/get-standard`);
  }  
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/standard/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/standard/create`, data);
  } 
}
