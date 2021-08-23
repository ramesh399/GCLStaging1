import { DecimalPipe } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { StandardreductionService } from '@app/services/master/standardreduction/standardreduction.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { StandardService } from '@app/services/standard.service';

import { Router } from '@angular/router';
import { first } from 'rxjs/operators';
import { Standard } from '@app/services/standard';

@Component({
  selector: 'app-add-standardreduction',
  templateUrl: './add-standardreduction.component.html',
  styleUrls: ['./add-standardreduction.component.scss'],
  providers: [StandardreductionService,DecimalPipe]
})
export class AddStandardreductionComponent implements OnInit {

  title = 'Add Standard Reduction';
  standardList:Standard[];
  reductionStandardList:any=[];
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  success:any;
  submittedError = false;
  
  reductionEntries:any=[];
  reduction_standard_idErrors=''; 
  reduction_percentageErrors='';
  reduction_standard_id_existErrors='';
  standard_idErrors = '';
  
  formData:FormData = new FormData();
  
  constructor(private router: Router,private fb:FormBuilder,private standardservice: StandardService,private standardreductionService:StandardreductionService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
	
	this.standardservice.getStandard().subscribe(res => {
		this.standardList = res['standards'];
	});
	
	this.standardreductionService.getreductionStandardList().subscribe(res => {
		this.reductionStandardList = res['standards'];
    });
	
	this.form = this.fb.group({
      standard_id:['',[Validators.required]],      
      reduction_standard_id:[''],
	  reduction_percentage:['']
    });
  }

  get f() { return this.form.controls; }  
  
//   getReductionStandards(id:number)
//   {
// 	 this.reductionStandardList=[];
// 	 this.reductionEntries=[];
// 	 this.standardList.forEach((val)=>{
// 		if(val.id!=id)
// 		{
// 			this.reductionStandardList.push({id:val.id,name:val.name})
// 		}		
//      });	 	 
//   }
  
  checkStandardReduction()
  { 
	let reduction_standard_id = this.form.get('reduction_standard_id').value;
	let reduction_percentage = this.form.get('reduction_percentage').value.toString();
		
	if(reduction_standard_id!='' || reduction_percentage!='')
	{
		if(reduction_standard_id=='' || reduction_standard_id<=0){
			this.reduction_standard_idErrors = 'Please select the Reduction Standard';
			this.reductionStatus=false;
		}else{
			this.reduction_standard_idErrors = '';
		}	
		
		if(reduction_percentage==''){
			this.reduction_percentageErrors = 'Please enter the Reduction Percentage';			
		}else if(!reduction_percentage.match(/^\d*\.?\d{0,2}$/g)){
			this.reduction_percentageErrors = 'Invalid Reduction Percentage';	
        }else if(reduction_percentage>100){	
			this.reduction_percentageErrors = 'Reduction Percentage should be maximum 100';			
		}else{
			this.reduction_percentageErrors = '';
		}
		
	}else{
		this.reduction_standard_idErrors = '';
		this.reduction_percentageErrors = '';
	}	
		
  }
  
  removeReduction(reduction_standard_id:number) {
	this.editStatus=false;
    let index= this.reductionEntries.findIndex(s => s.reduction_standard_id ==  reduction_standard_id);
	if(index != -1)
	   this.reductionEntries.splice(index,1);
  }
  
  reductionStatus=true;
  reductionIndex=null;
  addReduction(){
	let reduction_standard_id:number = this.form.get('reduction_standard_id').value;
	//let reduction_standard_id = this.form.get('reduction_standard_id').value;
	let reduction_percentage = this.form.get('reduction_percentage').value.toString();
		
	this.reductionStatus=true;
	
	if(reduction_standard_id<=0){
        this.reduction_standard_idErrors = 'Please select the Reduction Standard';
		this.reductionStatus=false;
    }
	
	if(reduction_percentage.trim()==''){
        this.reduction_percentageErrors = 'Please enter the Reduction Percentage';
		this.reductionStatus=false;
    }else if(!reduction_percentage.match(/^\d*\.?\d{0,2}$/g)){
		this.reduction_percentageErrors = 'Invalid Reduction Percentage';
		this.reductionStatus=false;
	}else if(reduction_percentage>100){	
		this.reduction_percentageErrors = 'Reduction Percentage should be maximum 100';
		this.reductionStatus=false;
	}		
	
	if(!this.reductionStatus)
	{
		return false;
	}
	
	let reduction_standard_name = this.reductionStandardList.find(s => s.id ==  reduction_standard_id);
				
	let entry= this.reductionEntries.find(s => s.reduction_standard_id ==  reduction_standard_id);
    if(entry === undefined){
		let expobject:any=[];
		expobject["reduction_standard_id"] = reduction_standard_id;
		expobject["reduction_standard_name"] = reduction_standard_name.name;	
		expobject["reduction_percentage"] = reduction_percentage;
		this.reductionEntries.push(expobject);
	}else{
		entry.reduction_percentage = reduction_percentage;
	}	

	// this.reductionEntries.forEach((val)=>{
	// 	if(val.reduction_standard_id!=reduction_standard_id)
	// 	{
	// 		this.reductionStandardList.push({id:val.id,name:val.name})
	// 	}		
	// });	 	 
  	
		
    this.form.patchValue({
      reduction_standard_id: '',
	  reduction_percentage: ''
    });
	
	this.reduction_standard_idErrors = '';
	this.reduction_percentageErrors = '';
	this.editStatus=false;
  }
  editStatus=false;
  editReduction(reduction_standard_id:number){
	this.editStatus=true;
	let rtn= this.reductionEntries.find(s => s.reduction_standard_id ==  reduction_standard_id);
    this.form.patchValue({
      reduction_standard_id: rtn.reduction_standard_id,
      reduction_percentage:rtn.reduction_percentage
    });	
  }

  resetReduction()
  {
	this.editStatus=false;
    this.form.patchValue({
	  reduction_standard_id: '',
      reduction_percentage:''
    });
  }
   
  onSubmit(){
    
	this.checkStandardReduction();
	if(this.reduction_percentageErrors!='' || this.reduction_standard_idErrors !='')
	{
		return false;
	}
			
	if (this.form.valid) {
		
	  if(this.reductionEntries.length<=0)
	  {
		this.reduction_standard_idErrors = 'Please add atleast one Reduction Standard';
		return false;
	  }	
	  
		//   let index= this.reductionEntries.findIndex(s => s.reduction_standard_id ==  this.f.standard_id.value);
		//   if(index != -1)
		//   {
		// 	this.reduction_standard_id_existErrors = 'Standard should not be same as Reduction Standard';
		// 	return false;
		//   }
	
	  this.reduction_standard_idErrors = '';
	  this.reduction_percentageErrors = '';
	  this.reduction_standard_id_existErrors = '';
	      
      this.loading = true;
	  
	  let reductiondatas = [];
      this.reductionEntries.forEach((val)=>{
        reductiondatas.push({standard_id:val.reduction_standard_id,reduction_percentage:val.reduction_percentage})
      });

	  
	  	  
	  let formvalue = this.form.value;
	  formvalue.reduction = [];      	  
      formvalue.reduction = reductiondatas;
      	  
	  this.formData.append('formvalues',JSON.stringify(this.formData));
      
      this.standardreductionService.addData(this.form.value)
      .pipe(
        first()        
      )
      .subscribe(res => {
        //console.log(res);
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
		    setTimeout(()=>this.router.navigate(['/master/standardreduction/list']),this.errorSummary.redirectTime);
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