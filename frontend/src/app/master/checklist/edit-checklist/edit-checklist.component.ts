import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ChecklistService } from '@app/services/master/checklist/checklist.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-edit-checklist',
  templateUrl: '../add-checklist/add-checklist.component.html',
  styleUrls: ['./edit-checklist.component.scss']
})
export class EditChecklistComponent implements OnInit {

  title = '';
  btnLabel = 'Update';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  category:number;
  success:any;
  nameErrors='';
    
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private checklistService: ChecklistService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.category = this.activatedRoute.snapshot.queryParams.category;
    
    if(this.category == 2){
      this.title = 'Edit Application Unit Review Checklist';
    }else{
      this.title = 'Edit Application Review Checklist';
    }

    this.form = this.fb.group({
      id:[''],
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      guidance:['',[this.errorSummary.noWhitespaceValidator]],	  
    });

    this.checklistService.getChecklist(this.id).pipe(first())
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
		this.checklistService.updateData(this.form.value).pipe(first()
      ).subscribe(res => {

          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
			setTimeout(()=>this.router.navigateByUrl('/master/checklist/list?category='+this.category),this.errorSummary.redirectTime);
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
