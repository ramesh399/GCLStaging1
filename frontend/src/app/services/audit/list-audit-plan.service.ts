import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {Invoice} from '@app/models/invoice/invoice';

interface SearchResult {
  listauditplan: Invoice[];
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
  brandFilter:any;
  riskFilter:any;
  franchiseFilter:any;
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(listauditplan: Invoice[], column: string, direction: string): Invoice[] {
  //console.log('234324');
  if (direction === '') {
    return listauditplan;
  } else {
    return [...listauditplan].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(invoice: Invoice, term: string, pipe: PipeTransform) {

  return invoice.invoice_number.toLowerCase().includes(term.toLowerCase());  
}



@Injectable({
  providedIn: 'root'
})
export class ListAuditPlanService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _listauditplan$ = new BehaviorSubject<Invoice[]>([]);
  private _total$ = new BehaviorSubject<number>(0);

  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    sortDirection: '',
    statusFilter:'',
    standardFilter:'',
    brandFilter:'',
    riskFilter:'',
	franchiseFilter:''
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
      this._listauditplan$.next(result.listauditplan);
      this._total$.next(result.total);
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get listauditplan$() { return this._listauditplan$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
  get loading$() { return this._loading$.asObservable(); }
  get statusFilter() { return this._state.statusFilter; }
  get page() { return this._state.page; }
  get pageSize() { return this._state.pageSize; }
  get searchTerm() { return this._state.searchTerm; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get standardFilter() { return this._state.standardFilter; }
  get brandFilter() { return this._state.brandFilter; }
  get riskFilter() { return this._state.riskFilter; }
  get franchiseFilter() { return this._state.franchiseFilter; }

  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set statusFilter(statusFilter: number) { this._set({statusFilter}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }
  set standardFilter(standardFilter: any) { this._set({standardFilter}); }
  set brandFilter(brandFilter: any) { this._set({brandFilter}); }
  set riskFilter(riskFilter: any) { this._set({riskFilter}); }
  set franchiseFilter(franchiseFilter: any) { this._set({franchiseFilter}); }


  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }

  private _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, statusFilter, pageSize, page, searchTerm, standardFilter,brandFilter, riskFilter, franchiseFilter} = this._state;
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

    return this.http.post<SearchResult>(`${environment.apiUrl}/audit/audit-plan/list-audit-plan`,{type:'audit',page,statusFilter,pageSize,searchTerm,sortColumn,sortDirection,standardFilter,brandFilter,riskFilter,franchiseFilter}).pipe(
        map(result => {
          return {listauditplan:result.listauditplan,total:result.total};
        })
    );

  }
  
  downloadInvoiceFile(data){
    return this.http.post(`${environment.apiUrl}/offer/invoice/create`,data,
      {responseType:'arraybuffer'}
    );
  }

  getReviewOptions(): Observable<any>{
	  return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/review-riskcategory`,{});
  }

 
  auditStatus(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/audit-status`,{});
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
