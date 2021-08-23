import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Product} from '@app/models/master/product';
import {ProductType} from '@app/models/master/producttype';
import {LabelGrade} from '@app/models/master/labelgrade';
import {MaterialComposition} from '@app/models/master/materialcomposition';

@Injectable({
  providedIn: 'root'
})
export class ProductService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
      
  getProduct(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/product/view`,{id});
  }
  
  getProductList(): Observable<Product[]>{
    return this.http.get<Product[]>(`${environment.apiUrl}/master/product/get-product`);
  }  

  getProductTypes(product_id): Observable<ProductType[]>{
    return this.http.post<ProductType[]>(`${environment.apiUrl}/master/product-type/list`,{product_id});
  }

  getStandardLabel(standard_id): Observable<LabelGrade[]>{
    return this.http.post<LabelGrade[]>(`${environment.apiUrl}/master/standard-label-grade/list`,{standard_id});
  }
  getMaterial(product_type_id): Observable<MaterialComposition[]>{
    return this.http.post<MaterialComposition[]>(`${environment.apiUrl}/master/product-type-material-composition/searchlist`,{product_type_id});
  }
  
  
  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/product/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/product/create`, data);
  } 
  
  getProductType(id): Observable<ProductType[]>{
    
    let params = new HttpParams();
    params = params.append('id', id);
    return this.http.get<ProductType[]>(`${environment.apiUrl}/master/product/producttype`,{params:params});
  }
}
