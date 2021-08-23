import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {User} from '@app/models/master/user';


@Injectable({
  providedIn: 'root'
})
export class ForgotPasswordService {
	
  constructor(private http: HttpClient) { }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }  
  
  forgotPasswordData(data){
    return this.http.post<any>(`${environment.apiUrl}/site/password-reset`, data);
  } 

}