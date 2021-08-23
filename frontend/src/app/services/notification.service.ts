import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';


@Injectable({
  providedIn: 'root'
})

export class NotificationService {

  constructor(private http: HttpClient) { }
  
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getUserData(): Observable<any[]>{    
    return this.http.get<any[]>(`${environment.apiUrl}/master/notification/user-notification`);    
  }
  
}
