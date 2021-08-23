import { Injectable, PipeTransform } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { BehaviorSubject, Observable, Subject, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {SortDirection} from '@app/helpers/sortable.directive';
import { AuditStandard} from "../../../models/master/AuditStandard"
import { switchMap, tap } from 'rxjs/operators';

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

function sort(standards: AuditStandard[], column: string, direction: string): AuditStandard[] {
  //console.log('234324');
  if (direction === '') {
    return standards;   
  } else {
    return [...standards].sort((a, b) => {
      const res = compare(a[column], b[column]);
      return direction === 'asc' ? res : -res;
    });
  }
}

function matches(standard: AuditStandard, term: string, pipe: PipeTransform) {

  return standard.standard_name.toLowerCase().includes(term.toLowerCase());
  /*return country.name.toLowerCase().includes(term.toLowerCase())
    || pipe.transform(country.area).includes(term)
    || pipe.transform(country.population).includes(term);
    */
}


@Injectable({
  providedIn: 'root'
})  
export class AuditStandardService {

  standards = [];
 
  private _loading$ = new BehaviorSubject<boolean>(true);
  private _search$ = new Subject<void>();
  private _standards$ = new BehaviorSubject<AuditStandard[]>([]);
  private _total$ = new BehaviorSubject<number>(0);

  private _state: State = {
    page: 1,
    pageSize: 10,
    searchTerm: '',
    sortColumn: '',
    sortDirection: ''
  };
  constructor(private http: HttpClient) { 
    // this._search$.pipe(
    //   tap(() => this._loading$.next(true)),
    //   //debounceTime(200),
    // //  switchMap(() => this._search()),
    //   //delay(200),
    //   tap(() => this._loading$.next(false))
    // ).subscribe(result => {
    //   this._standards$.next(result.data);
    //   this._total$.next(result.total);
    // });

    this._search$.next();
  } 

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/update-user`, data);
  } 

  getData() : Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-users`, { type : 100});
  } 
}
