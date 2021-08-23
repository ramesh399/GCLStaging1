import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ReductionStandardService } from '@app/services/master/reductionstandard/reductionstandard.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-edit-reductionstandard',
  templateUrl: '../add-reductionstandard/add-reductionstandard.component.html',
  styleUrls: ['./edit-reductionstandard.component.scss']
})
export class EditReductionstandardComponent implements OnInit {

  title = 'Edit Reduction Standard';
  btnLabel = 'Update';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  standardTypeList:any=[];
  RequiredFieldList:any=[];
  required_fieldsEntries:any=[];
  id:number;
  success:any;
  isSubmitted = false;
  typeErrors='';
  standard_type='';
  nameErrors = '';
  codeErrors = '';
  short_codeErrors = '';

  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private standardService: ReductionStandardService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
    
    this.form = this.fb.group({
      id:[''],
	  type:['',[Validators.required]],
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255), Validators.pattern("^[a-zA-Z0-9 \'\-().,]+$")]],
	  code:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(25), Validators.pattern("^[a-zA-Z0-9]+$")]],
	  short_code:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(5), Validators.pattern("^[a-zA-Z0-9]+$")]],
    description:['',[this.errorSummary.noWhitespaceValidator]],
    required_fields:['',[]]
    });

    this.standardService.getStandard(this.id).pipe(first())
    .subscribe(res => {
      let audittype = res.data;
      this.required_fieldsEntries = res.required_fields;
      
	
      this.form.patchValue({
        name:audittype.name,
        id:this.id,
        code:audittype.code,
        short_code:audittype.short_code,
        type:audittype.type,
        required_fields:this.required_fieldsEntries,
        description:audittype.description
      });
	    this.standard_type=audittype.type;
    },
    error => {
        this.error = error;
        this.loading = false;
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
    }		
 
   if (this.form.valid) {
   if(this.f.type.value=='')
   {
     return false;
   }
   this.typeErrors='';
   
       this.loading = true;      
   this.standardService.updateData(this.form.value).pipe(first()
     ).subscribe(res => {

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
   } else {
     this.error = {summary:this.errorSummary.errorSummaryText};
     this.errorSummary.validateAllFormFields(this.form); 
     
   }
 }

}
