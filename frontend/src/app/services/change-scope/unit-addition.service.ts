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
export class UnitAdditionService {
  
  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getRequestedUnitStatus(data)
  {
	return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/getrequestedunitstatus`, data);
  }

  getAdditionData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/getprocessdata`,data);
  }

  getApplication(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/get-unitdetails`,data);
  }

  getAppData(){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/get-appdata`, {});
  }

  getUnitData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/get-appunitdata`, {id});
  }

  getStandardData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/get-appstddata`, {id});
  }

  getData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/view`, {id});
  }

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/create`, data);
  }
 
  deleteData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/deleteunit`, data);
  }

  submitForAddition(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/submitadditionunit`, data);
  }
  
  updateAppUnitData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/create`, data);
  }
  
  downloadFile(data){
    return this.http.post(`${environment.apiUrl}/changescope/unit-addition/certificationfile`,data,
      {responseType:'arraybuffer'}
    );
  }
}
