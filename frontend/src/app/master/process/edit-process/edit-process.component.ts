import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ProcessService } from '@app/services/master/process/process.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-edit-process',
  templateUrl: '../add-process/add-process.component.html',
  styleUrls: ['./edit-process.component.scss']
})
export class EditProcessComponent implements OnInit {
  title = 'Edit Process';
  btnLabel = 'Update';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  success:any;
  nameErrors='';
  codeErrors='';
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private processService: ProcessService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	this.id = this.activatedRoute.snapshot.queryParams.id;
    
    this.form = this.fb.group({
      id:[''],
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z0-9 \'\-+%/&,().-]+$")]],
	  code:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(50),Validators.pattern("^[a-zA-Z0-9 \'\-+%/&,().-]+$")]],
      description:['',[this.errorSummary.noWhitespaceValidator]],
	  process_type:['']	  
    });

    this.processService.getProcess(this.id).pipe(first())
    .subscribe(res => {
      let audittype = res.data;
	  
      this.form.patchValue(audittype);
    },
    error => {
        this.error = error;
        this.loading = false;
    });
  }
  
  get f() { return this.form.controls; }

  onSubmit(){
    
    if (this.form.valid) {
		
        this.loading = true;  
		
		/*
		let formvalue = this.form.value; 
		let coreProcess=0;
		if(formvalue.core_process)
		{
			coreProcess=1;
		}
		formvalue.core_process=coreProcess;
		*/
	  
		this.processService.updateData(this.form.value).pipe(first()
		  ).subscribe(res => {

			  if(res.status){
				this.success = {summary:res.message};
				this.buttonDisable = true;
				setTimeout(()=>this.router.navigate(['/master/process/list']),this.errorSummary.redirectTime);
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
