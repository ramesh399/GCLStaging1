import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ReductionStandardService } from '@app/services/master/reductionstandard/reductionstandard.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-add-reductionstandard',
  templateUrl: './add-reductionstandard.component.html',
  styleUrls: ['./add-reductionstandard.component.scss']
})
export class AddReductionstandardComponent implements OnInit {

  title = 'Add Reduction Standard';
  btnLabel = 'Save';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  typeErrors='';
  success:any;
  standardTypeList:any=[];
  RequiredFieldList:any=[];
  standard_type = '';
  nameErrors = '';
  codeErrors = '';
  short_codeErrors = '';
  
  constructor(private router: Router,private fb:FormBuilder,private standardService:ReductionStandardService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
    this.form = this.fb.group({
        type:['',[Validators.required]],
		name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255), Validators.pattern("^[a-zA-Z0-9 \'\-().,]+$")]],
		code:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(25), Validators.pattern("^[a-zA-Z0-9]+$")]],
		short_code:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(5), Validators.pattern("^[a-zA-Z0-9]+$")]],
		description:['',[this.errorSummary.noWhitespaceValidator]],
		required_fields:['',[Validators.required]]
      });

      this.standardService.getOptionList().pipe(first())
      .subscribe(res => { 
        this.standardTypeList = res.standardType;
        this.RequiredFieldList = res.RequiredFields;
      });
    }

  get f() { return this.form.controls; }

  getSelectedValue(val)
  {
    return this.RequiredFieldList[val];
  }
  
  onSubmit(){
    
    if(this.f.type.value=='')
    {
      this.typeErrors='Please select the Standard Type';		
    }else{
      this.typeErrors='';
    }	
      
      if (this.form.valid) 
    {      
      if(this.f.type.value=='')
      {
        return false;
      }
      this.typeErrors='';
      
        this.loading = true;
        
        this.standardService.addData(this.form.value)
        .pipe(
          first()        
        )
        .subscribe(res => {
          //console.log(res);
            if(res.status){
        this.success = {summary:res.message};
        this.buttonDisable=true;
        setTimeout(()=>this.router.navigate(['/master/reductionstandard/list']),this.errorSummary.redirectTime);
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
