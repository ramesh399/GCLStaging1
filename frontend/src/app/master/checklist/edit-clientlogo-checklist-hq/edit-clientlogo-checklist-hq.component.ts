import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';

import { StandardService } from '@app/services/standard.service';
import {HqClientlogoChecklistService} from '@app/services/master/checklist/hq-clientlogo-checklist.service';

@Component({
  selector: 'app-edit-clientlogo-checklist-hq',
  templateUrl: '../add-clientlogo-checklist-hq/add-clientlogo-checklist-hq.component.html',
  styleUrls: ['./edit-clientlogo-checklist-hq.component.scss']
})
export class EditClientlogoChecklistHqComponent implements OnInit {

  id:number;
  title = 'Edit HQ Client Logo Checklist Question';  
  btnLabel = 'Save';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  riskCategoryList:any;
  nameErrors = '';
  standardList:any; 
  processList:any;
  riskcategoryErrors = '';
  
  formData:FormData = new FormData();
  category:any;
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, private clientlogoService: HqClientlogoChecklistService,private errorSummary: ErrorSummaryService,public standardservice:StandardService) { }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
        
      this.form = this.fb.group({
        id:[''],
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

      this.clientlogoService.getHqClientlogoChecklist(this.id).pipe(first())
      .subscribe(res => {
        let clientinfochecklist = res.data;
        this.form.patchValue(clientinfochecklist);
        let standards = clientinfochecklist.standard.map(String);
        this.form.patchValue({'standard_id':standards});
        let findings = clientinfochecklist.finding_id.map(String);
        this.form.patchValue({'finding_id':findings});
      },
      error => {
          this.error = error;
          this.loading = false;
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
    
    this.clientlogoService.updateData(this.form.value)
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
