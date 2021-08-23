import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Audittype} from '@app/models/master/audittype';


@Injectable({
  providedIn: 'root'
})
export class AudittypeService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  /*
  getAudittype(id:number): Observable<Audittype[]>{
    return this.http.get<Audittype[]>(`${environment.apiUrl}/master/audittype/view`,{});
  } 
  */
  
  getAudittype(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/audittype/view`,{id});
  }
  
  

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/audittype/create`, data);
  }  
  
  updateData(formData): Observable<any>{
    //X-Requested-With
    return this.http.post<any>(`${environment.apiUrl}/master/audittype/update`, formData,this.httpOptions);
  }
  
  /*
  getAudittypes(id): Observable<any>{
    let params = new HttpParams();
    params = params.append('id', id);
    return this.http.get<any>(`${environment.apiUrl}/master/audittype/view`,{params})
              .pipe(map(user => {
                if (user.data) {
                  return user;
                }else{
                  throw new Error(user.message);
                }
            }));
            
  } 
  */
  
  
}
