import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { BrandService } from '@app/services/master/brand/brand.service';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-brand',
  templateUrl: './brand.component.html',
  styleUrls: ['./brand.component.scss']
})
export class BrandComponent implements OnInit {
  title = 'Brands';
  
  btnLabel = 'Save';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  nameErrors = '';
  numErrors = '';
  descriptionErrors = '';
  brandexpformData:FormData = new FormData();
  
  constructor(private router: Router,private fb:FormBuilder,
    private brandService: BrandService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	this.form = this.fb.group({
      name:['',[Validators.required,  Validators.maxLength(255)]],      
	  number:['',[Validators.required,  Validators.maxLength(50)]],
      version:['', [Validators.required,  Validators.maxLength(50)]]      
    });
  }

  get f() { return this.form.controls; }
    
  onSubmit(){
    //console.log(this.form.valid);
    //console.log(this.form.value);
    if (this.form.valid) {
      // this.auditStandardService.standards.push(this.form.value);
      // console.log(this.form.value);
      this.brandexpformData.append('formvalues',JSON.stringify({
        "brand": [this.form.value],
        "actiontype": "brand",
        "id": "67"
      }));

      this.brandService.addData(this.brandexpformData)
      .pipe(
        first()        
      )   
      .subscribe(res => {
        //console.log(res);
          if(res.status){			  
            this.success = {summary:res.message};
            this.buttonDisable = true;
            this.form.reset();  
			 setTimeout(()=>this.router.navigate(['master/list-brand']));
      //     }else if(res.status == 0){
      //       this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};			      
      //     }else{			      
      //       this.error = {summary:res};
      //     }
      //     this.loading = false;
          }
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
