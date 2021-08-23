import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';


import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {Standardlabelgrade} from '@app/models/master/standardlabelgrade';

interface SearchResult {
  standardlabelgrades: Standardlabelgrade[];
  total: number;
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

function sort(standardlabelgrades: Standardlabelgrade[], column: string, direction: string): Standardlabelgrade[] {
  //console.log('234324');
  if (direction === '') {
    return standardlabelgrades;
  } else {
    return [...standardlabelgrades].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(standardlabelgrade: Standardlabelgrade, term: string, pipe: PipeTransform) {

  //return standardlabelgrade.name.toLowerCase().includes(term.toLowerCase());
  /*return country.name.toLowerCase().includes(term.toLowerCase())
    || pipe.transform(country.area).includes(term)
    || pipe.transform(country.population).includes(term);
    */
}



@Injectable({
  providedIn: 'root'
})
export class StandardlabelgradeListService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _standardlabelgrades$ = new BehaviorSubject<Standardlabelgrade[]>([]);
  private _total$ = new BehaviorSubject<number>(0);

  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    sortDirection: ''
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
      this._standardlabelgrades$.next(result.standardlabelgrades);
      this._total$.next(result.total);
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get standardlabelgrades$() { return this._standardlabelgrades$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
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

    return this.http.post<SearchResult>(`${environment.apiUrl}/master/standard-label-grade/index`,{page,pageSize,searchTerm,sortColumn,sortDirection}).pipe(
        map(result => {
          return {standardlabelgrades:result.standardlabelgrades,total:result.total};
        })
    );

  }

  addStandardlabelgrade(standardlabelgradeData){
    //return [{id:1, name:'USA'},{id:2, name:'India'}];
    //let standardlabelgradeDatas = Object.assign({}, standardlabelgradeData);
    return this.http.post<any>(`${environment.apiUrl}/master/standard-label-grade/create`, standardlabelgradeData);
    //, JSON.stringify(data)
  }

  getStandardlabelgrade():Observable<any>{
    
    return this.http.get<any>(`${environment.apiUrl}/master/standard-label-grade/index`,this.httpOptions);
    
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
    return this.http.post<any>(`${environment.apiUrl}/master/standard-label-grade/common-update`,data);
  } 
}
