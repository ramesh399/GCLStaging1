import { Component, OnInit, Renderer2 } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';
import * as $ from 'jquery';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import { UserService } from '@app/services/master/user/user.service';
import { ReCaptchaV3Service } from 'ng-recaptcha';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';

@Component({
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.component.html',
  styleUrls: ['./forgot-password.component.scss'],
  providers: [UserService]
})
export class ForgotPasswordComponent implements OnInit {
  forgotpasswordForm: FormGroup;
  loading = false;
  buttonDisable = false;
  submitted = false;
  returnUrl: string;
  success:any;
  error:any;
  username = '';  
  recaptchaErrors = '';
  recaptchaResponseStatus=true;
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
    private userService: UserService,
    private errorSummary: ErrorSummaryService,
    private recaptchaV3Service: ReCaptchaV3Service,
    private enquiry:EnquiryDetailService,
  ) { 
    this.renderer.removeClass(document.body, 'fixed-left');
	  this.renderer.addClass(document.body, 'login');
  }
  
  ngOnDestroy()
  {
    this.renderer.addClass(document.body, 'fixed-left');
    this.renderer.removeClass(document.body, 'login');
  }
  year$:any;
  ngOnInit() {
	  this.forgotpasswordForm = this.formBuilder.group({
        //email: ['', Validators.required]
     username: ['', [Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6)]],
     token:['']
    });
    this.year$ = this.enquiry.getYear();/*.subscribe(res => {
      this.year = res;
  });*/
  }
  
  ngAfterViewInit() {
    setTimeout(() => {
		$(window).resize(function() {
			$('.login').css('min-height', $(window).innerHeight());
		});
    $('.grecaptcha-badge').css('visibility', 'visible');
		$('.login').css('min-height', $(window).innerHeight());
	}, 500);	
  }
  
  /*
  get username() {
    return this.forgotpasswordForm.get('username');
  }
  */

  // convenience getter for easy access to form fields
  get f() { return this.forgotpasswordForm.controls; }

  public executeImportantAction(): any {
    // console.log('sdasd'); return false;
     this.recaptchaV3Service.execute('importantAction')
        .pipe(first())
        .subscribe((token) => {
           this.onSubmit(token);
     });
  }

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
  //if (this.forgotpasswordForm.invalid || !this.recaptchaResponseStatus) {
    if (this.forgotpasswordForm.invalid) {
      return;
    }
		
  this.loading = true;
	this.forgotpasswordForm.patchValue({token: token});
	this.userService.forgotPasswordData(this.forgotpasswordForm.value)
      .pipe(
        first()        
      )
      .subscribe(res => {

          if(res.status){
            this.forgotpasswordForm.reset();
            this.success = {summary:res.message};
            this.buttonDisable = true;
            this.submitted= false;
			      setTimeout(()=>this.router.navigate(['/login']),this.errorSummary.redirectTime);			
          }else if(!res.status){ 
            this.forgotpasswordForm.reset();		  
			      this.error = {summary:res.message};
		      }else{
            this.error = {summary:res};
			//this.loading = false;
          }
        	this.loading = false;
         
      },
      error => {
          this.error = {summary:error};
          this.loading = false;
      });
	
	
    
  }

}
