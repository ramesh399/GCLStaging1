import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { first, catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';


import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {Gislog} from '@app/models/library/gislog';

interface SearchResult {
  gislogs: Gislog[];
  total: number;
}
interface State {
  page: number;
  pageSize: number;
  searchTerm: string;
  statusFilter:any;
  sortColumn: string;
  sortDirection: SortDirection;
  typeFilter:any;
  from_date:any;
  to_date:any;
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(gislogs: Gislog[], column: string, direction: string): Gislog[] {
  if (direction === '') {
    return gislogs;
  } else {
    return [...gislogs].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(gislogs: Gislog, term: string, pipe: PipeTransform) {

  return gislogs.title.toLowerCase().includes(term.toLowerCase());
}



@Injectable({
  providedIn: 'root'
})
export class LogListService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _gislogs$ = new BehaviorSubject<Gislog[]>([]);
  private _total$ = new BehaviorSubject<number>(0);
   
  private unit_id:number;
  private audit_plan_id:number;
  private audit_id:number;
  private type:any;

  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    sortDirection: '',
    statusFilter:'',
    typeFilter:'',
	from_date:'',
    to_date:''
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
      this._gislogs$.next(result.gislogs);
      this._total$.next(result.total);
      
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get gislogs$() { return this._gislogs$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
   
  get loading$() { return this._loading$.asObservable(); }
  get page() { return this._state.page; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get pageSize() { return this._state.pageSize; }
  get searchTerm() { return this._state.searchTerm; }
  get statusFilter() { return this._state.statusFilter; }
  get typeFilter() { return this._state.typeFilter; }
  get from_date() { return this._state.from_date; }
  get to_date() { return this._state.to_date; }

  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }
  set statusFilter(statusFilter: number) { this._set({statusFilter}); }
  set typeFilter(typeFilter: number) { this._set({typeFilter}); }
  set from_date(from_date: any) { this._set({from_date}); }
  set to_date(to_date: any) { this._set({to_date}); }


  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }
  
  private _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, pageSize, page, searchTerm,statusFilter,typeFilter, from_date, to_date} = this._state;
	/*
	this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
	this.audit_plan_id = this.activatedRoute.snapshot.queryParams.audit_plan_id;
	this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
  */
	this.type = 'test';
			  /*
    return this.http.post<SearchResult>(`${environment.apiUrl}/library/gis/index`,{type:this.type,page,pageSize,searchTerm,sortColumn,sortDirection}).pipe(
        map(result => {
          return {gislogs:result.gislogs,total:result.total};
        })
    );*/
	
	let from_date_format:any;
    let to_date_format:any;
    if(from_date)
    {
		from_date_format = this.errorSummary.displayDateFormat(from_date);
    }

    if(to_date)
    {
		to_date_format = this.errorSummary.displayDateFormat(to_date);
    }

    return this.http.post<SearchResult>(`${environment.apiUrl}/library/gis/index`,{type:this.type,page,pageSize,searchTerm,sortColumn,sortDirection,statusFilter,typeFilter,from_date:from_date_format,to_date:to_date_format}).pipe(
        map(result => {
          return {gislogs:result.gislogs,total:result.total};
        })
    );

  }

  
  public customSearch(){
    this._gislogs$.next([]);
    this._total$.next(0);
    this._loading$.next(true);
    this._search$.next();
  }
  

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/gis/create`, data);
  }
  getGisLogData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/gis/getlogdata`, data);
  }
  deleteGisLogData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/gis/deletegislog`, data);
  }
  deleteGisData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/gis/deletegis`, data);
  }
  addGisLogData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/gis/addgislogdata`, data);
  }
  downloadGisFile(data){
    return this.http.post(`${environment.apiUrl}/library/gis/gisfile`,data,
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
