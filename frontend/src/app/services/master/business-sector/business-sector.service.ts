import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { BusinessSector } from '@app/models/master/business-sector';
import { BusinessSectorGroup } from '@app/models/master/business-sector-group';


@Injectable({
  providedIn: 'root'
})
export class BusinessSectorService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getBusinessSector(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/business-sector/view`,{id});
  }

  getBusinessSectors(data): Observable<BusinessSector[]>{

    return this.http.post<BusinessSector[]>(`${environment.apiUrl}/master/business-sector/business-sectors`,data);
  } 

  getBusinessSectorsbystds(data): Observable<BusinessSector[]>{

    return this.http.post<BusinessSector[]>(`${environment.apiUrl}/master/business-sector/get-business-sectors-by-standard`,data);
  } 

  getBusinessSectorGroupsbystds(data): Observable<BusinessSectorGroup[]>{

    return this.http.post<BusinessSectorGroup[]>(`${environment.apiUrl}/master/business-sector/get-business-sector-groups-by-standard`,data);
  } 

  getBusinessSectorGroups(data): Observable<BusinessSectorGroup[]>{

    return this.http.post<BusinessSectorGroup[]>(`${environment.apiUrl}/master/business-sector/business-sector-groups`,data);
  } 
  
  getBusinessSectorList(): Observable<BusinessSector[]>{
    return this.http.get<BusinessSector[]>(`${environment.apiUrl}/master/business-sector/get-business-sector`,{});
  } 
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/business-sector/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/business-sector/create`, data);
  }
}
