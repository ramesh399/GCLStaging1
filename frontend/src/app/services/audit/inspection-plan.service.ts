import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { InspectionPlan } from '@app/models/audit/inspection-plan';

@Injectable({
  providedIn: 'root'
})
export class InspectionPlanService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-inspection-plan/create`,data);
  } 

  getInspectionPlan(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-inspection-plan/view-inspection-plan`,data);
  }	

  deleteData(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-inspection-plan/delete-data`, data);
  }

  downloadInspectionPlan(data){
    return this.http.post(`${environment.apiUrl}/audit/audit-inspection-plan/generate`,data,
    {responseType:'arraybuffer'}
    );
  }	
}
