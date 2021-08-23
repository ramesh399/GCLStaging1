import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl, FormArray } from '@angular/forms';
import { RenewalChecklistService } from '@app/services/application/renewal-checklist.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';
import { StandardAdditionService } from '@app/services/change-scope/standard-addition.service';
import { AuthenticationService } from '@app/services';
@Component({
  selector: 'app-renewal-checklist',
  templateUrl: './renewal-checklist.component.html',
  styleUrls: ['./renewal-checklist.component.scss']
})
export class RenewalChecklistComponent implements OnInit {

  title = 'Add Standard';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  typeErrors='';
  success:any;
  default_type = '';
  nameErrors = '';
  codeErrors = '';
  short_codeErrors = '';
  modalss:any;
  
  audit_id:number;
  app_id:number;
  
  formContent = true;
  messageContent = false;
  
  standardAdditionList:any=[];
  userType:number;
  userdetails:any;
  role_name:any;
  resource_access:any;
  
  constructor(public authservice: AuthenticationService, public standardAdditionService:StandardAdditionService, private modalService: NgbModal,private router: Router,private fb:FormBuilder,private renewalChecklistService:RenewalChecklistService,private errorSummary: ErrorSummaryService,private activatedRoute:ActivatedRoute) { }

  ngOnInit() {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
	this.app_id = this.activatedRoute.snapshot.queryParams.app_id;
	
	this.authservice.currentUser.subscribe(x => {
		if(x){
		  let user = this.authservice.getDecodeToken();
		  this.userType= user.decodedToken.user_type;
		  this.userdetails= user.decodedToken;
		  this.role_name = this.userdetails.role_name.toLowerCase();
		  this.resource_access = this.userdetails.resource_access;
		}
	  });


	this.form = this.fb.group({
		app_id:[this.app_id],
		audit_id:[this.audit_id],
		addition_standard:[''],
		type:['',[Validators.required]]	  
	});
	this.standardAdditionService.getStandardAdditionListDetails({app_id:this.app_id}).pipe(first()).subscribe(resadd => {
		this.standardAdditionList = resadd['standardaddition'];	
		let selectAddition:any=[];
		this.standardAdditionList.forEach((x,index)=>{
			selectAddition.push(x.id);
		});
		this.form.patchValue({
			addition_standard : selectAddition
		})
  	});				



	
  }

  getSelectedValue(val)
  {
    return this.standardAdditionList.find(x=> x.id==val).name;
  }

  get f() { return this.form.controls; }
  
  onSubmit(confirmcontent){
    
	if(this.f.type.value=='')
	{
		this.typeErrors='Answer is required';		
	}else{
		this.typeErrors='';
	}	
		
    if (this.form.valid) 
	{      
		if(this.f.type.value=='')
		{		 
			return false;
		}
		this.typeErrors='';
		
		this.modalss = this.modalService.open(confirmcontent, {ariaLabelledBy: 'modal-basic-title',centered: true});
		
		this.modalss.result.then((result) => {
			this.loading = true;		  
			this.renewalChecklistService.addData(this.form.value)
			.pipe(
				first()        
			)
			.subscribe(res => {        
				if(res.status){					
					this.buttonDisable=true;
					if(res.type==2)
					{
						//this.router.navigate(['/application/add-request']);
						this.router.navigateByUrl('/application/add-request?app_id='+this.app_id+'&type=renewal&renewal_id='+res.renewal_id);
					}else{
						this.success = res.message;
						this.formContent = false;
						this.messageContent = true;
						//this.router.navigate(['/offer/generate-list']);
					}
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
		});  
      //console.log('sdfsdfdf');
    } else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form); 
      
    }
  }

}