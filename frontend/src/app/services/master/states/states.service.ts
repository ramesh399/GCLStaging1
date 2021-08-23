import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {States} from '@app/models/master/states';


@Injectable({
  providedIn: 'root'
})
export class StatesService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getStates(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/state/view`,{id});
  }
  
  getStatesList(): Observable<States[]>{
    return this.http.get<States[]>(`${environment.apiUrl}/master/state/index`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/state/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/state/create`, data);
  } 
}
