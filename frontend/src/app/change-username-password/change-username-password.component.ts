import { Component, OnInit, Renderer2 } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { first } from 'rxjs/operators';
import * as $ from 'jquery';
import { User, Role } from '@app/models';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import { AuthenticationService } from '@app/services';
import { JwtHelperService } from "@auth0/angular-jwt";

import { UserService } from '@app/services/master/user/user.service';

import { MustMatch } from '@app/helpers/must-match.validator';

@Component({
  selector: 'app-change-username-password',
  templateUrl: './change-username-password.component.html',
  styleUrls: ['./change-username-password.component.scss'],
  providers: [UserService]
})
export class ChangeUsernamePasswordComponent implements OnInit {

  form: FormGroup;
  loading = false;
  submitted = false;
  returnUrl: string;
  success:any;
  error:any;
  //currentUser: User;
  token:any;
  tokenStatus=false;
  isTokenVerifyRequest:any;
  
  constructor(
    private renderer: Renderer2,
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private userService: UserService,
    private authenticationService: AuthenticationService,
    private errorSummary: ErrorSummaryService
	) {
		this.renderer.removeClass(document.body, 'fixed-left');
		this.renderer.addClass(document.body, 'login');		
	}
			
  ngOnDestroy()
  {
	this.renderer.addClass(document.body, 'fixed-left');
	this.renderer.removeClass(document.body, 'login');
  }

  ngOnInit() {
	
    this.token = this.route.snapshot.queryParams.token;
	
    this.form = this.formBuilder.group({
      token:[''],
      isTokenVerifyRequest:[''],	
        new_username: ['', [Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(40),Validators.pattern("^[a-zA-Z0-9]*$")]],
      new_password: ['', [Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15)]],
      confirm_password: ['', [Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15)]]
      },{
      validator: MustMatch('new_password', 'confirm_password')
    });
	
    this.form.value.token = this.token;
    this.form.value.isTokenVerifyRequest = 1;
    
    let formdataValue = this.authenticationService.encryptData(this.form.value);
    let jsonformdataValue:any = JSON.parse(formdataValue.toString());

	  this.userService.changeUsernamePasswordData(jsonformdataValue).pipe(first())
    .subscribe(res => {
		
      if(res.status){		
        this.tokenStatus=true;		
      }else if(!res.status){
        this.error = {summary:res.message};		
        this.tokenStatus=false;
      }else{		
        this.error = {summary:res};	
        this.tokenStatus=false;	
      }	   
    },
    error => {
        this.error = {summary:error};
        this.tokenStatus=false;
    });
  }
  
  ngAfterViewInit() {
    setTimeout(() => {
		$(window).resize(function() {
			$('.login').css('min-height', $(window).innerHeight());
		});
	
		$('.login').css('min-height', $(window).innerHeight());
	}, 500);	
  }

  // convenience getter for easy access to form fields
  get f() { return this.form.controls; }
  /*
	cancelLogout(){
		this.authenticationService.logout();
		this.router.navigate(['/login']);
	}
	*/
  onSubmit() {
    this.submitted = true;

    // stop here if form is invalid
    if (this.form.invalid) {
        return;
    }
	
	//const helper = new JwtHelperService();
	//let myRawToken = this.currentUser.token;

	//const decodedToken = helper.decodeToken(myRawToken);
	//this.form.value.uid = decodedToken['uid'];
	
	this.form.value.token = this.token;	
	
    this.loading = true;
	
	this.form.value.isTokenVerifyRequest = 0;
  
  let formdataValue = this.authenticationService.encryptData(this.form.value);
  let jsonformdataValue:any = JSON.parse(formdataValue.toString());
    
	this.userService.changeUsernamePasswordData(jsonformdataValue)
      .pipe(
        first()        
      )
      .subscribe(res => {
		  if(res.status){
            this.form.reset();
			this.success = {summary:res.message};
			this.submitted= false;            
			setTimeout(()=>this.router.navigate(['/login']),this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = {summary:res.message};
			//this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
		  }else{
			this.error = {summary:res};			
          }
        	this.loading = false;
         
      },
      error => {
          this.error = {summary:error};
          this.loading = false;
      });
	
	
    
  }

}
