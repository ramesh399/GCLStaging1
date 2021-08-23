import { Component, OnInit, Renderer2 } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormGroup, FormControl, FormBuilder, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';
import * as $ from 'jquery';
import { JwtHelperService } from "@auth0/angular-jwt";
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';
import { AuthenticationService } from '@app/services';

import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ReCaptchaV3Service } from 'ng-recaptcha';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
	loginForm: FormGroup;
	loading = false;
	submitted = false;
	returnUrl: string;
	returnChangeUsernamePasswordUrl: string;
	error = '';
	recaptchaErrors = '';
	recaptchaResponseStatus=true;
    year$:any;
    
    /*
    resolved(captchaResponse: string) {
        //console.log('Resolved captcha with response: ${captchaResponse}');
		this.recaptchaResponseStatus=true;
		this.recaptchaErrors = '';
    }
    */


	constructor(
		private renderer: Renderer2,
        private formBuilder: FormBuilder,
        private route: ActivatedRoute,
        private router: Router,
        private authenticationService: AuthenticationService,
        private errorSummary: ErrorSummaryService,
        private enquiry:EnquiryDetailService,
        private recaptchaV3Service: ReCaptchaV3Service,
    ) { 
        // redirect to home if already logged in
        if (this.authenticationService.currentUserValue) { 
            //this.router.navigate(['/']);
            let user = this.authenticationService.getDecodeToken();
            let userType= user.decodedToken.user_type;

            if(userType == 1){
                this.returnUrl='/user/dashboard';
			}else if(userType == 2){
                this.returnUrl='/customer/dashboard';	
            }else{
                this.returnUrl='/enquiry/list';
            }
            this.router.navigate([this.returnUrl]);
        }
			
		this.renderer.removeClass(document.body, 'fixed-left');
		this.renderer.addClass(document.body, 'login');
    }
    
    public executeImportantAction(): any {
       // console.log('sdasd'); return false;
        this.recaptchaV3Service.execute('importantAction').pipe(first())
          .subscribe((token) => {
              this.onSubmit(token);
        });
    }

	ngOnDestroy()
	{
		this.renderer.addClass(document.body, 'fixed-left');
		this.renderer.removeClass(document.body, 'login');
	}
  
	ngOnInit() {
	
        this.year$ = this.enquiry.getYear(); /*.subscribe(res => {
        this.year = res;
    });*/
	
	//call validation function on focus out in angular 8
	//https://fiyazhasan.me/angular-forms-validation-updateon-blur/
	//https://ngninja.com/posts/angular2-form-validation
	
	/*
    this.loginForm = this.formBuilder.group({
        username: ['', [Validators.required, this.errorSummary.removeSpaces]],
        password: ['', [Validators.required, this.errorSummary.removeSpaces]]
    },{ updateOn: "blur" });
	*/
	
	this.loginForm = this.formBuilder.group({
        username: ['', [Validators.required,this.errorSummary.noWhitespaceValidator]],
        password: ['', [Validators.required,this.errorSummary.noWhitespaceValidator]]
	});
	
    // get return url from route parameters or default to '/'
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/enquiry/list';
	
	this.returnChangeUsernamePasswordUrl = this.route.snapshot.queryParams['returnUrl'] || '/change-username-password';
  }
  
  setHeight()
  {
      $('.login').css('min-height', $(window).innerHeight());
     
  }
  
  ngAfterViewInit() {
    setTimeout(() => {
		$(window).resize(()=>this.setHeight());
        $('.grecaptcha-badge').css('visibility', 'visible');
		this.setHeight()
	}, 500);	
  }

  // convenience getter for easy access to form fields
  get f() { return this.loginForm.controls; }

  onSubmit(token) {
    this.submitted = true;
	/*
	if(!this.recaptchaResponseStatus)
	{
		this.recaptchaErrors = 'Invalid Captcha';		
	}else{
		this.recaptchaErrors = '';
	}
    */
    // stop here if form is invalid
    //if (this.loginForm.invalid || !this.recaptchaResponseStatus) {
    if (this.loginForm.invalid) {
        return;
    }

    this.loading = true;
    
    this.authenticationService.login(this.f.username.value, this.f.password.value, token)
        .pipe(first())
        .subscribe(
            data => {
                //console.log(data);
                if(data.status && data.token){
                   $('.grecaptcha-badge').css('visibility', 'hidden');
                    const helper = new JwtHelperService();
                    let myRawToken = data.token;
    
                    const decodedToken = helper.decodeToken(myRawToken);
                    
                    if(decodedToken['firstlogin'])
                    {
                        this.router.navigate([this.returnChangeUsernamePasswordUrl]);
                    }else{
                        
                        if(decodedToken['user_type'] == 1 && decodedToken.resource_access !='5'){
                            this.returnUrl='/user/dashboard';
                        }else if(decodedToken['user_type'] == 2){
                            this.returnUrl='/customer/dashboard';
                        }
                        else{
                            this.returnUrl='/franchise/dashboard';
                        }
                        
                        if(this.returnUrl=='/change-username-password')
                        {
                            if(decodedToken['user_type'] == 2){
                                this.returnUrl='/customer/dashboard';
                            }else{
                                this.returnUrl='/enquiry/list';
                            }
                        }                    
                        this.router.navigate([this.returnUrl]);
                    }
                }else{
                    this.error = data.message;
                }
				               
            },
            error => {
                this.error = error;
                this.loading = false;
            });
  }

}
