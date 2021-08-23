import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class CertificateReviewerService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  
  getReviewQuestions(data): Observable<any>{
    //return this.http.get<any>(`${environment.apiUrl}/certificate/certificate-reviewer-review/index`,this.httpOptions);
	return this.http.post<any>(`${environment.apiUrl}/certificate/certificate-reviewer-review/index`,data);
  }

  addReviewchecklist(reviewdata): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/certificate/certificate-reviewer-review/create`,reviewdata);
  }
}
