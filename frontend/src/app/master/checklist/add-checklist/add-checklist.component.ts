import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ChecklistService } from '@app/services/master/checklist/checklist.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-add-checklist',
  templateUrl: './add-checklist.component.html',
  styleUrls: ['./add-checklist.component.scss']
})
export class AddChecklistComponent implements OnInit {

  title = '';
  btnLabel = 'Save';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  category:number;
  submittedError = false;
  success:any;
  nameErrors='';
  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private checklistService:ChecklistService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	  this.category = this.activatedRoute.snapshot.queryParams.category;
	
	  this.form = this.fb.group({
      category:[''],
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
      guidance:['',[this.errorSummary.noWhitespaceValidator]],      
    });
  
    if(this.category == 2){
      this.title = 'Add Application Unit Review Checklist';
    }else{
      this.title = 'Add Application Review Checklist';
    }
	  this.form.patchValue({category:this.category});
  }

  get f() { return this.form.controls; }
    
  onSubmit(){
    if (this.form.valid) {
      
      //console.log(this.category);
      this.loading = true;
      
      this.checklistService.addData(this.form.value)
      .pipe(
        first()        
      )
      .subscribe(res => {
          if(res.status){
			this.success = {summary:res.message};
			this.buttonDisable = true;
			setTimeout(() => {
                this.router.navigateByUrl('/master/checklist/list?category='+this.category);
            }, this.errorSummary.redirectTime);
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
    } else {
	  this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form);       
    }
  }

}
