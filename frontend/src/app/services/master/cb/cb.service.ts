import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { Cb } from '@app/models/master/cb';

@Injectable({
  providedIn: 'root'
})
export class CbService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getCb(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/cb/view`,{id});
  }

  getCbs(data): Observable<Cb[]>{

    return this.http.post<Cb[]>(`${environment.apiUrl}/master/cb/cbs`,data);
  } 

  getCbList(): Observable<Cb[]>{
    return this.http.get<Cb[]>(`${environment.apiUrl}/master/cb/get-cb`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/cb/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/cb/create`, data);
  }
}
