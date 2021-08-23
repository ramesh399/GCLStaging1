import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuditReviewerService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  
  getReviewQuestions(): Observable<any>{
    return this.http.get<any>(`${environment.apiUrl}/audit/audit-reviewer-review/index`,this.httpOptions);
  }

  addReviewchecklist(reviewdata): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-reviewer-review/create`,reviewdata);
  }
}
