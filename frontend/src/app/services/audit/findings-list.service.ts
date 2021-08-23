import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { first, catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {UnitFindings} from '@app/models/audit/unit-findings';

interface SearchResult {
  unitfindings: UnitFindings[];
  total: number;
  auditplanStatus:any;
}
interface State {
  page: number;
  pageSize: number;
  searchTerm: string;
  sortColumn: string;
  sortDirection: SortDirection;
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(unitfindings: UnitFindings[], column: string, direction: string): UnitFindings[] {
  if (direction === '') {
    return unitfindings;
  } else {
    return [...unitfindings].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(unitfindings: UnitFindings, term: string, pipe: PipeTransform) {

  return unitfindings.finding.toLowerCase().includes(term.toLowerCase());
  /*return country.finding.toLowerCase().includes(term.toLowerCase())
    || pipe.transform(country.area).includes(term)
    || pipe.transform(country.population).includes(term);
    */
}



@Injectable({
  providedIn: 'root'
})
export class UnitFindingsListService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _unitfindings$ = new BehaviorSubject<UnitFindings[]>([]);
  private _total$ = new BehaviorSubject<number>(0);
  private _planStatus$ = new BehaviorSubject<any>('');
  private unit_id:number;
  private audit_plan_id:number;
  private audit_id:number;
  private type:any;
  private subtopic:any;

  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
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
      this._unitfindings$.next(result.unitfindings);
      this._total$.next(result.total);
      this._planStatus$.next(result.auditplanStatus);
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get unitfindings$() { return this._unitfindings$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
  get auditplanStatus$() { return this._planStatus$.asObservable(); }
  get loading$() { return this._loading$.asObservable(); }
  get page() { return this._state.page; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get pageSize() { return this._state.pageSize; }
  get searchTerm() { return this._state.searchTerm; }

  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }

  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }

  private _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, pageSize, page, searchTerm} = this._state;
	
	this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
	this.audit_plan_id = this.activatedRoute.snapshot.queryParams.audit_plan_id;
	this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
	this.type = this.activatedRoute.snapshot.queryParams.type;
  this.subtopic = this.activatedRoute.snapshot.queryParams.subtopic;	  
    return this.http.post<SearchResult>(`${environment.apiUrl}/audit/audit-execution/index`,{subtopic:this.subtopic,unit_id:this.unit_id,audit_plan_id:this.audit_plan_id,audit_id:this.audit_id,type:this.type,page,pageSize,searchTerm,sortColumn,sortDirection}).pipe(
        map(result => {
          return {unitfindings:result.unitfindings,total:result.total,auditplanStatus:result.auditplanStatus};
        })
    );

  }

  
  public customSearch(){
    /*this._search$.pipe(
      first(),
      tap(() => this._loading$.next(true)),
      //debounceTime(200),
      switchMap(() => this._search()),
      //delay(200),
      tap(() => this._loading$.next(false))
    ).subscribe(result => {
      this._unitfindings$.next(result.unitfindings);
      this._total$.next(result.total);
      this._planStatus$.next(result.auditplanStatus);
    }); */
    this._search$.next();
  }
  // getBusinessSector():Observable<any>{
    
  //   return this.http.get<any>(`${environment.apiUrl}/master/business-sector/index`,this.httpOptions);
    
  // }



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
