import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable,interval,Subject } from 'rxjs';
import { map,debounceTime,exhaustMap,delay,tap,first,takeUntil,concatMapTo } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { User } from '@app/models';
import { JwtHelperService } from "@auth0/angular-jwt";
import { IndexedDBService } from '@app/indexeddbservices/indexed-db.service';
import * as CryptoJS from 'crypto-js';

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
    private currentUserSubject: BehaviorSubject<User>;
    public currentUser: Observable<User>;

    private currentUserDetails: BehaviorSubject<any>;
    public logUserDetails: Observable<any>;

    
    private userOnlineStatusSubject: BehaviorSubject<any>;
    public userOnlineStatus: Observable<any>;
    private JsonFormatter = {
        stringify: function(cipherParams) {
          // create json object with ciphertext
          var jsonObj:any = { ct: cipherParams.ciphertext.toString(CryptoJS.enc.Base64) };
      ​
          // optionally add iv or salt
          if (cipherParams.iv) {
            jsonObj.iv = cipherParams.iv.toString();
          }
      ​
          if (cipherParams.salt) {
            jsonObj.s = cipherParams.salt.toString();
          }
      ​
          // stringify json object
          return JSON.stringify(jsonObj);
        },
        parse: function(jsonStr) {
          // parse json string
          var jsonObj = JSON.parse(jsonStr);
      ​
          // extract ciphertext from json object, and create cipher params object
          var cipherParams = CryptoJS.lib.CipherParams.create({
            ciphertext: CryptoJS.enc.Base64.parse(jsonObj.ct)
          });
      ​
          // optionally extract iv or salt
      ​
          if (jsonObj.iv) {
            cipherParams.iv = CryptoJS.enc.Hex.parse(jsonObj.iv);
          }
      ​
          if (jsonObj.s) {
            cipherParams.salt = CryptoJS.enc.Hex.parse(jsonObj.s);
          }
      ​
          return cipherParams;
        }
      };
    
    
    decodedToken:any;
    expirationDate:any;
    rawToken:string;
    isExpired:boolean;
    intervalID:any;
    authenticateIsOnline:number=1;
    stopPlay: Subject<any> = new Subject();
    constructor(private http: HttpClient,  private indexedDB:IndexedDBService) {
        this.currentUserSubject = new BehaviorSubject<User>(JSON.parse(localStorage.getItem('currentUser')));
        this.currentUser = this.currentUserSubject.asObservable();

        this.userOnlineStatusSubject = new BehaviorSubject<any>('');
        this.userOnlineStatus = this.userOnlineStatusSubject.asObservable();
        //this.checkOnlineonload();
        //this.intervalID = window.setInterval(this.checkOnlineStatus.bind(this), 4500);
        //this.currentUserDetails = new BehaviorSubject<any>({});
        //this.logUserDetails = this.currentUserDetails.asObservable();
    }

    checkOnlines(){
        this.intervalID = interval(4000);
        
        //debounceTime(3000)
       /* this.intervalID.pipe(
            exhaustMap(doRequest),
            delay(6000),
            map(xx=> 'xx'+xx)
         ).subscribe(x=>console.log(x));
         */
        this.intervalID.pipe(
            //debounceTime(2000),
            takeUntil(this.stopPlay),
            delay(3000),
            tap(xx=>{
                this.doRequest().pipe(first()).subscribe(res => {
                    
                    if(this.authenticateIsOnline == 0){
                        this.authenticateIsOnline = 1;
                        this.userOnlineStatusSubject.next(1);
                    }
                },
                err=>{
                    console.log(err);
                    //this.authenticateIsOnline = 0;
                    if(this.authenticateIsOnline == 1){
                        this.authenticateIsOnline = 0;
                        this.userOnlineStatusSubject.next(0);
                    }
                });
            })
         ).subscribe(x=>{});
    }
    doRequest(){
        return this.http.post<any>(`${environment.apiUrl}/site/getrandomdata`,{});
    }
    deleteInterval(){
        //this.intervalID.unsubscribe();
        this.stopPlay.next(1);
    }
    ngOnDestroy() {
        //console.log('ngOnDestroy: cleaning up...');
        //clearInterval(this.intervalID);
    }
    checkOnlineonload(){
        
        this.doRequest().pipe(first()).subscribe(res => {
            //if(this.authenticateIsOnline == 0){
                this.authenticateIsOnline = 1;
                this.userOnlineStatusSubject.next(1);
            //}
        },
        err=>{
            //console.log('auther');
            //if(this.authenticateIsOnline == 1){
                this.authenticateIsOnline = 0;
                this.userOnlineStatusSubject.next(0);
            //}
        });
            
    }
    //debounceTime(500)
    /*
    async checkOnlineStatus(){
        let url = `http://yii72.aescorp.in/yii19102301_gcl/ver1/web/site/getyear`;
        let response:any = await fetch(url);
        console.log(response.ok);
        if (response.ok) { // if HTTP-status is 200-299
            // get the response body (the method explained below)
            //let json = await response.json();
            this.authenticateIsOnline = 1;
            console.log('Auth:'+this.authenticateIsOnline);
            
        } else {
            //console.log("HTTP-Error: " + response.status);
            this.authenticateIsOnline = 0;
        }
    }
    */
    public get currentUserValue(): User {
        return this.currentUserSubject.value;
    }



    getDecodeToken():any{
        const helper = new JwtHelperService();
        let user = this.currentUserSubject.value;
            
   
        if(user)
        {
            this.decodedToken = helper.decodeToken(user.token);
            this.expirationDate = helper.getTokenExpirationDate(user.token);
            this.isExpired = helper.isTokenExpired(user.token);
            return {decodedToken:this.decodedToken,expirationDate: this.expirationDate,rawToken:user.token};
        }
        return {};
    }
    

    
   /* checkRole(rolename,user_type=[]){

    
        if(this.decodedToken.resource_access == 1){
          return true;
        }
        if(user_type.includes(this.decodedToken.user_type)){
          return true;
        }
        if(this.decodedToken.rules.includes(rolename)){
          return true;
        }
        return false;
    }
*/
    login(username: string, password: string, token:string){
        /*
        let usertoken = JSON.stringify({"ct":"vTmlHoH1naEnqBTdC2sOyOuNolLqL1ScPgAUWCSWLz+kof2erov9sR9LXy+2w7K24AVqXJXHJbt3+pgHvpQfDdxyylSFKL1OQmHsT0Xy2txZWqCqsSMeAG0Qk+kF+9EgTdyjjMAhLu0jPrHMMETjlwuaFffYTtGmHoZFgGV6V48r4+wLpvNlPo7ZuQhTrTTtXTb2r2/tc2Q8b1LbABS24l/byC/vnRUV0acbRGjX3NInWOJAkRrH1C6p9qG58gR8QgMi85a+pcVHDzj6klTJVggeL6PjaSzAKXUzQp55f4EqpRANun27ITMMUpYzzJMCa+KJ5eEpz+VMk5ZVk/7ZGdjWSxUYPznMEtve6c7MGDrLRP96ZurhVbzzgiwXgfZB/ANPC6agsVAcqmCkEj90t7E+FjIfTKCwttR/4F3Twh5GZL+0EWqr15rX/Zblv0rRttdvU1m3kZHL+r5bNBqgThAb6Erwwc3k1zZzaLWockwH3wEY+svjhS5CfyzOl4huPki24coedML9EnUuTXEf8Y4fBhi1f2ZndyX3QjM3So3OvMlCVEQ6S8WBKVqlU1Mv5Q+71ulzLUt692fU6ZRy33MGNfVg9/Bv+dIpv04YZgGC1Ru51HmbVKU8ZMD7N65IBRb3dWXyjo1y6JLNcS9JSedXam0YjyRBjKLpxwoF1oRJtnKjO2LTKQx60gOhHPQeKCBpBp9LTcAEpJL+gLX0QVBsCAA/F8Dv7YCJcN/En3k8Q4CaBN5gMrkyi4bU6sTjuECnxw62IT8e5HksyGx5xA==","iv":"57aa1f0d0e3470d910150022bb4b8822","s":"4714637d26056db7"});

                console.log(usertoken);
                let decryptedtoken = this.decryptData(usertoken);
                
                console.log(decryptedtoken);
                console.log(decryptedtoken.toString(CryptoJS.enc.Utf8));
                return false;
        */
        //return this.http.post<any>(`${environment.apiUrl}/testapi/authenticate`, { username, password })
        let logindata = this.encryptData({ username, password,token });
        
        let logindetails:any = JSON.parse(logindata.toString());
        
        //console.log(logindetails.iv);
        //let logdata =  {username, password, token};
        //console.log(logdata);
        return this.http.post<any>(`${environment.apiUrl}/site/login`, logindetails)
        
            .pipe(map(user => {
                // login successful if there's a jwt token in the response
                if (user && user.token) {
                    
                    this.indexedDB.connectToDb().then(()=>{
                        this.indexedDB.addLocalStorage(user.token);
                    })
                    //console.log(user);
                    // store user details and jwt token in local storage to keep user logged in between page refreshes
                    localStorage.setItem('currentUser', JSON.stringify(user));
                    this.currentUserSubject.next(user);
                    

                }else{
                    throw new Error(user.password);
                }

                return user;
            }));
    }
	
    logout() {
        // remove user from local storage to log user out
        localStorage.removeItem('currentUser');
        this.currentUserSubject.next(null);
    }

    encryptData(message:any){
        let encrypted = CryptoJS.AES.encrypt(JSON.stringify(message), environment.EncryptDecryptKey, {
            format: this.JsonFormatter
        });
        return encrypted;
    }
    decryptData(encrypted:any=''){
        let decrypted = CryptoJS.AES.decrypt(encrypted, environment.EncryptDecryptKey, {
            format: this.JsonFormatter
          });
        return decrypted;
    }
}