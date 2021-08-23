import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {ProductType} from '@app/models/master/producttype';


@Injectable({
  providedIn: 'root'
})
export class ProductTypeService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
      
  getProductType(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/product-type/view`,{id});
  }
  
  getProductTypeList(): Observable<ProductType[]>{
    return this.http.get<ProductType[]>(`${environment.apiUrl}/master/product-type/index`);
  }  
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/product-type/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/product-type/create`, data);
  } 
}
