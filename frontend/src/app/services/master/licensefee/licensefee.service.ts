import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Licensefee} from '@app/models/master/licensefee';


@Injectable({
  providedIn: 'root'
})
export class LicensefeeService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
      
  getLicensefee(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/standard-license-fee/view`,{id});
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/standard-license-fee/create`, data);
  } 
}
