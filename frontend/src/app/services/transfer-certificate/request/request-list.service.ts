import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { first, catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {Request} from '@app/models/transfer-certificate/request';

interface SearchResult {
  request: Request[];
  total: number; 
}
interface State {
  page: number;
  pageSize: number;
  statusFilter:any;
  standardFilter:any;
  brandFilter:any;
  searchTerm: string;
  sortColumn: string;
  sortDirection: SortDirection;
  franchiseFilter:any;
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(request: Request[], column: string, direction: string): Request[] {
  if (direction === '') {
    return request;
  } else {
    return [...request].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(request: Request, term: string, pipe: PipeTransform) {

  return request.purchase_order_number.toLowerCase().includes(term.toLowerCase());
}



@Injectable({
  providedIn: 'root'
})
export class RequestListService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _request$ = new BehaviorSubject<Request[]>([]);
  private _total$ = new BehaviorSubject<number>(0); 
   
 
  private type:any;
  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    sortDirection: '',
    statusFilter:'',
	standardFilter:'',
  brandFilter:'',
	franchiseFilter:''  
  };

  constructor( private activatedRoute:ActivatedRoute,private http:HttpClient,public errorSummary: ErrorSummaryService) {
	this._state.pageSize=this.errorSummary.pageLimit;
    this._search$.pipe(
      tap(() => this._loading$.next(true)),
      //debounceTime(200),
      switchMap(() => this._search()),
      //delay(200),
      tap(() => this._loading$.next(false))
    ).subscribe(result => {
      this._request$.next(result.request);
      this._total$.next(result.total);           
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get request$() { return this._request$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
  //get source_file_status$() { return this._source_file_status$.asObservable(); }
  //get view_file_status$() { return this._view_file_status$.asObservable(); }
   
  get loading$() { return this._loading$.asObservable(); }
  get page() { return this._state.page; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get pageSize() { return this._state.pageSize; }
  get statusFilter() { return this._state.statusFilter; }
  get standardFilter() { return this._state.standardFilter; }
  get brandFilter() { return this._state.brandFilter; }
  get searchTerm() { return this._state.searchTerm; }
  get franchiseFilter() { return this._state.franchiseFilter; }

  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set statusFilter(statusFilter: number) { this._set({statusFilter}); }
  set standardFilter(standardFilter: any) { this._set({standardFilter}); }
  set brandFilter(brandFilter: any) { this._set({brandFilter}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }
  set franchiseFilter(franchiseFilter: any) { this._set({franchiseFilter}); }
    
  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }
  
  private _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, pageSize,statusFilter,standardFilter,brandFilter, page, searchTerm, franchiseFilter} = this._state;
	/*
	this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
	this.audit_plan_id = this.activatedRoute.snapshot.queryParams.audit_plan_id;
	this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    */
    //this.type = this.activatedRoute.snapshot.queryParams.type;
    this.type = this.activatedRoute.snapshot.data['pageType'];
	
    return this.http.post<SearchResult>(`${environment.apiUrl}/transfercertificate/request/index`,{type:this.type,page,pageSize,statusFilter,searchTerm,sortColumn,standardFilter,brandFilter,sortDirection,franchiseFilter}).pipe(
        map(result => {
          return {request:result.request,total:result.total};
        })
    );

  }

  
  public customSearch(){
    this._request$.next([]);
    this._total$.next(0);
    //this._source_file_status$.next(0);
    //this._view_file_status$.next(0);
    this._loading$.next(true);
    this._search$.next();
  }
  

  getStatusList(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/get-status`, data);
  }

  getFilterStatus(){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/get-filter-status`, {});
  }
 
  getAppData(){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/get-appdata`, {});
  }

  getUnitData(id){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/get-appunitdata`, {id});
  }

  loadCompanyAddress(id){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/get-appaddress`, id);
  }

  loadUnitAddress(id){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/get-unitaddress`, id);
  }

  loadBuyerAddress(id){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/get-buyeraddress`, id);
  }

  loadInspectionAddress(id){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/get-inspectionaddress`, id);
  }

  getStandardData(id){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/get-appstddata`, {id});
  }

  checkStandardCobination(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/checkstandardcombination`, data);
  }  
  
  getData(id){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/view`, {id});
  }

  downloadBLFile(data){
    return this.http.post(`${environment.apiUrl}/transfercertificate/request/download-blfile`, data,
    {responseType:'arraybuffer'});
  }   

  downloadFile(data){
    return this.http.post(`${environment.apiUrl}/transfercertificate/request/download-file`, data,
    {responseType:'arraybuffer'});
  } 

  downloadEvidenceFile(data){
    return this.http.post(`${environment.apiUrl}/transfercertificate/request/downloadevidencefile`, data,
    {responseType:'arraybuffer'});
  } 

  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/create`, data);
  }
 
  deleteData(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/deletedata`, data);
  }
  
  addProductData(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/addproductdata`, data);
  }
  
  getProductData(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/getproductdata`, data);
  }
  
  deleteProductData(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/deleteproductdata`, data);
  }
  

  getStandardwisematerial(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/raw-material/standardwisematerial`, data);
  }
  
  productWiseRawMaterialInputs(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/productwiserawmaterialinputs`, data);
  }
    
  assignReviewer(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/assign-reviewer`, data);
  }

  addReviewerchecklist(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/add-reviewer-review`, data);
  }

  addDeclaration(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/add-declaration`, data);
  }

  addOspchecklist(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/add-osp-review`, data);
  }

  
  changeStatus(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/change-status`, data);
  }
  
  addEvidenceData(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/evidence-document`, data);
  }
  
  changeProudctWastagePercentage(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/change-product-wastage-percentage`, data);
  }
  
  changeAdditionalWeight(data){
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/change-additional-weight`, data);
  }
  
  

  private handleError(error: HttpErrorResponse) {
    if (error.error instanceof ErrorEvent) {
      // A client-side or network error occurred. Handle it accordingly.
      console.error('An error occurred:', error.error.message);
    } else {
      // The backend returned an unsuccessful response code.
      // The response body may contain clues as to what went wrong,
      console.error(
        `Backend returned code ${error.status}, ` +
        `body was: ${error.error}`);
    }
    // return an observable with a user-facing error message
    return throwError(
      'Something bad happened; please try again later.');
  };
  
  // ------ New Code Start Here ---------
  commonActionData(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/deletedata`,data);
  } 

  clonerequestData(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/transfercertificate/request/copyrequestdetails`,data);
  } 
  // ------ New Code End Here ---------
  
}
