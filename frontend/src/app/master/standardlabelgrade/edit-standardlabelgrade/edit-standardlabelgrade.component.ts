import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { StandardlabelgradeService } from '@app/services/master/standardlabelgrade/standardlabelgrade.service';
import { StandardService } from '@app/services/standard.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';
import { Standard } from '@app/services/standard';

@Component({
  selector: 'app-edit-standardlabelgrade',
  templateUrl: '../add-standardlabelgrade/add-standardlabelgrade.component.html',
  styleUrls: ['./edit-standardlabelgrade.component.scss']
})
export class EditStandardlabelgradeComponent implements OnInit {

  title = 'Edit Standard Label Grade';
  btnLabel = 'Update';
  standardList:Standard[];
  id:number;
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;;
  submittedError = false;
  success:any;
  nameErrors = '';
  standard_idErrors = '';
  
  formData:FormData = new FormData();
  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private standardservice: StandardService,private standardlabelgradeService:StandardlabelgradeService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	
	this.id = this.activatedRoute.snapshot.queryParams.id;
	
	this.standardservice.getStandard().subscribe(res => {
		this.standardList = res['standards'];
    });
	
	this.form = this.fb.group({
	  id:[''],
      standard_id:['',[Validators.required]],      
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255), Validators.pattern("^[a-zA-Z0-9 \'\-]+$")]]     
    });
	
	this.standardlabelgradeService.getStandardlabelgrade(this.id).pipe(first())
    .subscribe(res => {
      let labelgrade = res.data; 
	  
      this.form.patchValue(labelgrade);
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
	  
	  this.standardlabelgradeService.updateData(this.form.value)
      .pipe(
        first()        
      ).subscribe(res => {
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
            setTimeout(()=>this.router.navigate(['/master/standardlabelgrade/list']),this.errorSummary.redirectTime);            
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