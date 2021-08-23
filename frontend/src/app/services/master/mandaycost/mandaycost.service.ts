import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Mandaycost} from '@app/models/master/mandaycost';


@Injectable({
  providedIn: 'root'
})
export class MandaycostService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
      
  getMandaycost(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/mandaycost/view`,{id});
  }
  
  getMandaycostList(): Observable<Mandaycost[]>{
    return this.http.get<Mandaycost[]>(`${environment.apiUrl}/master/mandaycost/index`);
  }  
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/mandaycost/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/mandaycost/create`, data);
  } 
}
