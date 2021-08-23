import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { Standard } from './standard';
import { environment } from '@environments/environment';


@Injectable({
  providedIn: 'root'
})
export class StandardService {

  constructor(private http: HttpClient) { }


  
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }

  getStandard(): Observable<Standard[]>{
    //return [{id:1, name:'USA'},{id:2, name:'India'}];
    return this.http.get<Standard[]>(`${environment.apiUrl}/master/standard/get-standard`);
    //, JSON.stringify(data)
  }
 

}
