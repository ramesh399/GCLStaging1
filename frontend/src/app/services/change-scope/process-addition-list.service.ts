import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { first, catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {Processaddition} from '@app/models/changescope/processaddition';

interface SearchResult {
  processadditions: Processaddition[];
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
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(processadditions: Processaddition[], column: string, direction: string): Processaddition[] {
  if (direction === '') {
    return processadditions;
  } else {
    return [...processadditions].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(processadditions: Processaddition, term: string, pipe: PipeTransform) {

  return processadditions.company_name.toLowerCase().includes(term.toLowerCase());
}


@Injectable({
  providedIn: 'root'
})
export class ProcessAdditionListService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _processadditions$ = new BehaviorSubject<Processaddition[]>([]);
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
    typeFilter:''
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
      this._processadditions$.next(result.processadditions);
      this._total$.next(result.total);
      
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get processadditions$() { return this._processadditions$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
   
  get loading$() { return this._loading$.asObservable(); }
  get page() { return this._state.page; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get pageSize() { return this._state.pageSize; }
  get searchTerm() { return this._state.searchTerm; }
  get statusFilter() { return this._state.statusFilter; }
  get typeFilter() { return this._state.typeFilter; }

  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }
  set statusFilter(statusFilter: number) { this._set({statusFilter}); }
  set typeFilter(typeFilter: number) { this._set({typeFilter}); }


  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }
  
  private _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, pageSize, page, searchTerm,statusFilter,typeFilter} = this._state;
	 
	this.type = 'test';
		 
    return this.http.post<SearchResult>(`${environment.apiUrl}/changescope/process-addition/index`,{type:this.type,page,pageSize,searchTerm,sortColumn,sortDirection,statusFilter,typeFilter}).pipe(
        map(result => {
          return {processadditions:result.processadditions,total:result.total};
        })
    );

  }

  
  public customSearch(){
    this._processadditions$.next([]);
    this._total$.next(0);
    this._loading$.next(true);
    this._search$.next();
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
    return this.http.post<any>(`${environment.apiUrl}/changescope/process-addition/common-update`,data);
  } 
  // ------ New Code End Here ---------
  
}
