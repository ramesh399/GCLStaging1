import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {User} from '@app/models/master/user';


@Injectable({
  providedIn: 'root'
})
export class FranchiseService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getFranchise(id:number): Observable<User[]>{
    return this.http.get<User[]>(`${environment.apiUrl}/master/franchise/view`,{});
  }  
}
