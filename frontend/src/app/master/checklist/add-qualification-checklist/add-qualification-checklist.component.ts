import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { MaterialCompositionService } from '@app/services/master/materialcomposition/materialcomposition.service';
import { ProductService } from '@app/services/master/product/product.service';
import { BusinessSectorService } from '@app/services/master/business-sector/business-sector.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';

import {QualificationChecklist} from '@app/models/master/qualification-checklist';
import { StandardService } from '@app/services/master/standard/standard.service';
import { ProcessService } from '@app/services/master/process/process.service';
import { UserRoleService } from '@app/services/master/userrole/userrole.service';

import { QualificationChecklistService } from '@app/services/master/checklist/qualification-checklist.service';

import { UserRole } from '@app/models/master/userrole';
import { Process } from '@app/models/master/process';
import { Standard } from '@app/services/standard';
import { BusinessSector } from '@app/models/master/business-sector';
import { BusinessSectorGroup } from '@app/models/master/business-sector-group';

@Component({
  selector: 'app-add-qualification-checklist',
  templateUrl: './add-qualification-checklist.component.html',
  styleUrls: ['./add-qualification-checklist.component.scss']
})
export class AddQualificationChecklistComponent implements OnInit {

  title = 'Add Qualification Checklist';
  
  recurringPeriodList:QualificationChecklist[];
  
  standardList:Standard[];
  processList:Process[];
  roleList:UserRole[];
  bsectorList:BusinessSector[];
  bsectorgroupList:BusinessSectorGroup[];
   
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  product_idErrors = '';
  product_type_idErrors = '';
  nameErrors = '';
  
  standardErrors ='';
  processErrors='';
  roleErrors = '';
  recurring_periodErrors = '';
  
  formData:FormData = new FormData();
  
  constructor(private router: Router,private fb:FormBuilder,private qualificationChecklistService: QualificationChecklistService,private standardService: StandardService,private processService: ProcessService, private userRoleService:UserRoleService ,private BusinessSectorService: BusinessSectorService, private errorSummary: ErrorSummaryService) { }
  
  getSelectedValue(type,val)
  {
    if(type=='standard'){
      return this.standardList.find(x=> x.id==val).name;
    }else if(type=='role'){
      return this.roleList.find(x=> x.id==val).role_name;
    }else if(type=='process'){
      return this.processList.find(x=> x.id==val).name;
    }else if(type=='business_sector_id'){
      return this.bsectorList.find(x=> x.id==val).name;
    }else if(type=='business_sector_group_id'){
      return this.bsectorgroupList.find(x=> x.id==val).group_code;
    }
  }
  
  ngOnInit() {
	
	/*	
	this.productService.getProductList().subscribe(res => {
      this.productList = res['products'];      
    });	
	*/
	
	this.qualificationChecklistService.getQualificationChecklistRecurringPeriod().subscribe(res => {
      this.recurringPeriodList = res['recurringperiod'];      
    });
	
	this.standardService.getStandardList().subscribe(res => {
      this.standardList = res['standards'];
    });
	
    this.processService.getProcessList().subscribe(res => {
      this.processList = res['processes'];
    });
	
    this.userRoleService.getAllRoles().subscribe(res => {
      this.roleList = res['userroles'];
    });

    
	
	this.form = this.fb.group({
	  standard:['',[Validators.required]], 
      role:['',[Validators.required]], 
      //process:['',[Validators.required]], 
      business_sector_id:['',[Validators.required]],
      business_sector_group_id:['',[Validators.required]],
	  name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
      guidance:[''],
	  recurring_period:[''],
      file_upload_required:['']	 
      //product_id:['',[Validators.required]], 
	  //product_type_id:['',[Validators.required]],  
      //name:['',[Validators.required,Validators.maxLength(255),Validators.pattern("^[a-zA-Z0-9 \'\-+%/&,().-]+$")]]     
    });
	
  }  
	
 
  getBsectorList(value){
    let standardvals=this.form.controls.standard.value;
    //let processvals=this.form.controls.process.value;
    //if(standardvals.length>0 && processvals.length>0)
	if(standardvals.length>0)
    {
      this.BusinessSectorService.getBusinessSectors({standardvals}).subscribe(res => {
        this.bsectorList = res['bsectors'];
        this.form.patchValue({business_sector_id:''});
      });	
    }else{		
      this.bsectorList = [];
      this.form.patchValue({business_sector_id:''});		
    }
  }
  

  getBsectorgroupList(value){
    let standardvals=this.form.controls.standard.value;
    //let processvals=this.form.controls.process.value;
    let bsectorvals=value;
	//if(standardvals.length>0 && processvals.length>0 && bsectorvals.length>0)
    if(standardvals.length>0 && bsectorvals.length>0)
    {
      this.BusinessSectorService.getBusinessSectorGroups({standardvals,bsectorvals}).subscribe(res => {
        this.bsectorgroupList = res['bsectorgroups'];
        this.form.patchValue({business_sector_group_id:''});
      });	
    }else{		
      this.bsectorgroupList = [];
      this.form.patchValue({business_sector_group_id:''});		
    }
  }

  get f() { return this.form.controls; }
    
  onSubmit(){
    if (this.form.valid) {
      
      this.loading = true;
	  
	  let fileUploadRequired=0;
	  if(this.form.value.file_upload_required)
	  {
		fileUploadRequired=1;
	  }
	  this.form.value.file_upload_required=fileUploadRequired;
	  
	  if(this.form.value.recurring_period=='')
	  {
		this.form.value.recurring_period=0;
	  }  
	  
	  this.qualificationChecklistService.addData(this.form.value)
      .pipe(
        first()        
      ).subscribe(res => {
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
            setTimeout(()=>this.router.navigate(['/master/qualification-checklist/list']),this.errorSummary.redirectTime);            
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
