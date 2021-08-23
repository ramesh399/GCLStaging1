import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { AudittypeService } from '@app/services/master/audittype/audittype.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-edit-audittype',
  templateUrl: '../add-audittype/add-audittype.component.html',
  styleUrls: ['./edit-audittype.component.scss']
})
export class EditAudittypeComponent implements OnInit {

  title = 'Edit Audit Type';
  btnLabel = 'Update';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  success:any;
  nameErrors = '';
  //audittype:Audittype;
  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private audittypeService: AudittypeService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	this.id = this.activatedRoute.snapshot.queryParams.id;
    
    this.form = this.fb.group({
      id:[''],
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255), Validators.pattern("^[a-zA-Z \'\-]+$")]],
      description:['',[this.errorSummary.noWhitespaceValidator]]	  
    });

    this.audittypeService.getAudittype(this.id).pipe(first())
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
		this.audittypeService.updateData(this.form.value).pipe(first()
      ).subscribe(res => {

          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
			setTimeout(()=>this.router.navigate(['/master/audittype/list']),this.errorSummary.redirectTime);
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
