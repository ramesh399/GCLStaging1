import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';


import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import {Enquiry} from '@app/models/enquiry';

interface SearchResult {
  enquiries: Enquiry[];
  total: number;
}
interface State {
  page: number;
  pageSize: number;
  searchTerm: string;
  sortColumn: string;
  sortDirection: SortDirection;
  standardFilter:any;
  countryFilter:any;
  franchiseFilter:any;
  from_date:any;
  to_date:any;
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(enquiries: Enquiry[], column: string, direction: string): Enquiry[] {
  //console.log('234324');
  if (direction === '') {
    return enquiries;
  } else {
    return [...enquiries].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(enquiry: Enquiry, term: string, pipe: PipeTransform) {

  return enquiry.first_name.toLowerCase().includes(term.toLowerCase());
  /*return country.name.toLowerCase().includes(term.toLowerCase())
    || pipe.transform(country.area).includes(term)
    || pipe.transform(country.population).includes(term);
    */
}



@Injectable({
  providedIn: 'root'
})
export class EnquiryService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _enquiries$ = new BehaviorSubject<Enquiry[]>([]);
  private _total$ = new BehaviorSubject<number>(0);
  private type:number;

  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    sortDirection: '',
    standardFilter: '',
    countryFilter: '',
    franchiseFilter: '',
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
      this._enquiries$.next(result.enquiries);
      this._total$.next(result.total);
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get enquiries$() { return this._enquiries$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
  get loading$() { return this._loading$.asObservable(); }
  get page() { return this._state.page; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get pageSize() { return this._state.pageSize; }
  get searchTerm() { return this._state.searchTerm; }
  get standardFilter() { return this._state.standardFilter; }
  get countryFilter() { return this._state.countryFilter; }
  get franchiseFilter() { return this._state.franchiseFilter; }
  get from_date() { return this._state.from_date; }
  get to_date() { return this._state.to_date; }
  
  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }
  set standardFilter(standardFilter: any) { this._set({standardFilter}); }
  set countryFilter(countryFilter: any) { this._set({countryFilter}); }
  set franchiseFilter(franchiseFilter: any) { this._set({franchiseFilter}); }
  set from_date(from_date: any) { this._set({from_date}); }
  set to_date(to_date: any) { this._set({to_date}); }
  
  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }

  private _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, pageSize, page, searchTerm, standardFilter, countryFilter, franchiseFilter, from_date, to_date} = this._state;
	
	this.type = this.activatedRoute.snapshot.queryParams.type;
	
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
    
    return this.http.post<SearchResult>(`${environment.apiUrl}/enquiry/index`,{type:this.type,page,pageSize,searchTerm,sortColumn,sortDirection,standardFilter,countryFilter,franchiseFilter,from_date:from_date_format,to_date:to_date_format}).pipe(
        map(result => {
          return {enquiries:result.enquiries,total:result.total};
        })
    );

  }


  getEnquiry():Observable<any>{
    
    return this.http.get<any>(`${environment.apiUrl}/enquiry/index`,this.httpOptions);
    
  }
  addApplication(data): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<any>(`${environment.apiUrl}/application/apps/create`,data);
  }
  updateApplication(data): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<any>(`${environment.apiUrl}/application/apps/update`,data);
  }
  getEnquiryDetails(id): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<any>(`${environment.apiUrl}/application/apps/view`,{id});
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
