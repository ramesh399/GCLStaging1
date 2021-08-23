import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import {Application} from '@app/models/application/application';
import { ObserversModule } from '@angular/cdk/observers';

@Injectable({
  providedIn: 'root'
})
export class BrandService {

  constructor(private http: HttpClient) { }

  brandapprove(data):Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/brands/brand-approve`,data);
  }

  addData(data)
  {
    return this.http.post<any>(`${environment.apiUrl}/master/user/update-user`, data);
  } 

  addBrand(data)
  {
    return this.http.post<any>(`${environment.apiUrl}/master/brands/create`,data);
  }
  
  addBrandGroup(data)
  {
    return this.http.post<any>(`${environment.apiUrl}/master/brand-group/create`,data);
  }

  getBrandGroup(): Observable<any>
   {
    return this.http.get<any>(`${environment.apiUrl}/master/brands/brand-group`);
  }

  getData() : Observable<any>
  {
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-users`, { type : 500});
  } 

  getBrand(data) : Observable<any>
  {
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-users`,data);

  }
  getApplicationDetails(data): Observable<Application>
  {
    return this.http.post<Application>(`${environment.apiUrl}/application/brands/view`,data);
  }

  brandConsent(data): Observable<any>
  {
    return this.http.post<any>(`${environment.apiUrl}/master/brands/brand-consent`,data);
  }

  getBrandConsentDetails(data): Observable<any>
  {
    return this.http.post<any>(`${environment.apiUrl}/master/brands/get-consent`,data);
  }

  downloadFile(data){
    return this.http.post(`${environment.apiUrl}/master/brands/download-file`, data,
    {responseType:'arraybuffer'});
  } 
}
