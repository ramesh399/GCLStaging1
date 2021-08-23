import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import { Issues } from '@app/models/itcenter/issues';
 

interface SearchResult {
  issues: Issues[];
  total: number;
}
interface State {
  page: number;
  pageSize: number;
  searchTerm: string;
  sortColumn: string;
  sortDirection: SortDirection;
  standardFilter:any;
  franchiseFilter:any;
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(issues: Issues[], column: string, direction: string): Issues[] {
  //console.log('234324');
  if (direction === '') {
    return issues;
  } else {
    return [...issues].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(offer: Issues, term: string, pipe: PipeTransform) {

  return offer.issue_type.toLowerCase().includes(term.toLowerCase());  
}

@Injectable({
  providedIn: 'root'
})
export class IssueService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _issue$ = new BehaviorSubject<any[]>([]);
  private _total$ = new BehaviorSubject<number>(0);

  public list: any = []

  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    sortDirection: '',
	standardFilter: '',
	franchiseFilter: ''
  };
  editData: any;

  constructor( private http:HttpClient,public errorSummary: ErrorSummaryService) {
	this._state.pageSize=this.errorSummary.pageLimit;
    this._search$.pipe(
      tap(() => this._loading$.next(true)),
      //debounceTime(200),
      switchMap(() => this._search()),
      //delay(200),
      tap(() => this._loading$.next(false))
    ).subscribe(result => {
       
      this._issue$.next(result.issues);
      this._total$.next(result.total);
    });

    this._search$.next();
  }

  

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get issue$() { 
     
    return this._issue$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
  get loading$() { return this._loading$.asObservable(); }
  get page() { return this._state.page; }
  get pageSize() { return this._state.pageSize; }
  get searchTerm() { return this._state.searchTerm; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get standardFilter() { return this._state.standardFilter; }
  get franchiseFilter() { return this._state.franchiseFilter; }
      
  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }
  set standardFilter(standardFilter: number) { this._set({standardFilter}); }
  set franchiseFilter(franchiseFilter: number) { this._set({franchiseFilter}); }
  

  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }

    _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, pageSize, page, searchTerm,standardFilter,franchiseFilter} = this._state;
    //console.log(sortColumn+sortDirection);
    // 1. sort
    //let countries = sort(COUNTRIES, sortColumn, sortDirection);

    // 2. filter
    // countries = countries.filter(country => matches(country, searchTerm, this.pipe));
    //const total = countries.length;

    // 3. paginate
    //countries = countries.slice((page - 1) * pageSize, (page - 1) * pageSize + pageSize);

    //console.log(pageSize,page);
    /*
    let params = new HttpParams();
    params = params.append('page', ''+page);
    params = params.append('pageSize', ''+pageSize);
    */

    return this.http.post<SearchResult>(`${environment.apiUrl}/informationcenter/issues/index`,{page,pageSize,searchTerm}).pipe(
        map(result => {
           
          return {issues:result.issues,total:result.total};
        })
    );

  }
   getProduct():Observable<any>{
    
    return this.http.get<any>(`${environment.apiUrl}/informationcenter/issues/index`,this.httpOptions);
    
  }


  createIssue(issue) {
     
    return this.http.post<any>(`${environment.apiUrl}/informationcenter/issues/create`, issue);    
  }
  deleteIssue(issue) {
     
    return this.http.post<any>(`${environment.apiUrl}/informationcenter/issues/delete`, issue);    
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
}