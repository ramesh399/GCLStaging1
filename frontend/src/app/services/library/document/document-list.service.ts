import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';


import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import { Document } from '@app/models/library/document';

interface SearchResult {
  documents: Document[];
  total: number;
}
interface State {
  page: number;
  pageSize: number;
  searchTerm: string;
  sortColumn: string;
  sortDirection: SortDirection;
  franchiseFilter:any;
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(documents: Document[], column: string, direction: string): Document[] {
  //console.log('234324');
  if (direction === '') {
    return documents;
  } else {
    return [...documents].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(document: Document, term: string, pipe: PipeTransform) {
  return document.note.toLowerCase().includes(term.toLowerCase());  
}



@Injectable({
  providedIn: 'root'
})
export class DocumentListService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _documents$ = new BehaviorSubject<Document[]>([]);
  private _total$ = new BehaviorSubject<number>(0);
  private category:number;

  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    franchiseFilter: '',
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
      this._documents$.next(result.documents);
      this._total$.next(result.total);
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get documents$() { return this._documents$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
  get loading$() { return this._loading$.asObservable(); }
  get page() { return this._state.page; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get pageSize() { return this._state.pageSize; }
  get franchiseFilter() { return this._state.franchiseFilter; }
  get searchTerm() { return this._state.searchTerm; }

  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }
  set franchiseFilter(franchiseFilter: any) { this._set({franchiseFilter}); }

  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }

  private _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, pageSize, page, franchiseFilter, searchTerm} = this._state;
	
    
    return this.http.post<SearchResult>(`${environment.apiUrl}/library/document/index`,{page,pageSize,searchTerm,sortColumn,franchiseFilter,sortDirection}).pipe(
        map(result => {
          return {documents:result.documents,total:result.total};
        })
    );

  }

  public customSearch(){
    this._documents$.next([]);
    this._total$.next(0);
    this._loading$.next(true);
    this._search$.next();
  }

  getDocTypeList(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/document/doctypelist`,{});
  }

  addData(documentData){
    return this.http.post<any>(`${environment.apiUrl}/library/document/create`, documentData);    
  }

  deleteDocumentData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/document/deletedocumentdata`, data);
  }

  getDocument():Observable<any>{    
    return this.http.get<any>(`${environment.apiUrl}/library/document/index`,this.httpOptions);    
  }

  downloadDocumentFile(data){
    return this.http.post(`${environment.apiUrl}/library/document/documentfile`,data,
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

  commonActionData(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/library/document/common-update`,data);
  } 
}
