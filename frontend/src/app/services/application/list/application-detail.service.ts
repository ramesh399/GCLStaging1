import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Application} from '@app/models/application/application';



@Injectable({
  providedIn: 'root'
})
export class ApplicationDetailService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getApplication(id): Observable<Application>{
    return this.http.post<Application>(`${environment.apiUrl}/application/apps/view`,{id});
  }

  getApplicationDetails(data): Observable<Application>{
    return this.http.post<Application>(`${environment.apiUrl}/application/apps/view`,data);
  }
  
  getApplicationDetailsByGet(data): Observable<Application>{
    return this.http.get<Application>(`${environment.apiUrl}/application/apps/view?${data}`);
  }

  getProductDetails(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/apps/get-product-details`,{id});
  }
  
  getProductDetailsBasedOnStandard(data): Observable<Application>{
    return this.http.post<any>(`${environment.apiUrl}/application/apps/get-product-details`,data);
  }
  
  getApplicationStatusList(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/apps/list-application-status`,{});
  }
  
  getApplicationType(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/apps/list-application-type`,{});
  }
  
  getApplicationUnit(auditid): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/apps/get-unit`,{auditid});
  }
  
  getApplicationStandard(id): Observable<Application>{
    return this.http.post<Application>(`${environment.apiUrl}/application/apps/get-application-standard`,{id});
  }
  
  updateApplicationProductReviewer(data): Observable<any>{
    return this.http.post<Application>(`${environment.apiUrl}/application/apps/updateproducttemp`,data);
  }
  
}
