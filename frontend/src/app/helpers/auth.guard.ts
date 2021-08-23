import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';

import { AuthenticationService } from '@app/services';
import { JwtHelperService } from "@auth0/angular-jwt";


@Injectable({ providedIn: 'root' })
export class AuthGuard implements CanActivate {
    constructor(
        private router: Router,
        private authenticationService: AuthenticationService
    ) { }

    canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
        const currentUser = this.authenticationService.currentUserValue;
        const helper = new JwtHelperService();
        let user = this.authenticationService.getDecodeToken();
        let isExpired = helper.isTokenExpired(user.rawToken);
        
        if (!isExpired) {

            // for Admin Access
            if(user.decodedToken.resource_access==1){
                return true;
            }
            if(route.data.userCanAccess !==undefined && route.data.userCanAccess.indexOf(user.decodedToken.user_type)!== -1){
                return true;
            }
            // for User & Franchise Access
            if (route.data.usertype && route.data.usertype.indexOf(user.decodedToken.user_type) === -1) {
                this.router.navigate(['/error404']);
                return false;
            }
            if(route.data.rulewithtype){
                if(user.decodedToken.user_type == route.data.rulewithtype){
                    let checksecroutedata:any = true;
                    let checkroutedata:any = true;
                    if(route.data.secrules && route.data.secrules.length>0){
                        const matchesfound = route.data.secrules.filter(element => user.decodedToken.rules.includes(element));
                        //console.log(matchesfound);
                        //console.log(user.decodedToken.rules);
                        if(matchesfound.length<=0){
                            checksecroutedata = false;
                            //this.router.navigate(['/error404']);
                            //return false;
                        }
                    }
                    // for Access with User Rule 
                    if (route.data.rules && user.decodedToken.rules && user.decodedToken.rules.indexOf(route.data.rules) === -1) {
                        //this.router.navigate(['/error404']);
                        //return false;
                        checkroutedata = false;
                    }
                     
                    if(!checksecroutedata && !checkroutedata){
                        this.router.navigate(['/error404']);
                        return false;
                    }else{
                        return true;
                    }
                }else{
                    return true;
                }
            }

             
            // for Access with User Rule 
            if (route.data.rules && user.decodedToken.rules && user.decodedToken.rules.indexOf(route.data.rules) === -1) {
                this.router.navigate(['/error404']);
                return false;
                 
            }
           
            return true;
        }
        
        this.authenticationService.logout();
        this.router.navigate(['/login'], { queryParams: { returnUrl: state.url } });
        return false;
        //return true;
        /*
        if (currentUser){
            const helper = new JwtHelperService();
            let myRawToken = currentUser.token;

            const decodedToken = helper.decodeToken(myRawToken);
            const expirationDate = helper.getTokenExpirationDate(myRawToken);
            const isExpired = helper.isTokenExpired(myRawToken);
            
            if (!isExpired) {
                // check if route is restricted by role
               // if (route.data.roles && route.data.roles.indexOf(currentUser.role) === -1) {
                     //role not authorised so redirect to home page
                 //  this.router.navigate(['/']);
                   //return false;
                //}
    
                // authorised so return true
                return true;
            }
        }
        */
        /*
        this.authenticationService.currentUser.subscribe(x => {
            if(x){
                let user = this.authenticationService.getDecodeToken();
                let isExpired = helper.isTokenExpired(user.rawToken);
                if (!isExpired) {
                    // check if route is restricted by role
                   // if (route.data.roles && route.data.roles.indexOf(currentUser.role) === -1) {
                         //role not authorised so redirect to home page
                     //  this.router.navigate(['/']);
                       //return false;
                    //}
        
                    // authorised so return true
                    return true;
                }
                //this.authenticationService.logUserDetails.subscribe(x => {
                //  console.log(x);
                //this.currentUser = user;
                //this.isExpired = helper.isTokenExpired(user.rawToken);
                //this.userType= user.decodedToken.user_type;
            }else{
                
                this.authenticationService.logout();
                this.router.navigate(['/login'], { queryParams: { returnUrl: state.url } });
                return false;
            }
            //this.isAuth();
        });
        */

        


        
    }
}