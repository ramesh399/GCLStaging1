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
export class ClientLogoRequestService {

  public docsContentType = {'pdf':'application/pdf','docx':'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
  ,'doc':'application/msword'
  ,'txt':'text/plain'
  ,'png' : 'image/png'
  ,'jpeg' : 'image/jpeg'
  ,'jpg' : 'image/jpeg'
  };
  
  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  downloadFile(data){
    return this.http.post(`${environment.apiUrl}/application/clientlogo-request/checklistfile`,data,
      {responseType:'arraybuffer'}
    );
  }

  getchecklistDetails(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/clientlogo-request/get-checklist`,data);
  }

  getAppData(){
    return this.http.post<any>(`${environment.apiUrl}/application/clientlogo-request/get-appdata`, {});
  }

  getchecklist(){
    return this.http.post<any>(`${environment.apiUrl}/master/customer-clientlogo-checklist/get-questions`, {});
  }

  getStandardData(id){
    return this.http.post<any>(`${environment.apiUrl}/application/clientlogo-request/get-appstddata`, {id});
  }

  saveAuditAnswers(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/clientlogo-request/save-checklist`,data);
  }

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/application/clientlogo-request/create`, data);
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
  
 
}
