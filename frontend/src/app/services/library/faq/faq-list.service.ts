import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import { Faq } from '@app/models/library/faq';

interface SearchResult {
  faqs: Faq[];
  total: number;
}
interface State {
  page: number;
  pageSize: number;
  searchTerm: string;
  sortColumn: string;
  sortDirection: SortDirection;
  roleFilter:any;
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(faqs: Faq[], column: string, direction: string): Faq[] {
  //console.log('234324');
  if (direction === '') {
    return faqs;
  } else {
    return [...faqs].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(faq: Faq, term: string, pipe: PipeTransform) {
  return faq.question.toLowerCase().includes(term.toLowerCase());  
}



@Injectable({
  providedIn: 'root'
})
export class FaqListService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _faqs$ = new BehaviorSubject<Faq[]>([]);
  private _total$ = new BehaviorSubject<number>(0);
  private category:number;

  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    sortDirection: '',
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
      this._faqs$.next(result.faqs);
      this._total$.next(result.total);
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get faqs$() { return this._faqs$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
  get loading$() { return this._loading$.asObservable(); }
  get page() { return this._state.page; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get pageSize() { return this._state.pageSize; }
  get searchTerm() { return this._state.searchTerm; }
  get roleFilter() { return this._state.roleFilter; }

  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }
  set roleFilter(roleFilter: any) { this._set({roleFilter}); }

  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }

  private _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, pageSize, page, searchTerm, roleFilter} = this._state;
	
    
    return this.http.post<SearchResult>(`${environment.apiUrl}/library/faq/index`,{page,pageSize,searchTerm,sortColumn,sortDirection, roleFilter}).pipe(
        map(result => {
          return {faqs:result.faqs,total:result.total};
        })
    );

  }

  public customSearch(){
    this._faqs$.next([]);
    this._total$.next(0);
    this._loading$.next(true);
    this._search$.next();
  }

  addData(faqData){
    return this.http.post<any>(`${environment.apiUrl}/library/faq/create`, faqData);    
  }
  deleteFaqData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/faq/deletefaqdata`, data);
  }

  getData(){
    return this.http.post<any>(`${environment.apiUrl}/library/faq/get-data`, {});
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

  commonActionData(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/faq/common-update`,data);
  } 
}
