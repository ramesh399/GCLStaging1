import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { first, catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';


import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {Request} from '@app/models/transfer-certificate/request';
import {Application} from '@app/models/application/application';


@Injectable({
  providedIn: 'root'
})
export class ProcessAdditionService {
  
  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }

  getAdditionData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/process-addition/getprocessdata`,data);
  }

  getApplication(data): Observable<Application>{
    return this.http.post<Application>(`${environment.apiUrl}/changescope/process-addition/get-processdetails`,data);
  }

  getAppData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/process-addition/get-appdata`, data);
  }

  getUnitData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/process-addition/get-appunitdata`, {id});
  }

  getStandardData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/process-addition/get-appstddata`, {id});
  }

  getData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/process-addition/view`, {id});
  }

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/process-addition/create`, data);
  }

  addProcessAdditionData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/process-addition/createprocessaddition`, data);
  }
 
  deleteData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/process-addition/deletedata`, data);
  }
  
  updateAppProcessData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/process-addition/create`, data);
  }
  
  getRequestedStatus(data)
  {
  return this.http.post<any>(`${environment.apiUrl}/changescope/process-addition/getrequestedstatus`, data);
  }

}
