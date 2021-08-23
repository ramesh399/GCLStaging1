import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { first, catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';


import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {Changeaddress} from '@app/models/changescope/changeaddress';
import {Application} from '@app/models/application/application';


@Injectable({
  providedIn: 'root'
})
export class ChangeAddressService {
  
  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }

  getUnitData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/change-address/get-appunitdata`, id);
  }

  getUnitList(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/change-address/get-appunitlist`, data);
  }
  
  getAddress(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/change-address/view`, id);
  }

  addAddressData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/change-address/create`, data);
  }
  
 
}
