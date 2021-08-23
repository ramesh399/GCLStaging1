import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';

import { StandardService } from '@app/services/standard.service';
import {HqClientlogoChecklistService} from '@app/services/master/checklist/hq-clientlogo-checklist.service';

@Component({
  selector: 'app-add-clientlogo-checklist-hq',
  templateUrl: './add-clientlogo-checklist-hq.component.html',
  styleUrls: ['./add-clientlogo-checklist-hq.component.scss']
})
export class AddClientlogoChecklistHqComponent implements OnInit {

  title = 'Add HQ Client Logo Checklist Question';  
  btnLabel = 'Save';
  riskCategoryList:any;
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  product_idErrors = '';
  product_type_idErrors = '';
  nameErrors = '';
  standardList:any; 
  processList:any;
  riskcategoryErrors = '';
  
  formData:FormData = new FormData();
  category:any;
  constructor(private router: Router,private fb:FormBuilder, private clientlogoService: HqClientlogoChecklistService,private errorSummary: ErrorSummaryService,public standardservice:StandardService) { }

  ngOnInit() {

    this.form = this.fb.group({
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
      interpretation:['',[this.errorSummary.noWhitespaceValidator]],
      finding_id:['',[Validators.required]],
      standard_id:['',[Validators.required]],
    });	

    this.standardservice.getStandard().subscribe(res => {
      this.standardList = res['standards'];
    });
    
    this.clientlogoService.getHqClientlogoChecklistRiskCategory().subscribe(res => {
      this.riskCategoryList = res['finding_id'];      
    });
  }

  getSelectedValue(type,val)
  {
    if(type=='riskcategory')
    {
      return this.riskCategoryList[val];
    }
    else if(type=='standard')
    {
      return this.standardList.find(x=> x.id==val).name;
    }
  }

  get f() { return this.form.controls; }

  onSubmit(){
    if (this.form.valid) {
      
      this.loading = true;
	  
	  this.clientlogoService.addData(this.form.value)
      .pipe(
        first()        
      ).subscribe(res => {
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
            setTimeout(()=>this.router.navigate(['/master/clientlogo-checklist-hq/list']),this.errorSummary.redirectTime);			
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
