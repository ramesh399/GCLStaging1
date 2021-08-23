import { Component, OnInit, Renderer2 } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { first } from 'rxjs/operators';
import * as $ from 'jquery';

import { UserService } from '@app/services/master/user/user.service';

import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import { MustMatch } from '@app/helpers/must-match.validator';

@Component({
  selector: 'app-reset-password',
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.scss']
})
export class ResetPasswordComponent implements OnInit {
  
  resetpasswordForm: FormGroup;
  loading = false;
  buttonDisable = false;
  submitted = false;
  returnUrl: string;
  success:any;
  error:any;
  token:any;
  tokenStatus=false;
  isTokenVerifyRequest:any;
  
  constructor(
    private renderer: Renderer2,
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
	private userService: UserService,
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
	
	this.resetpasswordForm = this.formBuilder.group({
	   token:[''],
	   isTokenVerifyRequest:[''],
       new_password: ['', [Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15)]],
	   confirm_password: ['', [Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15)]]
    },{
		validator: MustMatch('new_password', 'confirm_password')
	});
	
	this.resetpasswordForm.value.token = this.token;
	this.resetpasswordForm.value.isTokenVerifyRequest = 1;
		
	this.userService.resetPasswordData(this.resetpasswordForm.value).pipe(first())
    .subscribe(res => {
		
	  this.tokenStatus=false;
      if(res.status){
        this.success = {summary:res.message};
		if(res.status===2)
		{
			this.tokenStatus=true;
		}
		
		if(res.status===1)
		{		
			setTimeout(()=>this.router.navigate(['/login']),this.errorSummary.redirectTime);
			this.tokenStatus=false;
		}	
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
  get f() { return this.resetpasswordForm.controls; }

  onSubmit() {
    this.submitted = true;

    // stop here if form is invalid
    if (this.resetpasswordForm.invalid) {
        return;
    }

    this.loading = true;
	
	this.resetpasswordForm.value.token = this.token;
	
	this.resetpasswordForm.value.isTokenVerifyRequest = 0;
	
	this.userService.resetPasswordData(this.resetpasswordForm.value)
      .pipe(
        first()        
      )
      .subscribe(res => {
        
          if(res.status){
            this.submitted = false;
            this.resetpasswordForm.reset();
			      this.success = {summary:res.message};
			      this.buttonDisable = true;
			      setTimeout(()=>this.router.navigate(['/login']),this.errorSummary.redirectTime);	
          }else if(!res.status){
			      this.resetpasswordForm.reset();
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
