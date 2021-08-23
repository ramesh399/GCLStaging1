import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';

import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import { AuditSampling } from '@app/models/audit/audit-sampling';


@Injectable({
  providedIn: 'root'
})
export class AuditSamplingService {
  constructor(private http: HttpClient) { }

  getOptionList(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-sampling/optionlist`,{});
  }

  addData(samplingData){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-sampling/create`, samplingData);    
  }

  deleteData(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-sampling/delete-data`, data);
  }

  getsamplingdata(data):Observable<any>{    
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-sampling/get-samplingdata`,data);    
  }

  addSampleData(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-sampling/add-sample`, data);
  }

  getsampleData(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-sampling/get-sampledata`, data);
  }

  deleteSampleData(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-sampling/delete-sample`, data);
  }

  addRemark(remarkData)
  {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/add-remark`, remarkData);
  }

  getRemarkData(data):Observable<any>{    
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/get-applicable-data`,data);    
  }

  commonActionData(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-sampling/common-update`,data);
  } 
}
