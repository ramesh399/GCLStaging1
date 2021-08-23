import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { BusinessSectorGroup } from '@app/models/master/business-sector-group';


@Injectable({
  providedIn: 'root'
})
export class BusinessSectorGroupService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getBusinessSectorGroup(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/business-sector-group/view`,{id});
  }

  
  getBusinessSectorGroupView(id): Observable<BusinessSectorGroup[]>{
    return this.http.post<BusinessSectorGroup[]>(`${environment.apiUrl}/master/business-sector-group/getbsector-group`,{id});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/business-sector-group/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/business-sector-group/create`, data);
  }
}
