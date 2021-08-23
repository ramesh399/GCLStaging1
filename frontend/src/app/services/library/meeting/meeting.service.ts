import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Meeting} from '@app/models/library/meeting';


@Injectable({
  providedIn: 'root'
})
export class MeetingService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
      
  getMeetingStatusList(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/meeting/meetingstatuslist`,{});
  }

  fetchMeeting(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/meeting/view`,{id});
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/meeting/create`, data);
  } 
}
