import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { first, catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';


import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';


@Injectable({
  providedIn: 'root'
})
export class WithdrawUnitService {
  
  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  addWithdrawUnitData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/create`, data);
  }

  getAdditionData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/getprocessdata`,data);
  }

  getApplication(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/get-unitdetails`,data);
  }

  getAppStandard(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/get-productstd`, data);
  }

  getUnit(id)
  {
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/get-unit`, {id});
  }

  getAppData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/get-appdata`, data);
  }


  getAppCompanyData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/get-appcompanydata`, data);
  }

  getProductData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/get-productdata`, {id});
  }
  
  getStatusList(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/get-status`, data);
  }

  addReviewerchecklist(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/add-reviewer-review`, data);
  }

  addOspchecklist(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/add-osp-review`, data);
  }

  assignReviewer(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/assign-reviewer`, data);
  }
  
   assignCertificationReviewer(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/assign-certification-reviewer`, data);
  }

  getData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/view`, {id});
  }

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/create`, data);
  }
 
  deleteData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/deleteproduct`, data);
  }

  submitForAddition(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/submitadditionunit`, data);
  }
  
   
  updateAppProductData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/updateproduct`, data);
  }
  
  getUnitData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/withdraw/get-appunitdata`, {id});
  } 
  
}
