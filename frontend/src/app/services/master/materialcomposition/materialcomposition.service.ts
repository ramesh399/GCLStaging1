import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {MaterialComposition} from '@app/models/master/materialcomposition';


@Injectable({
  providedIn: 'root'
})
export class MaterialCompositionService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
      
  getMaterialComposition(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/product-type-material-composition/view`,{id});
  }
  
  getMaterialCompositionList(): Observable<MaterialComposition[]>{
    return this.http.get<MaterialComposition[]>(`${environment.apiUrl}/master/product-type-material-composition/index`);
  }  
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/product-type-material-composition/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/product-type-material-composition/create`, data);
  } 
}
