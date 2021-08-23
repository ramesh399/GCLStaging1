import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { UserService } from '@app/services/master/user/user.service';
import { SettingsService } from '@app/services/master/settings/settings.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { User } from '@app/models/master/user';
import { Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-settings',
  templateUrl: './settings.component.html',
  styleUrls: ['./settings.component.scss']
})
export class SettingsComponent implements OnInit {
  model: any = {headquarters:null,customer_id:null};
  franchiseList:User[];

  title = 'Settings';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  success:any;
  submittedError = false;
  from_emailErrors ='';
  to_emailErrors ='';
  reminder_days_user_qualificationErrors =''; 
  userQualificationReminderDaysList:any; 
  
  constructor(private userservice: UserService,private router: Router,private fb:FormBuilder,private settingsService: SettingsService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	
    this.form = this.fb.group({ 
      from_email:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.email,Validators.maxLength(255)]],
      to_email:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.email,Validators.maxLength(255)]],	   
      reminder_days_user_qualification:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      //headquarters:['',[Validators.required]],
    });

    this.settingsService.getSettings().pipe(first())
    .subscribe(res => {
      let audittype = res['data'];
	  
	  this.userQualificationReminderDaysList = audittype['reminder_days_user_qualification_array'];
	  
	  //console.log(this.userQualificationReminderDaysList.length)
	  
      this.form.patchValue(audittype);
	  
    },
    error => {
        this.error = error;
        this.loading = false;
    });

    this.userservice.getAllUser({type:3}).pipe(first())
    .subscribe(res => {
      this.franchiseList = res.users;
    },
    error => {
        this.error = {summary:error};
    });
  }
  
  get f() { return this.form.controls; }
  
  onSubmit(){
    
    if (this.form.valid) {
        this.loading = true;      
		this.settingsService.updateData(this.form.value).pipe(first()
      ).subscribe(res => {

          if(res.status){
            this.success = {summary:res.message};
			//this.buttonDisable = true;
			setTimeout(()=>this.router.navigate(['/master/settings']),this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
		  }else{
            this.error = {summary:res};
		  }
          this.loading = false;
      },
      error => {
          this.error = {summary:error};
          this.loading = false;
      });
      //console.log('sdfsdfdf');
    } else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form); 
      
    }
  }

}