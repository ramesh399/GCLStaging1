import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { User } from '@app/models';

@Injectable({ providedIn: 'root' })
export class UserService {
    constructor(private http: HttpClient) { }

    getAll() {
        return this.http.get<User[]>(`${environment.apiUrl}/master/users/get-users`);
    }

    getById(id: number) {
        return this.http.get<User>(`${environment.apiUrl}/master/users/${id}`);
    }

    getMenuStatus(){
        return this.http.get<any>(`${environment.apiUrl}/master/users`);
    }

    getLeftMenuOptions(){
        return this.http.get<any>(`${environment.apiUrl}/master/user/left-menu-options`);
    }
}