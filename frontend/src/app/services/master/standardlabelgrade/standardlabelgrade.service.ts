import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Standardlabelgrade} from '@app/models/master/standardlabelgrade';


@Injectable({
  providedIn: 'root'
})
export class StandardlabelgradeService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
      
  getStandardlabelgrade(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/standard-label-grade/view`,{id});
  }
  
  getStandardlabelgradeList(): Observable<Standardlabelgrade[]>{
    return this.http.get<Standardlabelgrade[]>(`${environment.apiUrl}/master/standard-label-grade/index`);
  }  
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/standard-label-grade/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/standard-label-grade/create`, data);
  } 
}
