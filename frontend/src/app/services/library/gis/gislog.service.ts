import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Gislog} from '@app/models/library/gislog';


@Injectable({
  providedIn: 'root'
})
export class GislogService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getGisStatusList(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/gis/gisstatuslist`,{});
  }

  getGislog(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/gis/index`,{id});
  }

  fetchGislog(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/gis/view`,{id});
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/gis/create`, data);
  } 

 
}
