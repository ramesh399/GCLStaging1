import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { first, catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';


import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {Request} from '@app/models/transfer-certificate/request';
import {Application} from '@app/models/application/application';


@Injectable({
  providedIn: 'root'
})
export class ProductAdditionService {
  
  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }

  getAdditionData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/getprocessdata`,data);
  }

  getApplication(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/unit-addition/get-unitdetails`,data);
  }

  getAppStandard(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/get-productstd`, data);
  }

  getUnit(id)
  {
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/get-unit`, {id});
  }

  getAppData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/get-appdata`, data);
  }


  getAppCompanyData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/get-appcompanydata`, data);
  }

  getProductData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/get-productdata`, {id});
  }

  getProductDetails(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/get-product-details`,id);
  }

  updateProductMaterialComposition(data): Observable<any>{    
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/updateproductmaterial`,data);
  }
  
  getStatusList(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/get-status`, data);
  }

  addReviewerchecklist(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/add-reviewer-review`, data);
  }

  addOspchecklist(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/add-osp-review`, data);
  }

  assignReviewer(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/assign-reviewer`, data);
  }
  
   assignCertificationReviewer(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/assign-certification-reviewer`, data);
  }

  getData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/view`, {id});
  }

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/create`, data);
  }
 
  deleteData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/deleteproduct`, data);
  }

  submitForAddition(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/submitadditionunit`, data);
  }
  
   
  updateAppProductData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/updateproduct`, data);
  }
  
  getUnitData(id){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/get-appunitdata`, {id});
  }
  
  addProductAdditionData(data){
    return this.http.post<any>(`${environment.apiUrl}/changescope/product-addition/createproductaddition`, data);
  }
}
