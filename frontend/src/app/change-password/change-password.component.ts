import { Component, OnInit, Renderer2 } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { first } from 'rxjs/operators';
import * as $ from 'jquery';
import { User, Role } from '@app/models';

import { AuthenticationService } from '@app/services';
import { JwtHelperService } from "@auth0/angular-jwt";

import { UserService } from '@app/services/master/user/user.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import { MustMatch } from '@app/helpers/must-match.validator';

@Component({
  selector: 'app-change-password',
  templateUrl: './change-password.component.html',
  styleUrls: ['./change-password.component.scss'],
  providers: [UserService]
})
export class ChangePasswordComponent implements OnInit {

  form: FormGroup;
  loading = false;
  buttonDisable = false;
  submitted = false;
  returnUrl: string;
  success:any;
  error:any;
  currentUser: User;
  
  old_passwordErrors = ''; 
  new_passwordErrors = '';
  confirm_passwordErrors = '';
  old_pass_fld='old_password';
  
  pass_get = '';
  
  constructor(
    private renderer: Renderer2,
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
	private userService: UserService,
	private authenticationService: AuthenticationService,
	private errorSummary: ErrorSummaryService
	) {
		this.authenticationService.currentUser.subscribe(x => {
		  this.currentUser = x;		  
		});
	}
			
  ngOnDestroy()
  {
	
  }

  ngOnInit() {
	this.form = this.formBuilder.group({
       old_password: ['', [Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15)]],
	   new_password: ['', [Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15)]],
	   confirm_password: ['', [Validators.required,this.errorSummary.noWhitespaceValidator,Validators.minLength(6),Validators.maxLength(15)]]
    },{
		validator: MustMatch('new_password', 'confirm_password')
	});
	
	/*	
	this[this.old_pass_fld+'Errors']= 'Please select the Percentage';
	console.log(this.form.controls);
	console.log(this.form.controls.hasOwnProperty('first_name1'));
	this.form.controls.formErrors['first_name']='Please enter the valid first name';
	*/
  }
  
    // convenience getter for easy access to form fields
  get f() { return this.form.controls; }  
  	
  onSubmit() {
	
    this.submitted = true;
    // stop here if form is invalid
    if (this.form.valid) 
	{
		this.old_passwordErrors='';
		this.new_passwordErrors='';
		this.confirm_passwordErrors='';
		this.error = '';
		
		const helper = new JwtHelperService();
		let myRawToken = this.currentUser.token;

		const decodedToken = helper.decodeToken(myRawToken);
		this.form.value.uid = decodedToken['uid'];
		this.form.value.roleid = decodedToken['roleid'];
					
		this.loading = true;
		
		let formdataValue = this.authenticationService.encryptData(this.form.value);
		let jsonformdataValue:any = JSON.parse(formdataValue.toString());
		//console.log(jsonformdataValue);
		//return false;
		//this.form.value
		this.userService.changePasswordData(jsonformdataValue)
		  .pipe(
			first()        
		  )
		  .subscribe(res => {
			  if(res.status){
				this.form.reset();
				this.success = {summary:res.message};
				//this.router.navigate(['/enquiry/list']);
				//setTimeout(()=>this.cancelLogout(),1500);
			  }else if(res.status == 0){
				this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
			  }else{
				this.error = {summary:res};					
			  }
			  this.buttonDisable = false;
			  this.loading = false;
			 
		  },
		  error => {
			  this.error = {summary:error};
			  this.buttonDisable = false;
			  this.loading = false;
		  });
	}else{
      this.error = {summary:this.errorSummary.errorSummaryText};	
      this.errorSummary.validateAllFormFields(this.form); 	
	}
    
  }

}

