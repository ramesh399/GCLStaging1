import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Application} from '@app/models/brand/brand';



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
    return this.http.post<Application>(`${environment.apiUrl}/application/brands/view`,{id});
  }

  getApplicationDetails(data): Observable<Application>{
    return this.http.post<Application>(`${environment.apiUrl}/application/brands/view`,data);
  }
  
  getApplicationDetailsByGet(data): Observable<Application>{
    return this.http.get<Application>(`${environment.apiUrl}/application/brands/view?${data}`);
  }

  getProductDetails(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/brands/get-product-details`,{id});
  }
  
  getProductDetailsBasedOnStandard(data): Observable<Application>{
    return this.http.post<any>(`${environment.apiUrl}/application/brands/get-product-details`,data);
  }
  
  getApplicationStatusList(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/brands/list-application-status`,{});
  }
  
  getApplicationType(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/brands/list-application-type`,{});
  }
  
  getApplicationUnit(auditid): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/brands/get-unit`,{auditid});
  }
  
  getApplicationStandard(id): Observable<Application>{
    return this.http.post<Application>(`${environment.apiUrl}/application/brands/get-application-standard`,{id});
  }
  
  updateApplicationProductReviewer(data): Observable<any>{
    return this.http.post<Application>(`${environment.apiUrl}/application/brands/updateproducttemp`,data);
  }
  
}
