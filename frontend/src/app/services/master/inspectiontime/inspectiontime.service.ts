import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Inspectiontime} from '@app/models/master/inspectiontime';


@Injectable({
  providedIn: 'root'
})
export class InspectiontimeService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }

      
  getInspectiontime(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/standard-inspection-time/view`,{id});
  }

  getDaysData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/standard-inspection-time/getdata`, data);
  }

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/standard-inspection-time/create`, data);
  } 

  addStdData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/standard-inspection-time/create-standard`, data);
  } 

  

  addDaysData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/standard-inspection-time/adddaysdata`, data);
  }

  deleteDaysData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/standard-inspection-time/deletedays`, data);
  }

  deleteOtherData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/standard-inspection-time/deleteothers`, data);
  }

  deleteStandardData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/standard-inspection-time/deletestd`, data);
  }
}
