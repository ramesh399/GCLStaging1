import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { SubTopic } from '@app/models/master/sub-topic';

@Injectable({
  providedIn: 'root'
})
export class SubTopicService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getSubTopic(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/sub-topic/view`,{id});
  }

  getSubTopics(data): Observable<SubTopic[]>{

    return this.http.post<SubTopic[]>(`${environment.apiUrl}/master/sub-topic/sub-topics`,data);
  } 

  getSubTopicList(): Observable<SubTopic[]>{
    return this.http.get<SubTopic[]>(`${environment.apiUrl}/master/sub-topic/get-sub-topic`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/sub-topic/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/sub-topic/create`, data);
  }
}
