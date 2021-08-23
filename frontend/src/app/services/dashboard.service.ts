import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';


@Injectable({
  providedIn: 'root'
})

export class DashboardService {

  constructor(private http: HttpClient) { }
  
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }

  getapp_id(id): Observable<any>{    
    return this.http.post<any>(`${environment.apiUrl}/application/apps/get-userid`,{id});    
  }

  getCustomerData(): Observable<any[]>{    
    return this.http.get<any[]>(`${environment.apiUrl}/master/dashboard/customer-dashboard`);    
  }

  getFranchiseData(): Observable<any[]>{    
    return this.http.get<any[]>(`${environment.apiUrl}/master/dashboard/franchise-dashboard`);    
  }
  
  getUserData(): Observable<any[]>{    
    return this.http.get<any[]>(`${environment.apiUrl}/master/dashboard/user-dashboard`);    
  }

  downloadFile(data){
    return this.http.post(`${environment.apiUrl}/master/dashboard/download-certificate`,data,
      {responseType:'arraybuffer'}
    );
  }
  
}
