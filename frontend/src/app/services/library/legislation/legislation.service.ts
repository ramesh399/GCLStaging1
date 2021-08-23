import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { Legislation } from '@app/models/library/legislation';


@Injectable({
  providedIn: 'root'
})
export class LegislationService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
 
      
  getLegislation(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/legislation/view`,{id});
  }
  
  getLegislationList(): Observable<Legislation[]>{
    return this.http.get<Legislation[]>(`${environment.apiUrl}/library/legislation/index`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/legislation/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/legislation/create`, data);
  }
}
