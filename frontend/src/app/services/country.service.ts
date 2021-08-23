import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
//import { Country } from './master/country';
import { Country } from '@app/models/master/country';
import {State } from './state';

@Injectable({
  providedIn: 'root'
})
export class CountryService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }

  getCountry(): Observable<Country[]>{
    //return [{id:1, name:'USA'},{id:2, name:'India'}];
    return this.http.get<Country[]>(`${environment.apiUrl}/master/country/get-country`, this.httpOptions);
    //, JSON.stringify(data)
  }
  
  getCountryCode(): Observable<Country[]>{
    //return [{id:1, name:'USA'},{id:2, name:'India'}];
    return this.http.get<Country[]>(`${environment.apiUrl}/master/country/code`, this.httpOptions);
    //, JSON.stringify(data)
  }

  getStates(id): Observable<State[]>{
    //return [{id:1, name:'Tamil'},{id:2, name:'Karnataka'}];

    let params = new HttpParams();
    params = params.append('id', id);
    // httpOptions:this.httpOptions ,
    return this.http.get<State[]>(`${environment.apiUrl}/master/country/states`,{params:params});
  } 
  
}
