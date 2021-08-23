import { Component,OnInit } from '@angular/core';
import { Router } from '@angular/router';

import { AuthenticationService } from '@app/services';
import { User, Role } from '@app/models';
import { JwtHelperService } from "@auth0/angular-jwt";

import * as $ from 'jquery';


@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: []

})
export class AppComponent  implements OnInit{
  
  currentUser: User;
  routerUrl:string;
  isExpired:any;
  userType:any;

  constructor(public router: Router,public authenticationService: AuthenticationService) 
  {
    
    //this.authenticationService.currentUser.subscribe(x => {
      //this.currentUser = x;
      //this.isAuth();
    //});

    
    let helper = new JwtHelperService;

    this.authenticationService.currentUser.subscribe(x => {
     // this.authenticationService.checkOnlineonload();
      if(x){
        let user = this.authenticationService.getDecodeToken();
        //this.authenticationService.logUserDetails.subscribe(x => {
        //  console.log(x);
        this.currentUser = user;
        this.isExpired = helper.isTokenExpired(user.rawToken);
        this.userType= user.decodedToken.user_type;
      }else{
        this.isExpired = 1;
        this.currentUser = x;
        this.isAuth;
      }
      //this.isAuth();
    });
      //this.isAuth();
   // });
  }
    
  ngOnInit(){
    
    window.addEventListener('storage', (event) => {
      //console.log('111');
      if (event.storageArea == localStorage) {
          let token = localStorage.getItem('currentUser');
          if(token == undefined) { 
            // Perform logout
            //Navigate to login/home
            this.logout();
          }
      }
  });
   // if(this.router.url=='/' && this.currentUser){
    //  if( this.userType ==2){
       // this.router.navigate(['/enquiry/list']);
    //  }else{
       // this.router.navigate(['/application/list']);
    //  }
    //}
	 // console.log('-----'+this.currentUser);
    //t//his.routerUrl=this.router.url;
    //console.log('sdf');
  }
	
	
  
  get isFirstUser() {
	 // const helper = new JwtHelperService();
	  //let myRawToken = this.currentUser.token;

	 // const decodedToken = helper.decodeToken(myRawToken);
    
		
    return this.currentUser.decodedToken.firstlogin;
   // return 0;    
  }
	
  
  get isAdmin() {
    return false;
    //return this.currentUser && this.currentUser.role === Role.Admin;
  }
  
  
  get isAuth() {
     if(this.currentUser && this.currentUser.rawToken)
	 {
        if (!this.isExpired) {
          return true;
        }
        this.logout();
     }
     return false;         
  }

  logout() {
    this.authenticationService.logout();
    this.router.navigate(['/login']);
  }

}
