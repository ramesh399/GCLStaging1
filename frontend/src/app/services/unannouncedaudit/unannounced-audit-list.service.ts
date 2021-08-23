import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {Application} from '@app/models/application/application';

interface SearchResult {
  applications: Application[];
  total: number;
}
interface State {
  page: number;
  pageSize: number;
  statusFilter:any;
  searchTerm: string;
  sortColumn: string;
  sortDirection: SortDirection;
  standardFilter:any;
  riskFilter:any;
  from_date:any;
  to_date:any;
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(applications: Application[], column: string, direction: string): Application[] {
  //console.log('234324');
  if (direction === '') {
    return applications;
  } else {
    return [...applications].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(application: Application, term: string, pipe: PipeTransform) {

  return application.company_name.toLowerCase().includes(term.toLowerCase());  
}



@Injectable({
  providedIn: 'root'
})
export class UnannouncedAuditListService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _applications$ = new BehaviorSubject<Application[]>([]);
  private _total$ = new BehaviorSubject<number>(0);

  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    sortDirection: '',
    statusFilter:'',
	  standardFilter:'',
	  riskFilter:'',
    from_date:'',
    to_date:''
  };

  constructor( private http:HttpClient,public errorSummary: ErrorSummaryService) { 
	this._state.pageSize=this.errorSummary.pageLimit;
    this._search$.pipe(
      tap(() => this._loading$.next(true)),
      //debounceTime(200),
      switchMap(() => this._search()),
      //delay(200),
      tap(() => this._loading$.next(false))
    ).subscribe(result => {
      this._applications$.next(result.applications);
      this._total$.next(result.total);
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get applications$() { return this._applications$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
  get loading$() { return this._loading$.asObservable(); }
  get page() { return this._state.page; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get statusFilter() { return this._state.statusFilter; }
  get pageSize() { return this._state.pageSize; }
  get searchTerm() { return this._state.searchTerm; }
  get standardFilter() { return this._state.standardFilter; }
  get riskFilter() { return this._state.riskFilter; }
  get from_date() { return this._state.from_date; }
  get to_date() { return this._state.to_date; }

  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set statusFilter(statusFilter: number) { this._set({statusFilter}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }
  set standardFilter(standardFilter: any) { this._set({standardFilter}); }
  set riskFilter(riskFilter: any) { this._set({riskFilter}); }
  set from_date(from_date: any) { this._set({from_date}); }
  set to_date(to_date: any) { this._set({to_date}); }

  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }

  private _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, statusFilter, pageSize, page, searchTerm, standardFilter, riskFilter, from_date, to_date} = this._state;
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

    return this.http.post<SearchResult>(`${environment.apiUrl}/unannouncedaudit/unannounced-audit/index`,{page,pageSize,searchTerm,statusFilter,sortColumn,sortDirection,standardFilter, riskFilter,from_date:from_date_format,to_date:to_date_format}).pipe(
        map(result => {
          return {applications:result.applications,total:result.total};
        })
    );

  }

  

  getUnitsbystds(data):Observable<any>{
    
    return this.http.post<any>(`${environment.apiUrl}/unannouncedaudit/unannounced-audit/get-unit`,data);
    
  }

  getUnannouncedAudit(data):Observable<any>{
    
    return this.http.post<any>(`${environment.apiUrl}/unannouncedaudit/unannounced-audit/view`,data);
    
  }

  getRiskoptions():Observable<any>{
    
    return this.http.post<any>(`${environment.apiUrl}/unannouncedaudit/unannounced-audit/get-risklist`,{});
    
  }

  SaveAudit(data):Observable<any>{
    
    return this.http.post<any>(`${environment.apiUrl}/unannouncedaudit/unannounced-audit/save-unannounced-audit`,data);
    
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
