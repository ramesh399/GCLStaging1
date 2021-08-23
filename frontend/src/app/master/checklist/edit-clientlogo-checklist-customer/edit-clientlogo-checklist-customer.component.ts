import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';

import { StandardService } from '@app/services/standard.service';
import { ProcessService } from '@app/services/master/process/process.service';
import {CustomerClientlogoChecklistService} from '@app/services/master/checklist/customer-clientlogo-checklist.service';


@Component({
  selector: 'app-edit-clientlogo-checklist-customer',
  templateUrl: '../add-clientlogo-checklist-customer/add-clientlogo-checklist-customer.component.html',
  styleUrls: ['./edit-clientlogo-checklist-customer.component.scss']
})
export class EditClientlogoChecklistCustomerComponent implements OnInit {

  id:number;
  title = 'Add Customer Client Logo Checklist Question';  
  btnLabel = 'Save';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  nameErrors = '';
  standardList:any; 
  processList:any;
  riskcategoryErrors = '';
  
  formData:FormData = new FormData();
  category:any;
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, private processService:ProcessService, private clientlogoService: CustomerClientlogoChecklistService,private errorSummary: ErrorSummaryService,public standardservice:StandardService) { }


  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
        
    this.form = this.fb.group({
        id:[''],	
        name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
        interpretation:['',[this.errorSummary.noWhitespaceValidator]],
        file_upload_required:[''],
        standard_id:['',[Validators.required]]
      });
    
      this.standardservice.getStandard().subscribe(res => {
        this.standardList = res['standards'];
      });

      this.clientlogoService.getCustomerClientlogoChecklist(this.id).pipe(first())
      .subscribe(res => {
        let clientinfochecklist = res.data;
        this.form.patchValue(clientinfochecklist);
        let standards = clientinfochecklist.standard.map(String);
        this.form.patchValue({'standard_id':standards});
      },
      error => {
          this.error = error;
          this.loading = false;
      });
  }

  getSelectedValue(type,val)
  {
    if(type=='standard')
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
            setTimeout(()=>this.router.navigate(['/master/clientlogo-checklist-customer/list']),this.errorSummary.redirectTime);		
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
