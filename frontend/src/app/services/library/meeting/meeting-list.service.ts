import { Injectable,PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { ActivatedRoute ,Params } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import {DecimalPipe} from '@angular/common';
import {SortDirection} from '@app/helpers/sortable.directive';
import { Meeting } from '@app/models/library/meeting';

interface SearchResult {
  meetings: Meeting[];
  total: number;
}
interface State {
  page: number;
  pageSize: number;
  searchTerm: string;
  sortColumn: string;
  sortDirection: SortDirection;
  from_date:any;
  to_date:any;
}


function compare(v1, v2) {
  return v1 < v2 ? -1 : v1 > v2 ? 1 : 0;
}

function sort(meetings: Meeting[], column: string, direction: string): Meeting[] {
  //console.log('234324');
  if (direction === '') {
    return meetings;
  } else {
    return [...meetings].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(meeting: Meeting, term: string, pipe: PipeTransform) {
  return meeting.location.toLowerCase().includes(term.toLowerCase());  
}



@Injectable({
  providedIn: 'root'
})
export class MeetingListService {
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _meetings$ = new BehaviorSubject<Meeting[]>([]);
  private _total$ = new BehaviorSubject<number>(0);
  private category:number;

  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    sortDirection: '',
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
      this._meetings$.next(result.meetings);
      this._total$.next(result.total);
    });

    this._search$.next();
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type':  'application/json',
    })
  };
  
  get meetings$() { return this._meetings$.asObservable(); }
  get total$() { return this._total$.asObservable(); }
  get loading$() { return this._loading$.asObservable(); }
  get page() { return this._state.page; }
  get pageNo() { return (this._state.page - 1) * this._state.pageSize; }
  get pageSize() { return this._state.pageSize; }
  get searchTerm() { return this._state.searchTerm; }
  get from_date() { return this._state.from_date; }
  get to_date() { return this._state.to_date; }

  set page(page: number) { this._set({page}); }
  set pageSize(pageSize: number) { this._set({pageSize}); }
  set searchTerm(searchTerm: string) { this._set({searchTerm}); }
  set sortColumn(sortColumn: string) { this._set({sortColumn}); }
  set sortDirection(sortDirection: SortDirection) { this._set({sortDirection}); }
  set from_date(from_date: any) { this._set({from_date}); }
  set to_date(to_date: any) { this._set({to_date}); }

  private _set(patch: Partial<State>) {
    Object.assign(this._state, patch);
    this._search$.next();
  }

  private _search(): Observable<SearchResult> {

    const {sortColumn, sortDirection, pageSize, page, searchTerm, from_date, to_date} = this._state;
	
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
	
    return this.http.post<SearchResult>(`${environment.apiUrl}/library/meeting/index`,{page,pageSize,searchTerm,sortColumn,sortDirection,from_date:from_date_format,to_date:to_date_format}).pipe(
        map(result => {
          return {meetings:result.meetings,total:result.total};
        })
    );

  }

  public customSearch(){
    this._meetings$.next([]);
    this._total$.next(0);
    this._loading$.next(true);
    this._search$.next();
  }

  addData(meetingData){
    return this.http.post<any>(`${environment.apiUrl}/library/meeting/create`, meetingData);    
  }

  generatePDF(data){
    return this.http.post(`${environment.apiUrl}/library/meeting/generate-pdf`,data,
    {responseType:'arraybuffer'}
    );
  }	

  deleteMeetingData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/meeting/deletemeetingdata`, data);
  }

  getMeeting():Observable<any>{    
    return this.http.get<any>(`${environment.apiUrl}/library/meeting/index`,this.httpOptions);    
  }

  getMinuteData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/meeting/get-minutes`, data);
  }

  getMinutelogData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/meeting/get-minutelogs`, data);
  }

  deleteMinuteData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/meeting/delete-minute`, data);
  }

  deleteMinutelogData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/meeting/delete-minutelog`, data);
  }

  addMinuteData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/meeting/create-minute`, data);
  }

  addMinutelogData(data){
    return this.http.post<any>(`${environment.apiUrl}/library/meeting/addlogdata`, data);
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
    return this.http.post<any>(`${environment.apiUrl}/library/meeting/common-update`,data);
  } 
}
