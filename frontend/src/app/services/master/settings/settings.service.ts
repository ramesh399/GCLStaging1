import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { Settings } from '@app/models/master/settings';


@Injectable({
  providedIn: 'root'
})
export class SettingsService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }      
 
  getSettings(): Observable<Settings>{
    return this.http.get<Settings>(`${environment.apiUrl}/master/settings/index`);
  }   
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/settings/update`, formData,this.httpOptions);
  } 
}
