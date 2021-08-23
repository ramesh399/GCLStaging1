import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { InspectiontimeService } from '@app/services/master/inspectiontime/inspectiontime.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { StandardService } from '@app/services/standard.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import {Observable} from 'rxjs';
import { Standard } from '@app/services/standard';


@Component({
  selector: 'app-inspectiontime',
  templateUrl: './inspectiontime.component.html',
  styleUrls: ['./inspectiontime.component.scss'],
  providers: [InspectiontimeService]
})
export class InspectiontimeComponent implements OnInit {
	
  form : FormGroup;
  otherform : FormGroup;
  standardform : FormGroup;
  daysform: FormGroup;
  workersstdform: FormGroup;
  processstdform: FormGroup;
  loading:any=[];
  standardList:Standard[];
  workerStandardList:any=[];
  processStandardList:any=[]; 
  buttonDisable = false;
  error:any;
  id:number;
  success:any;
  inspectionData:any;
  otherinspectionData:any;
  standardinspectionData:any;
  daysEntries:any=[];

  editStatus=0;
  editOtherStatus=0;
  editStandardStatus=0;
  editDaysStatus=0;  
  modalss:any;
  
  inspectiontimeEntries:any=[];
  otherinspectiontimeEntries:any=[];
  standardinspectiontimeEntries:any=[];
  no_of_workers_fromErrors='';
  no_of_process_fromErrors='';
  no_of_standard_fromErrors='';
  inspector_daysErrors='';
	
  userType:number;
  userdetails:any;
  userdecoded:any;
  
  formData:FormData = new FormData();
  
  no_of_workers_and_process_based_status=true;
  process_based_status=false;
  standard_based_status=false;   
  workerStandardsSelected:any=[];
  processStandardsSelected:any=[];
	
  constructor(private modalService: NgbModal,private standardservice: StandardService,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private inspectiontimeService: InspectiontimeService,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {
	  
	this.form = this.fb.group({
      no_of_workers_from:['',[Validators.required,Validators.pattern('^[0-9]*$')]],
	  no_of_workers_to:['',[Validators.required,Validators.pattern('^[0-9]*$')]]	 	  
	});

	this.workersstdform = this.fb.group({
		inspection_time_type:['0'],
		standard_id:['',[Validators.required]]
	});
	
	this.processstdform = this.fb.group({
		inspection_time_type:['1'],
		standard_id:['',[Validators.required]]
	});

	this.daysform = this.fb.group({
		no_of_process_from:['',[Validators.required,Validators.pattern('^[0-9]*$')]],
		no_of_process_to:['',[Validators.required,Validators.pattern('^[0-9]*$')]],
		inspector_days:['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$')]]	 	  
	});

	this.otherform = this.fb.group({
		no_of_process_from:['',[Validators.required,Validators.pattern('^[0-9]*$')]],
		no_of_process_to:['',[Validators.required,Validators.pattern('^[0-9]*$')]],
		inspector_days:['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$')]]
	});

	this.standardform = this.fb.group({
		no_of_standard_from:['',[Validators.required,Validators.pattern('^[0-9]*$')]],
		no_of_standard_to:['',[Validators.required,Validators.pattern('^[0-9]*$')]],
		inspector_days:['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$')]]
	});

	this.standardservice.getStandard().subscribe(res => {
		this.standardList = res['standards'];
		this.loadDetails();		
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
  
  get f() { return this.form.controls; }
  get df() { return this.daysform.controls; } 
  get wf() { return this.workersstdform.controls; } 
  get pf() { return this.processstdform.controls; }  
  get of() { return this.otherform.controls; }
  get sf() { return this.standardform.controls; }  

  loadDetails()
  {
	this.inspectiontimeService.getInspectiontime(this.id).pipe(first())
    .subscribe(res => {
	  this.inspectiontimeEntries = res.inspectiontimes; 
	  this.otherinspectiontimeEntries = res.otherinspectiontimes; 
	  this.standardinspectiontimeEntries = res.standardinspectiontimes;  
	  
	  this.workerStandardsSelected=res.workerstandards;
	  this.processStandardsSelected=res.procstandards;
	  
	  this.workerStandardList = [];
	  this.processStandardList = [];
	  this.standardList.forEach((val,index)=>{
		  if(!this.processStandardsSelected.includes(val.id))
		  {
			this.workerStandardList.push({id: val.id, code: val.code});
		  }
		  
		  if(!this.workerStandardsSelected.includes(val.id))
		  {
			this.processStandardList.push({id: val.id, code: val.code});
		  }			  
	  });
	  
	  
	  //workerStandardList:Standard[];
	  //processStandardList:Standard[];  
	  //channelArray.indexOf("three")
  
	  
	  this.workersstdform.patchValue({
		standard_id: res.workerstandards
	  });	

	  this.processstdform.patchValue({
		standard_id: res.procstandards
	  });	
    },
    error => {
        this.error = error;
        //this.loading[''] = false;
		});
  }
  editLogStatus:any=false;
  standard_idErrors:any = '';
  otherinspectionTimeIndex:any=0;
  checkInspectionTime()
  {
	this.no_of_workers_fromErrors = '';  
	if (this.form.valid) 
	{
		let no_of_workers_from = this.form.get('no_of_workers_from').value.toString();
		let no_of_workers_to = this.form.get('no_of_workers_to').value.toString();
				
		if(no_of_workers_from!='' || no_of_workers_to!='' )
		{
			if(this.no_of_workers_fromErrors == '')
			{
				if(Number(no_of_workers_from)>=Number(no_of_workers_to))
				{
					this.no_of_workers_fromErrors = 'Number of Workers To should be greater than the Number of Workers From';
				}
			}			
			
			
			this.inspectiontimeEntries.forEach((val,index)=>{
				let from_val = val.no_of_workers_from;
				let to_val = val.no_of_workers_to
				if(this.inspectionTimeIndex===null || this.inspectionTimeIndex!=index)
				{
					if ((no_of_workers_from >= from_val && no_of_workers_from <= to_val) || (no_of_workers_to >= from_val && no_of_workers_to <= to_val)) {
						this.no_of_workers_fromErrors = 'Number of Workers From / To range has already been taken.';
					}
				}	
			});
			
			//console.log(this.no_of_workers_fromErrors);
			
		}else{
			this.no_of_workers_fromErrors = '';
			//this.inspector_daysErrors = '';
		}
	}	
  }

  checkProcessCount()
  {
	this.no_of_process_fromErrors = '';
	if (this.daysform.valid) 
	{
		let no_of_process_from = this.daysform.get('no_of_process_from').value.toString();
		let no_of_process_to = this.daysform.get('no_of_process_to').value.toString();
		//return false;
		
		if(no_of_process_from!='' || no_of_process_to!='')
		{
			if(this.no_of_process_fromErrors == '')
			{
				if(Number(no_of_process_from)>=Number(no_of_process_to))
				{
					this.no_of_process_fromErrors = 'Number of Process To should be greater than the Number of Process From';
				}
				
			}			
			
			
			this.inspectiontimeEntries.forEach((val,index)=>{
				let from_val = val.no_of_process_from;
				let to_val = val.no_of_process_to
				if(this.inspectionTimeIndex===null || this.inspectionTimeIndex!=index)
				{
					if ((no_of_process_from >= from_val && no_of_process_from <= to_val) || (no_of_process_to >= from_val && no_of_process_to <= to_val)) {
						this.no_of_process_fromErrors = 'Number of Process From / To range has already been taken.';
					}
					
				}	
			});
			
			//console.log(this.no_of_workers_fromErrors);
			
		}
		else
		{
			this.no_of_process_fromErrors = '';
		}
	}	
		
  }

  checkProcessCountStd()
  {
	this.no_of_process_fromErrors = '';  
	if (this.otherform.valid) 
	{
		let no_of_process_from = this.otherform.get('no_of_process_from').value.toString();
		let no_of_process_to = this.otherform.get('no_of_process_to').value.toString();
		//return false;
		
		if(no_of_process_from!='' || no_of_process_to!='')
		{
			if(this.no_of_process_fromErrors == '')
			{
				if(Number(no_of_process_from)>=Number(no_of_process_to))
				{
					this.no_of_process_fromErrors = 'Number of Process To should be greater than the Number of Process From';
				}
				
			}			
			
			
			//this.inspectiontimeEntries.forEach((val,index)=>{
			this.otherinspectiontimeEntries.forEach((val,index)=>{
				let from_val = val.no_of_process_from;
				let to_val = val.no_of_process_to
				//if(this.inspectionTimeIndex===null || this.inspectionTimeIndex!=index)
				
				if(this.OtherinspectionTimeIndex===null || this.OtherinspectionTimeIndex!=index)
				{
					if ((no_of_process_from >= from_val && no_of_process_from <= to_val) || (no_of_process_to >= from_val && no_of_process_to <= to_val)) {
						this.no_of_process_fromErrors = 'Number of Process From / To range has already been taken.';
					}
					
				}	
			});
			
			//console.log(this.no_of_workers_fromErrors);
			
		}
		else
		{
			this.no_of_process_fromErrors = '';
		}
	}	
		
  }

  checkStandardCountStd()
  {
	this.no_of_standard_fromErrors = '';  
	if (this.standardform.valid) 
	{
		let no_of_standard_from = this.standardform.get('no_of_standard_from').value.toString();
		let no_of_standard_to = this.standardform.get('no_of_standard_to').value.toString();
		//return false;
		
		if(no_of_standard_from!='' || no_of_standard_to!='')
		{
			if(this.no_of_standard_fromErrors == '')
			{
				if(Number(no_of_standard_from)>=Number(no_of_standard_to))
				{
					this.no_of_standard_fromErrors = 'Number of Standard To should be greater than the Number of Standard From';
				}
				
			}			
			
			
			//this.inspectiontimeEntries.forEach((val,index)=>{
			this.standardinspectiontimeEntries.forEach((val,index)=>{
				let from_val = val.no_of_standard_from;
				let to_val = val.no_of_standard_to
				//if(this.inspectionTimeIndex===null || this.inspectionTimeIndex!=index)
				
				if(this.StandardinspectionTimeIndex===null || this.StandardinspectionTimeIndex!=index)
				{
					if ((no_of_standard_from >= from_val && no_of_standard_from <= to_val) || (no_of_standard_to >= from_val && no_of_standard_to <= to_val)) {
						this.no_of_standard_fromErrors = 'Number of Standard From / To range has already been taken.';
					}
					
				}	
			});
			
			//console.log(this.no_of_workers_fromErrors);
			
		}
		else
		{
			this.no_of_process_fromErrors = '';
		}
	}	
		
  }
  
  resetInspectionTime()
  {
	 this.form.patchValue({
      no_of_workers_from: '',
	  no_of_workers_to: ''
    });	
	this.form.reset();
	this.no_of_workers_fromErrors = '';

	this.editDaysStatus=0;   
	this.editStatus=0;
	this.daysEntries = [];
	this.inspectionData = '';
	
	this.inspectionTimeIndex=null;
  }

  resetOtherInspectionTime()
  {
	 this.otherform.patchValue({
      no_of_process_from: '',
	  no_of_process_to: '',
	  inspector_days:''
    });	
	
	this.no_of_process_fromErrors = '';

	this.editOtherStatus=0;   
	this.otherinspectionData = '';
	
	this.OtherinspectionTimeIndex=null;
  }

  resetStandardInspectionTime()
  {
	 this.standardform.patchValue({
      no_of_standard_from: '',
	  no_of_standard_to: '',
	  inspector_days:''
    });	
	
	this.no_of_standard_fromErrors = '';

	this.editStandardStatus=0;   
	this.standardinspectionData = '';
	
	this.StandardinspectionTimeIndex=null;
  }
  
	addWorkersStd()
	{
		this.wf.standard_id.markAsTouched();

		if (this.workersstdform.valid) 
		{
			this.loading['stdbutton'] = true;

			this.inspectiontimeService.addStdData(this.workersstdform.value)
			.pipe(first())
			.subscribe(res => {

				if(res.status){
					this.success = {summary:res.message};
					this.buttonDisable = false;
					this.loadDetails();
				}else if(res.status == 0){
					//this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.enquiryForm)};
					this.error = {summary:res};
				}
				this.loading['stdbutton'] = false;
				this.buttonDisable = false;
			},
			error => {
				this.error = {summary:error};
				this.loading['stdbutton'] = false;
			});
					
		}
		else 
		{ 
			this.error = {summary:this.errorSummary.errorSummaryText};	
			this.errorSummary.validateAllFormFields(this.workersstdform);       
		}

	}

	addProcessStd()
	{
		this.pf.standard_id.markAsTouched();

		if (this.processstdform.valid) 
		{
			this.loading['stdbutton'] = true;

			this.inspectiontimeService.addStdData(this.processstdform.value)
			.pipe(first())
			.subscribe(res => {

				if(res.status){
					this.success = {summary:res.message};
					this.loadDetails();
					this.buttonDisable = false;
				}else if(res.status == 0){
					//this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.enquiryForm)};
					this.error = {summary:res};
				}
				this.loading['stdbutton'] = false;
				this.buttonDisable = false;
			},
			error => {
				this.error = {summary:error};
				this.loading['stdbutton'] = false;
			});
					
		}
		else 
		{ 
			this.error = {summary:this.errorSummary.errorSummaryText};	
			this.errorSummary.validateAllFormFields(this.processstdform);       
		}

	}

	inspectionTimeIndex=null;
	addInspectionTime()
	{
		this.f.no_of_workers_from.markAsTouched();
		this.f.no_of_workers_to.markAsTouched();
		

		this.checkInspectionTime();
		
		if(this.no_of_workers_fromErrors!='' )
		{
			return false;
		}

		this.no_of_workers_fromErrors = '';


		if (this.form.valid) 
		{
			this.loading['button'] = true;
			//this.buttonDisable = true;

			let no_of_workers_from = this.form.get('no_of_workers_from').value.toString();
			let no_of_workers_to = this.form.get('no_of_workers_to').value.toString();
			let type = 'normal';

			let expobject:any= {no_of_workers_from:no_of_workers_from,no_of_workers_to:no_of_workers_to,type:type};

			if(1)
			{

				if(this.inspectionData){
				expobject.id = this.inspectionData.id;
				}
				
				//this.formData.append('formvalues',JSON.stringify(expobject));

				this.inspectiontimeService.addData(expobject)
				.pipe(first())
				.subscribe(res => {

				
					if(res.status){
					this.inspectionData = '';
					this.loadDetails();
					this.inspectiontimeFormreset();
					this.success = {summary:res.message};
					this.buttonDisable = false;
					}else if(res.status == 0){
					//this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.enquiryForm)};
					this.error = {summary:res};
					}
					this.loading['button'] = false;
					this.buttonDisable = false;
				},
				error => {
					this.error = {summary:error};
					this.loading['button'] = false;
				});
				
			} else {
				
				this.error = {summary:this.errorSummary.errorSummaryText};
				this.errorSummary.validateAllFormFields(this.form); 
				
			}
		}
		else 
		{ 
			this.error = {summary:this.errorSummary.errorSummaryText};	
			this.errorSummary.validateAllFormFields(this.form);       
		}

	}

	addOtherInspectionTime()
	{
		this.of.no_of_process_from.markAsTouched();
		this.of.no_of_process_to.markAsTouched();
		this.of.inspector_days.markAsTouched();

		this.checkProcessCountStd();
		
		if(this.no_of_process_fromErrors!='' )
		{
			return false;
		}

		this.no_of_process_fromErrors = '';


		if (this.otherform.valid) 
		{
			this.loading['button'] = true;
			//this.buttonDisable = true;

			let no_of_process_from = this.otherform.get('no_of_process_from').value.toString();
			let no_of_process_to = this.otherform.get('no_of_process_to').value.toString();
			let inspector_days = this.otherform.get('inspector_days').value.toString();
			let type = 'other';

			let expobject:any= {no_of_process_from:no_of_process_from,no_of_process_to:no_of_process_to,inspector_days:inspector_days,type:type};

			if(1)
			{

				if(this.otherinspectionData){
				expobject.id = this.otherinspectionData.id;
				}
				
				//this.formData.append('formvalues',JSON.stringify(expobject));

				this.inspectiontimeService.addData(expobject)
				.pipe(first())
				.subscribe(res => {

				
					if(res.status){
					this.otherinspectionData = '';
					this.loadDetails();
					this.otherinspectiontimeFormreset();
					this.success = {summary:res.message};
					this.buttonDisable = false;
					}else if(res.status == 0){
					//this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.enquiryForm)};
					this.error = {summary:res};
					}
					this.loading['button'] = false;
					this.buttonDisable = false;
				},
				error => {
					this.error = {summary:error};
					this.loading['button'] = false;
				});
				
			} else {
				
				this.error = {summary:this.errorSummary.errorSummaryText};
				this.errorSummary.validateAllFormFields(this.otherform); 
				
			}
		}
		else 
		{ 
			this.error = {summary:this.errorSummary.errorSummaryText};	
			this.errorSummary.validateAllFormFields(this.otherform);       
		}

	}


	addStandardInspectionTime()
	{
		this.sf.no_of_standard_from.markAsTouched();
		this.sf.no_of_standard_to.markAsTouched();
		this.sf.inspector_days.markAsTouched();

		this.checkStandardCountStd();
		
		if(this.no_of_standard_fromErrors!='' )
		{
			return false;
		}

		this.no_of_standard_fromErrors = '';


		if (this.standardform.valid) 
		{
			this.loading['button'] = true;
			//this.buttonDisable = true;

			let no_of_standard_from = this.standardform.get('no_of_standard_from').value.toString();
			let no_of_standard_to = this.standardform.get('no_of_standard_to').value.toString();
			let inspector_days = this.standardform.get('inspector_days').value.toString();
			let type = 'standard';

			let expobject:any= {no_of_standard_from:no_of_standard_from,no_of_standard_to:no_of_standard_to,inspector_days:inspector_days,type:type};

			if(1)
			{

				if(this.standardinspectionData){
				expobject.id = this.standardinspectionData.id;
				}
				
				//this.formData.append('formvalues',JSON.stringify(expobject));

				this.inspectiontimeService.addData(expobject)
				.pipe(first())
				.subscribe(res => {

				
					if(res.status){
					this.standardinspectionData = '';
					this.loadDetails();
					this.standardinspectiontimeFormreset();
					this.success = {summary:res.message};
					this.buttonDisable = false;
					}else if(res.status == 0){
					//this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.enquiryForm)};
					this.error = {summary:res};
					}
					this.loading['button'] = false;
					this.buttonDisable = false;
				},
				error => {
					this.error = {summary:error};
					this.loading['button'] = false;
				});
				
			} else {
				
				this.error = {summary:this.errorSummary.errorSummaryText};
				this.errorSummary.validateAllFormFields(this.standardform); 
				
			}
		}
		else 
		{ 
			this.error = {summary:this.errorSummary.errorSummaryText};	
			this.errorSummary.validateAllFormFields(this.standardform);       
		}

	}


	  



	inspectiontimeFormreset()
	{
		this.editDaysStatus=0;   
		this.editStatus=0;
		this.form.reset();
		this.inspectionData = '';
		this.daysEntries = [];
		
		this.form.patchValue({      
			no_of_workers_from:'',         
			no_of_workers_to:''
		});
	}

	otherinspectiontimeFormreset()
	{ 
		this.editOtherStatus=0;
		this.otherform.reset();
		this.otherinspectionData = '';
		
		this.otherform.patchValue({      
			no_of_process_from:'',         
			no_of_process_to:'',
			inspector_days:''
		});
	}


	standardinspectiontimeFormreset()
	{ 
		this.editStandardStatus=0;
		this.standardform.reset();
		this.standardinspectionData = '';
		
		this.standardform.patchValue({      
			no_of_standard_from:'',         
			no_of_standard_to:'',
			inspector_days:''
		});
	}

	editInspectionTime(index:number,inspectiondata)
	{
		this.no_of_workers_fromErrors = '';
		
		this.editStatus=1; 
		this.inspectionTimeIndex = index;
		this.inspectionData = inspectiondata;
		let qual = this.inspectiontimeEntries[index];
		this.form.patchValue({
		no_of_workers_from: Number(qual.no_of_workers_from),
		no_of_workers_to: Number(qual.no_of_workers_to)
		});
		this.getDaysData(this.inspectionData.id);
		this.loadDetails();
		this.scrollToBottom();
	}

	OtherinspectionTimeIndex=null;
	editOtherInspectionTime(index:number,inspectiondata)
	{
		this.no_of_process_fromErrors = '';
		
		this.editOtherStatus=1; 
		this.OtherinspectionTimeIndex = index;
		this.otherinspectionData = inspectiondata;
		let qual = this.otherinspectiontimeEntries[index];
		this.otherform.patchValue({
		no_of_process_from: Number(qual.no_of_process_from),
		no_of_process_to: Number(qual.no_of_process_to),
		inspector_days: Number(qual.inspector_days)
		});
		this.loadDetails();
		// this.scrollToBottom();
	}

	StandardinspectionTimeIndex=null;
	editStandardInspectionTime(index:number,inspectiondata)
	{
		this.no_of_standard_fromErrors = '';
		
		this.editStandardStatus=1; 
		this.StandardinspectionTimeIndex = index;
		this.standardinspectionData = inspectiondata;
		let qual = this.standardinspectiontimeEntries[index];
		this.standardform.patchValue({
		no_of_standard_from: Number(qual.no_of_standard_from),
		no_of_standard_to: Number(qual.no_of_standard_to),
		inspector_days: Number(qual.inspector_days)
		});
		this.loadDetails();
		// this.scrollToBottom();
	}

	getDaysData(inspectiondataid)
	{
		this.daysEntries = [];
		this.loading['daysdata'] = true;
		this.inspectiontimeService.getDaysData({id:inspectiondataid})
		.pipe(first())
		.subscribe(res => {

		this.loading['daysdata'] =false;
		if(res.status){
			this.daysEntries = res['data'];
		}else if(res.status == 0){
			this.error = {summary:res};
		}       
		},
		error => {
			this.error = {summary:error};
			this.loading['daysdata'] =false;
		});
	}

	addDays(content)
	{
	  this.daysdata = '';
	  
	  this.daysform.reset();
	  
	  this.no_of_workers_fromErrors = '';
	  
	  this.editDaysStatus=0;  
	  
	  this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
	}

	daysdata:any;
	editDays(content,index:number,Daysdata) 
	{
		this.editDaysStatus=1;  
		this.daysdata = Daysdata;
		
		this.daysform.patchValue({
			no_of_process_from:this.daysdata.no_of_process_from,
			no_of_process_to:this.daysdata.no_of_process_to,      
			inspector_days:this.daysdata.inspector_days
		});

		this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
	}

	daysIndex=null;
	removeDays(content,Daysdata) 
	{
		this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

		this.modalss.result.then((result) => {
			this.inspectiontimeService.deleteDaysData({id:Daysdata.id})
			.pipe(first())
			.subscribe(res => {
		
				if(res.status){
				this.getDaysData(this.inspectionData.id);
				this.success = {summary:res.message};
				this.buttonDisable = true;
				}else if(res.status == 0){
				this.error = {summary:res};
				}
				this.loading['button'] = false;
				this.buttonDisable = false;
			},
			error => {
				this.error = {summary:error};
				this.loading['button'] = false;
			});
		}, (reason) => {
			
		});

		
	}

	removeOtherInspectionTime(content,Otherdata) 
	{
		this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

		this.modalss.result.then((result) => {
			this.inspectiontimeService.deleteOtherData({id:Otherdata.id})
			.pipe(first())
			.subscribe(res => {
		
				if(res.status){
				this.success = {summary:res.message};
				this.buttonDisable = false;
				}else if(res.status == 0){
				this.error = {summary:res};
				}
				this.loading['button'] = false;
				this.buttonDisable = false;
				this.loadDetails();
			},
			error => {
				this.error = {summary:error};
				this.loading['button'] = false;
			});
		}, (reason) => {
			
		});
		

		
	}

	removeStandardInspectionTime(content,Standarddata) 
	{
		this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

		this.modalss.result.then((result) => {
			this.inspectiontimeService.deleteStandardData({id:Standarddata.id})
			.pipe(first())
			.subscribe(res => {
		
				if(res.status){
				this.success = {summary:res.message};
				this.buttonDisable = false;
				}else if(res.status == 0){
				this.error = {summary:res};
				}
				this.loading['button'] = false;
				this.buttonDisable = false;
				this.loadDetails();
			},
			error => {
				this.error = {summary:error};
				this.loading['button'] = false;
			});
		}, (reason) => {
			
		});
		

		
	}

	logsuccess:any;
	logerror:any;
	logloading:any;
	submitLogAction(){
		
		this.no_of_process_fromErrors == '';
		
		this.df.no_of_process_from.markAsTouched();
		this.df.no_of_process_to.markAsTouched();
		this.df.inspector_days.markAsTouched();

		this.checkProcessCount();

		if(this.no_of_process_fromErrors!='' )
		{
			return false;
		}

		this.no_of_process_fromErrors = '';

		if(this.daysform.valid)
		{
			let no_of_process_from = this.daysform.get('no_of_process_from').value;
			let no_of_process_to = this.daysform.get('no_of_process_to').value;     
			let inspector_days = this.daysform.get('inspector_days').value;
			
		
			let datalog:any = {inspection_id:this.inspectionData.id,no_of_process_from:no_of_process_from,no_of_process_to:no_of_process_to,inspector_days:inspector_days};
			if(this.daysdata){
				datalog.id = this.daysdata.id;
			}

		
			this.loading['logsbutton'] = true;
			this.inspectiontimeService.addDaysData(datalog)
			.pipe(first())
			.subscribe(res => {

				if(res.status){
					this.getDaysData(this.inspectionData.id);
					this.logsuccess = res.message;
					setTimeout(() => {
					this.logsuccess = '';
					this.daysdata = '';
					this.modalss.close('');
					},this.errorSummary.redirectTime);
					
					this.buttonDisable = true;
				}else if(res.status == 0){
					this.logerror = {summary:res};
				}
				this.loading['logsbutton'] = false;
				
				this.buttonDisable = false;
			},
			error => {
				this.loading['logsbutton'] = false;
				this.logerror = {summary:error};
				
			});
		
		}
		
		
		
	}

	scrollToBottom()
	{
		window.scroll({ 
		top: window.innerHeight,
		left: 0, 
		behavior: 'smooth' 
		});
	}

	getSelectedValue(val)
    {
      return this.standardList.find(x=> x.id==val).code;
    }
	
	getSelectedWorkersValue(val)
	{
      return this.workerStandardList.find(x=> x.id==val).code;
    }
	
	getSelectedProcessValue(val)
    {
      return this.processStandardList.find(x=> x.id==val).code;
    }
	 
	
	changeInspectionTimeTab(arg)
	{
		this.no_of_workers_and_process_based_status=false;
		this.process_based_status=false;  
		this.standard_based_status=false;  
		  
		if(arg=='no_of_workers_and_process_based'){
			this.no_of_workers_and_process_based_status=true;
		}else if(arg=='process_based'){
			this.process_based_status=true;
		}else if(arg=='standard_based'){
			this.standard_based_status=true;
		}
	}	 
  
}
