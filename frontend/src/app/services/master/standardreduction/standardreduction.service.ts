import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Standardreduction} from '@app/models/master/standardreduction';
import { Standard } from '../../standard';

@Injectable({
  providedIn: 'root'
})
export class StandardreductionService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
      
  getStandardreduction(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/standard-reduction/view`,{id});
  }
  
  getStandardreductionList(): Observable<Standardreduction[]>{
    return this.http.get<Standardreduction[]>(`${environment.apiUrl}/master/standard-reduction/index`);
  }  
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/standard-reduction/update`, formData,this.httpOptions);
  }

  getreductionStandardList(): Observable<Standard[]>{
    return this.http.get<any[]>(`${environment.apiUrl}/master/reduction-standard/get-standard`);
  }  
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/standard-reduction/create`, data);
  } 
}
