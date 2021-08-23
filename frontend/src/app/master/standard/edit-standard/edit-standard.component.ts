import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { StandardService } from '@app/services/master/standard/standard.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-edit-standard',
  templateUrl: '../add-standard/add-standard.component.html',
  styleUrls: ['./edit-standard.component.scss']
})
export class EditStandardComponent implements OnInit {
  
  title = 'Edit Standard';
  btnLabel = 'Update';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  success:any;
  isSubmitted = false;
  typeErrors='';
  standard_type='';
  nameErrors = '';
  codeErrors = '';
  short_codeErrors = '';
  //audittype:Audittype;
  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private standardService: StandardService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	this.id = this.activatedRoute.snapshot.queryParams.id;
    
    this.form = this.fb.group({
      id:[''],
	    type:['',[Validators.required]],
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255), Validators.pattern("^[a-zA-Z \'\-().,]+$")]],
      code:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(25), Validators.pattern("^[a-zA-Z0-9]+$")]],
      short_code:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(5), Validators.pattern("^[a-zA-Z0-9]+$")]],
      version:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.pattern("^[0-9]+(.[0-9]{0,2})?$"),Validators.maxLength(10),Validators.min(1)]],
      license_number:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]],
      priority:['',[Validators.required,Validators.maxLength(5), Validators.pattern("^[0-9]+$")]],
      description:['',[this.errorSummary.noWhitespaceValidator]]
    });

    this.standardService.getStandard(this.id).pipe(first())
    .subscribe(res => {
      let audittype = res.data;
	  //console.log(audittype.type);
	  /*
	  this.form = this.fb.group({
       type:[audittype.type,[Validators.required]],       
      });
	  */
	
      this.form.patchValue({
        name:audittype.name,
        id:this.id,
        code:audittype.code,
        short_code:audittype.short_code,
        version:audittype.version,
        type:audittype.type,
        license_number:audittype.license_number,
        priority:audittype.priority,
        description:audittype.description
      });
      this.standard_type=audittype.type;
    },
    error => {
        this.error = error;
        this.loading = false;
    });
  }
  
  get f() { return this.form.controls; }  

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
			setTimeout(()=>this.router.navigate(['/master/standard/list']),this.errorSummary.redirectTime);
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
