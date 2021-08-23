import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Country} from '@app/models/master/country';
import { map } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class CountryService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'X-Requested-With' : 'XMLHttpRequest'
    })
  }
  
  getCountry(id): Observable<any>{
    let params = new HttpParams();
    params = params.append('id', id);
    return this.http.get<any>(`${environment.apiUrl}/master/country/view`,{params})
              .pipe(map(user => {
                if (user.data) {
                  return user;
                }else{
                  throw new Error(user.message);
                }
            }));
            
  }  
  
  addData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/country/create`, formData);
  }

  updateData(formData): Observable<any>{
    //X-Requested-With
    return this.http.post<any>(`${environment.apiUrl}/master/country/update`, formData,this.httpOptions);
  }
  
}
