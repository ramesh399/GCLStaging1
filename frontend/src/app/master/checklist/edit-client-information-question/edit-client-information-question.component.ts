import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';

import {ClientInformationChecklist} from '@app/models/master/client-information-checklist';
import { StandardService } from '@app/services/standard.service';
import { ProcessService } from '@app/services/master/process/process.service';
import {ClientInformationChecklistService} from '@app/services/master/checklist/client-information-checklist.service';
import { AuditReviewerChecklistService } from '@app/services/master/checklist/audit-reviewer-checklist.service';

@Component({
  selector: 'app-edit-client-information-question',
  templateUrl: '../add-client-information-question/add-client-information-question.component.html',
  styleUrls: ['./edit-client-information-question.component.scss']
})
export class EditClientInformationQuestionComponent implements OnInit {

  title = 'Edit Client Information Question Checklist';
  btnLabel = 'Update';
  riskCategoryList:any;
  categoryList:any;
  category:number;   
  form : FormGroup;
  id:number;
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


  riskcategoryErrors='';
  
  formData:FormData = new FormData();

  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, private processService:ProcessService, private clientInformationChecklistService: ClientInformationChecklistService,private clientinfochecklistService: AuditReviewerChecklistService,private errorSummary: ErrorSummaryService,public standardservice:StandardService) { }

  getSelectedValue(type,val)
  {
    if(type=='riskcategory')
    {
      return this.riskCategoryList.find(x=> x.id==val).name;
    }
    else if(type=='standard')
    {
      return this.standardList.find(x=> x.id==val).name;
    }
    else if(type=='process')
    {
      //return this.processList.find(x=> x.id==val).name;
    }
  }

  ngOnInit() {
	
    this.id = this.activatedRoute.snapshot.queryParams.id;
        
    this.form = this.fb.group({
        id:[''],	
        name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
        interpretation:['',[this.errorSummary.noWhitespaceValidator]],
        riskcategory:['',[Validators.required]],
        standard_id:['',[Validators.required]],
        //process_id:['',[Validators.required]],
        client_information_id:['',[Validators.required]] 
      });
    
      this.standardservice.getStandard().subscribe(res => {
        this.standardList = res['standards'];
      });
      /*
      this.processService.getProcessList().pipe(first()).subscribe(res => {
        this.processList = res['processes'];      
      });	
      */
      this.clientInformationChecklistService.getClientInformations().subscribe(res => {
        this.categoryList = res['informations'];      
      });
  
     this.clientInformationChecklistService.getClientInformationChecklist(this.id).pipe(first())
      .subscribe(res => {
        let clientinfochecklist = res.data;
      this.riskCategoryList = clientinfochecklist['riskCategory']; 
      this.form.patchValue(clientinfochecklist);
      let riskcategory = clientinfochecklist.riskcategory.map(String);
      this.form.patchValue({'riskcategory':riskcategory});
      let standards = clientinfochecklist.standard.map(String);
      this.form.patchValue({'standard_id':standards});
      //let process = clientinfochecklist.process.map(String);
      //this.form.patchValue({'process_id':process});
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
      
      this.clientInformationChecklistService.updateData(this.form.value)
        .pipe(
          first()        
        ).subscribe(res => {
        
            if(res.status){
              this.success = {summary:res.message};
              this.buttonDisable = true;
              setTimeout(()=>this.router.navigate(['/master/client-information-question/list']),this.errorSummary.redirectTime);		
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
