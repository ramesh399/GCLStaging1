import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { Faq } from '@app/models/library/faq';


@Injectable({
  providedIn: 'root'
})
export class FaqService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
 
      
  getFaq(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/faq/view`,{id});
  }

  getFaqList(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/faq/index`,{id});
  }
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/faq/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/faq/create`, data);
  }
}
