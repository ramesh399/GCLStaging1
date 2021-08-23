import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { LicensefeeService } from '@app/services/master/licensefee/licensefee.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';

import { StandardService } from '@app/services/standard.service';
import { Standard } from '@app/services/standard';

@Component({
  selector: 'app-licensefee',
  templateUrl: './licensefee.component.html',
  styleUrls: ['./licensefee.component.scss']
})
export class LicensefeeComponent implements OnInit {

  standardList:Standard[];
  licenseFeeStandardList:any=[];
  form : FormGroup;
  loading = false;
  error:any;
  id:number;
  success:any;
  //audittype:Audittype;
  
  licenseFeesEntries:any=[];
  standard_idErrors=''; 
  license_feeErrors='';
  subsequent_license_feeErrors='';
	licenseFeeIncompleteErrors='';
	
	userType:number;
  userdetails:any;
  userdecoded:any;
  
  formData:FormData = new FormData();
  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private standardservice: StandardService,private licensefeeService: LicensefeeService,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {
	this.standardservice.getStandard().subscribe(res => {
		this.standardList = res['standards'];
    });
    
    this.form = this.fb.group({
      standard_id:[''],
	  license_fee:['',[Validators.pattern(/^\d*\.?\d{0,2}$/g)]],
	  subsequent_license_fee:['',[Validators.pattern(/^\d*\.?\d{0,2}$/g)]]		  
    });
	
	this.licensefeeService.getLicensefee(this.id).pipe(first())
    .subscribe(res => {
      this.licenseFeesEntries = res.licensefees;      
    },
    error => {
        this.error = error;
        this.loading = false;
		});
		
		this.authservice.currentUser.subscribe(x => {
      if(x){
        
         
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        
      }else{
        this.userdecoded=null;
      }
		});
 }
 
 getLicenseFeeStandards(id=0)
 {
	 this.licenseFeeStandardList=[];	 	 
	 this.standardList.forEach((val)=>{
		let existsEntry = this.licenseFeesEntries.find(s => s.standard_id == val.id);
		if(existsEntry === undefined || (id!=0 && id==val.id))
		{
			this.licenseFeeStandardList.push({id:val.id,name:val.name})
		}		
     });	
  }
  
  get f() { return this.form.controls; }  
  
  removeLicenseFee(standard_id:number) {
    let index= this.licenseFeesEntries.findIndex(s => s.standard_id ==  standard_id);
	if(index != -1)
	   this.licenseFeesEntries.splice(index,1);
   
    this.newRecordStatus=true;
	this.licenseFeeIndex=null;
	
	this.form.patchValue({
       standard_id: '',
	   standard_name: '',
	   license_fee: '',
	   subsequent_license_fee:''
    });
	
	this.getLicenseFeeStandards();
  }
  
  checkLicenseFee()
  {
	let standard_id:number = this.form.get('standard_id').value;
	let license_fee = this.form.get('license_fee').value;
	let subsequent_license_fee = this.form.get('subsequent_license_fee').value;		
	
	if(standard_id>0 || license_fee.trim()!='')
	{
		if(standard_id<=0){
			this.standard_idErrors = 'Please select the Standard';			
		}else{
			this.standard_idErrors = '';
		}
		
		if(license_fee.trim()==''){
			this.license_feeErrors = 'Please select the License Fee';			
		}else if(!license_fee.match(/^\d*\.?\d{0,2}$/g)){
			this.license_feeErrors = 'Invalid License Fee';			
	    }else{
			this.license_feeErrors = '';
		}
		
		if(!subsequent_license_fee.match(/^\d*\.?\d{0,2}$/g)){
			this.subsequent_license_feeErrors = 'Invalid Subsequent License Fee';			
	    }else{
			this.subsequent_license_feeErrors = '';
		}
		
	}else{
		this.standard_idErrors = '';
		this.license_feeErrors = '';
		this.subsequent_license_feeErrors = '';
		this.licenseFeeIncompleteErrors = '';
	}	
		
  }
  
  resetLicenseFee()
  {
	 this.standard_idErrors = '';
	 this.license_feeErrors = '';
	 this.subsequent_license_feeErrors = '';
	 this.licenseFeeIncompleteErrors = '';
	 
	 this.form.patchValue({
       standard_id: '',
	   standard_name: '',
	   license_fee: '',
	   subsequent_license_fee:''
     });
	 this.editStatus=false; 
	 this.newRecordStatus=true;
	 this.licenseFeeIndex=null;
	
	 this.getLicenseFeeStandards();
  }
  
  licenseFeeStatus=true;
  licenseFeeIndex=null;
  newRecordStatus=false;
  addLicenseFee(){
	let standard_id:number = this.form.get('standard_id').value;
	let license_fee = this.form.get('license_fee').value;
	let subsequent_license_fee = this.form.get('subsequent_license_fee').value;	
		
	this.licenseFeeStatus=true;
		
	if(standard_id<=0){
        this.standard_idErrors = 'Please select the Standard';
		this.licenseFeeStatus=false;
    }
	
	if(license_fee.trim()==''){
        this.license_feeErrors = 'Please select the License Fee';
		this.licenseFeeStatus=false;
    }else if(!license_fee.match(/^\d*\.?\d{0,2}$/g)){
		this.license_feeErrors = 'Invalid License Fee';
		this.licenseFeeStatus=false;
	}
	
	if(!subsequent_license_fee.match(/^\d*\.?\d{0,2}$/g))
	{
		this.subsequent_license_feeErrors = 'Invalid Subsequent License Fee';
		this.licenseFeeStatus=false;
	}
	
	
	
	if(!this.licenseFeeStatus)
	{
		return false;
	}
	
	let standard_name = this.standardList.find(s => s.id ==  standard_id);
				
	let entry= this.licenseFeesEntries.find(s => s.standard_id ==  standard_id);
    if(entry === undefined){
		let expobject:any=[];
		expobject["standard_id"] = standard_id;
		expobject["standard_name"] = standard_name.name;	
		expobject["license_fee"] = license_fee;
		expobject["subsequent_license_fee"] = subsequent_license_fee;
		
		this.licenseFeesEntries.push(expobject);
	}else{
		entry.license_fee = license_fee;
		entry.subsequent_license_fee = subsequent_license_fee;
	}		
		
    this.form.patchValue({
       standard_id: '',
	   standard_name: '',
	   license_fee: '',
	   subsequent_license_fee:''
    });
	this.newRecordStatus=true;
	this.licenseFeeIndex=null;
	
	this.getLicenseFeeStandards();
	this.editStatus=false; 
  }
  
  editStatus=false;
  editLicenseFee(standard_id:number)
  {
	this.editStatus=true;  
	this.standard_idErrors = '';
	this.license_feeErrors = '';
	this.subsequent_license_feeErrors = '';
	this.licenseFeeIncompleteErrors = '';
	 
	let rtn= this.licenseFeesEntries.find(s => s.standard_id ==  standard_id);
	this.getLicenseFeeStandards(rtn.standard_id);
    this.form.patchValue({
       standard_id: rtn.standard_id,
       license_fee:rtn.license_fee,
	   subsequent_license_fee:rtn.subsequent_license_fee	   
    });	
  }
  
  onSubmit(){
    
	this.checkLicenseFee();	
	if(this.standard_idErrors!='' || this.license_feeErrors != '' || this.subsequent_license_feeErrors !='')
	{
		return false;
	}
		
	this.standard_idErrors = '';
	this.license_feeErrors = '';
	this.subsequent_license_feeErrors = '';
	
	if(this.form.get('standard_id').value!='' || this.form.get('license_fee').value!='' || this.form.get('subsequent_license_fee').value!='')
	{
	  this.licenseFeeIncompleteErrors = 'License Fee details incomplete. Please Add/Reset License Fee to proceed further.';		
	  return false;	
	}else{
	  this.licenseFeeIncompleteErrors = '';
	}
	
    if (this.form.valid) {
	
	  if(!this.newRecordStatus)	
	  {
		return false  
	  }
	  
      this.loading = true;    
		
	  let licensefeedatas = [];
      this.licenseFeesEntries.forEach((val)=>{
          licensefeedatas.push({standard_id:val.standard_id,standard_name:val.standard_name,license_fee:val.license_fee,subsequent_license_fee:val.subsequent_license_fee})
      });
	  
	  let formvalue = this.form.value;
	  formvalue.licensefees = [];      	  
      formvalue.licensefees = licensefeedatas;
      	  
	  //this.formData.append('formvalues',JSON.stringify(this.formData));
	  
	  this.licensefeeService.addData(this.form.value).pipe(first()).subscribe(res => {
	
          if(res.status){
			this.editStatus=false; 
            this.success = {summary:res.message};
		    setTimeout(()=>this.router.navigate(['/master/licensefee/list']),this.errorSummary.redirectTime);
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
