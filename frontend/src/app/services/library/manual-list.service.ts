import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { first, catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';


import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {Manual} from '@app/models/library/manual';

interface SearchResult {
  manual: Manual[];
  total: number;
  source_file_status: number;
  view_file_status: number;
}
interface State {
  page: number;
  pageSize: number;
  statusFilter:any;
  searchTerm: string;
  sortColumn: string;
  sortDirection: SortDirection;
  roleFilter:any;
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(manual: Manual[], column: string, direction: string): Manual[] {
  if (direction === '') {
    return manual;
  } else {
    return [...manual].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(manual: Manual, term: string, pipe: PipeTransform) {

  return manual.title.toLowerCase().includes(term.toLowerCase());
}



@Injectable({
  providedIn: 'root'
})
export class ManualListService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _manual$ = new BehaviorSubject<Manual[]>([]);
  private _total$ = new BehaviorSubject<number>(0);
  private _source_file_status$ = new BehaviorSubject<number>(0);
  private _view_file_status$ = new BehaviorSubject<number>(0);
   
 
  private type:any;
  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    sortDirection: '',
    statusFilter:1,
	roleFilter:''
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
      this._manual$.next(result.manual);
      this._total$.next(result.total);
      this._source_file_status$.next(result.source_file_status);
      this._view_file_status$.next(result.view_file_status);
      
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get manual$() { return this._manual$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
  get source_file_status$() { return this._source_file_status$.asObservable(); }
  get view_file_status$() { return this._view_file_status$.asObservable(); }
   
  get loading$() { return this._loading$.asObservable(); }
  get page() { return this._state.page; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get pageSize() { return this._state.pageSize; }
  get statusFilter() { return this._state.statusFilter; }
  get searchTerm() { return this._state.searchTerm; }
  get roleFilter() { return this._state.roleFilter; }

  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set statusFilter(statusFilter: number) { this._set({statusFilter}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }
  set roleFilter(roleFilter: any) { this._set({roleFilter}); }

  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }
  
  private _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, pageSize,statusFilter, page, searchTerm, roleFilter} = this._state;
	/*
	this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
	this.audit_plan_id = this.activatedRoute.snapshot.queryParams.audit_plan_id;
	this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
  */
  //this.type = this.activatedRoute.snapshot.queryParams.type;
  this.type = this.activatedRoute.snapshot.data['pageType'];
	
    return this.http.post<SearchResult>(`${environment.apiUrl}/library/download/index`,{type:this.type,page,pageSize,statusFilter,searchTerm,sortColumn,sortDirection,roleFilter}).pipe(
        map(result => {
          return {manual:result.manual,total:result.total,source_file_status:result.source_file_status,view_file_status:result.view_file_status};
        })
    );

  }

  
  public customSearch(){
    this._manual$.next([]);
    this._total$.next(0);
    this._source_file_status$.next(0);
    this._view_file_status$.next(0);
    this._loading$.next(true);
    this._search$.next();
  }
  

  getData(){
    return this.http.post<any>(`${environment.apiUrl}/library/download/get-data`, {});
  }

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/download/create`, data);
  }
 
  deleteData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/download/deletedata`, data);
  }
  
  downloadFile(data){
    return this.http.post(`${environment.apiUrl}/library/download/downloadfile`,data,
      {responseType:'arraybuffer'}
    );
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
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-execution/common-update`,data);
  } 
  // ------ New Code End Here ---------
  
}
