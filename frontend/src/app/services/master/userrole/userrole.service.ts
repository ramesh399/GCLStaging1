import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { throwError,BehaviorSubject, Observable, of, Subject,pipe } from 'rxjs';
import { catchError, debounceTime, delay, switchMap, tap,map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { UserRole } from '@app/models/master/userrole';
import { TreeviewItem } from '../../../../lib';

@Injectable({
  providedIn: 'root'
})
export class UserRoleService {
  constructor(private http: HttpClient) { }
  // result
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  private _items$ = new BehaviorSubject<Array<any>>([]);
  items=[];
  getAllRoles() {
    return this.http.get<UserRole[]>(`${environment.apiUrl}/master/user-role/get-roles`);
  }
  
  /*
  getUserRole(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/index`,data);
  } 
  */

  addUserRole(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user-role/create`,data);
  } 

  updateUserRole(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user-role/update`,data);
  }

  getUserRoleTypes(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user-role/get-user-roles`,{});
  }

  

  getUserPrivileges(): Observable<any>{
    
    return this.http.get<any>(`${environment.apiUrl}/master/user-role/privileges`).pipe(
        map(res=>{

            res.forEach(element => {
                this.items.push(new TreeviewItem(element));
            });
            return this.items;
            
        })
    );
  }
  getUserPrivilegeDetails(id): Observable<any>{
    
    return this.http.post<any>(`${environment.apiUrl}/master/user-role/view`,{id}).pipe(
        map(res=>{

            res.privileges.forEach(element => {
                this.items.push(new TreeviewItem(element));
            });
            return {items:this.items,data:res.data};
            
        })
    );
  }
  
  getFranchiseBasedUserRole(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user-role/franchise-based-user-role`,data);
  }
  
  
}