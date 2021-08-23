import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { first, catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';


import {DecimalPipe} from '@angular/common'; 
import {Request} from '@app/models/transfer-certificate/request';
 


@Injectable({
  providedIn: 'root'
})
export class StandardAdditionService {
  
  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
   
  getAppData(){
    return this.http.post<any>(`${environment.apiUrl}/changescope/standard-addition/get-appdata`, {});
  }

  getUnitData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/standard-addition/get-appunitdata`, {id});
  }

  getStandardData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/standard-addition/get-appstddata`, {id});
  }

  getData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/standard-addition/view`, {id});
  }

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/standard-addition/create`, data);
  }
 
  deleteData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/standard-addition/deletedata`, data);
  }
  
  getStandardAdditionList(data){
    return this.http.post<any[]>(`${environment.apiUrl}/changescope/standard-addition/get-standard-addition`,data);
  }
  
  getStandardAdditionListDetails(data){
    return this.http.post<any[]>(`${environment.apiUrl}/changescope/standard-addition/get-standard-addition-details`,data);
  }

  getRequestedStatus(data)
  {
   return this.http.post<any>(`${environment.apiUrl}/changescope/standard-addition/getrequestedstatus`, data);
  }

  private handleError(error: HttpErrorResponse) {
    if (error.error instanceof ErrorEvent) {
      // A client-side or network error occurred. Handle it accordingly.
      console.error('An error occurred:', error.error.message);
    } else {
      // The backend returned an unsuccessful response code.
      // The response body may contain clues as to what went wrong,
      console.error(
        `Backend returned code ${error.status}, ` +
        `body was: ${error.error}`);
    }
    // return an observable with a user-facing error message
    return throwError(
      'Something bad happened; please try again later.');
  };
  
  // ------ New Code Start Here ---------
  commonActionData(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/changescope/standard-addition/deletedata`,data);
  } 
  // ------ New Code End Here ---------
  
}
