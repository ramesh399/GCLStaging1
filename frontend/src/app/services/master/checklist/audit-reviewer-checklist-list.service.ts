import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {AuditReviewerChecklist} from '@app/models/master/audit-reviewer-checklist';

interface SearchResult {
  auditreviewerchecklists: AuditReviewerChecklist[];
  total: number;
}
interface State {
  page: number;
  pageSize: number;
  searchTerm: string;
  sortColumn: string;
  standardFilter:any;
  sortDirection: SortDirection;
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(auditreviewerchecklists: AuditReviewerChecklist[], column: string, direction: string): AuditReviewerChecklist[] {
  //console.log('234324');
  if (direction === '') {
    return auditreviewerchecklists;
  } else {
    return [...auditreviewerchecklists].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(auditreviewerchecklist: AuditReviewerChecklist, term: string, pipe: PipeTransform) {
  return auditreviewerchecklist.name.toLowerCase().includes(term.toLowerCase());  
}



@Injectable({
  providedIn: 'root'
})
export class AuditReviewerChecklistListService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _auditreviewerchecklists$ = new BehaviorSubject<AuditReviewerChecklist[]>([]);
  private _total$ = new BehaviorSubject<number>(0);
  private category:number;

  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    standardFilter:'',
    sortDirection: ''
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
      this._auditreviewerchecklists$.next(result.auditreviewerchecklists);
      this._total$.next(result.total);
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get auditreviewerchecklists$() { return this._auditreviewerchecklists$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
  get loading$() { return this._loading$.asObservable(); }
  get page() { return this._state.page; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get standardFilter() { return this._state.standardFilter; }
  get pageSize() { return this._state.pageSize; }
  get searchTerm() { return this._state.searchTerm; }

  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set standardFilter(standardFilter: any) { this._set({standardFilter}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }

  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }

  private _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, pageSize, page,standardFilter, searchTerm} = this._state;
	
	this.category = this.activatedRoute.snapshot.queryParams.category; 
    
    return this.http.post<SearchResult>(`${environment.apiUrl}/master/audit-reviewer-checklist/index`,{category:this.category,page,pageSize,searchTerm,sortColumn,standardFilter,sortDirection}).pipe(
        map(result => {
          return {auditreviewerchecklists:result.auditreviewerchecklists,total:result.total};
        })
    );

  }

  addAuditReviewerChecklist(auditreviewerchecklistData){
    return this.http.post<any>(`${environment.apiUrl}/master/audit-reviewer-checklist/create`, auditreviewerchecklistData);    
  }

  getAuditReviewerChecklist():Observable<any>{    
    return this.http.get<any>(`${environment.apiUrl}/master/audit-reviewer-checklist/index`,this.httpOptions);    
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
    return this.http.post<any>(`${environment.apiUrl}/master/audit-reviewer-checklist/common-update`,data);
  } 
}
