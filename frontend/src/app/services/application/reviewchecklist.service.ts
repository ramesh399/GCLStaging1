import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Reviewchecklist} from '@app/models/application/reviewchecklist';

@Injectable({
  providedIn: 'root'
})
export class ReviewchecklistService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }

  getReviewchecklist(): Observable<Reviewchecklist[]>{
    //return [{id:1, name:'USA'},{id:2, name:'India'}];
	//let params = new HttpParams();
    //params = params.append('id', 1);
	
    return this.http.get<Reviewchecklist[]>(`${environment.apiUrl}/master/checklist/get-checklist`,this.httpOptions);
    //, JSON.stringify(data)
  }
  getReviewerchecklistEntries(app_id): Observable<Reviewchecklist[]>{
    return this.http.post<Reviewchecklist[]>(`${environment.apiUrl}/application/review/view-answer`,{app_id});
  }

  addReviewchecklist(reviewdata): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/review/create`,reviewdata);
  }
  getApplicationDetails(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/apps/question-view`,{id});
  }
  

}
