import { Component, OnInit,ViewChild,ViewEncapsulation,Output, EventEmitter } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl } from '@angular/forms';
import { AuditPlanService } from '@app/services/audit/audit-plan.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first, debounceTime, distinctUntilChanged, map } from 'rxjs/operators';
import {Observable,Subject} from 'rxjs';
import { GenerateDetailService } from '@app/services/offer/generate-detail.service';


@Component({
  selector: 'app-audit-plan',
  templateUrl: './audit-plan.component.html',
  styleUrls: ['./audit-plan.component.scss'],
  encapsulation: ViewEncapsulation.None
})
export class AuditPlanComponent implements OnInit {

	title = 'Audit Planning';
	form : any;
	loading = false;
	buttonDisable = false;
	error:any='';
	id:number;
	app_id:number;
	invoice_id:number;
	offer_id:number;
	success:any='';
	submittedError = false;
	nameErrors ='';
	descriptionErrors= '';
	auditPlanData:any=[];
	auditplanlist=[];
	auditorList = [];
	
	technicalExpertList = [];
	translatorList = [];
	appLeadAuditors = [];
						
	leadAuditorList = [];
	application_lead_auditor:any;
	@ViewChild('form', {static: false}) ngForm: NgForm;
	
	unitAuditorsEntries:Array<any> = [];
	appAuditorsEntries:Array<any> = [];
	
	unitLeadAuditorsEntries:Array<any> = [];
	appUnitLeadAuditorsEntries:Array<any> = [];
	
	appPlanData:Array<any> = [];
	
	appActualMandayData:Array<any> = [];
	
	activeUnit = 0;
	
	auditorlist=[];
	
	minDate: Date;
	loadings:any=[];


	userDateUpdate = new Subject<any>();
	public consoleMessages: string[] = [];
	manday: any;

	constructor(private activatedRoute:ActivatedRoute,
		 private generateDetail:GenerateDetailService, private router: Router,private fb:FormBuilder,private auditPlanService: AuditPlanService,private errorSummary: ErrorSummaryService) { }
	
	ngOnInit() {
		this.userDateUpdate.pipe(
			debounceTime(700),
			distinctUntilChanged())
		.subscribe(value => {
			//this.consoleMessages.push(value);
			//console.log(this.consoleMessages);
			if(value.unitdates.length>0){
				this.getAuditors(value.ut,value.unitid);
			}else{
				this.auditorList[value.unitid] = [];
				this.technicalExpertList[value.unitid] = [];
				this.translatorList[value.unitid] = [];
				this.sectorwiseusersList[value.unitid] = [];
				
			}
			this.emptyAuditorDetails(value.ut,value.unitid);
	
			//value.calendar.updateTodaysDate();
		});
		
		this.minDate = new Date();
		this.id = this.activatedRoute.snapshot.queryParams.id;
		this.app_id = this.activatedRoute.snapshot.queryParams.app_id;
		this.invoice_id = this.activatedRoute.snapshot.queryParams.invoice_id;
		this.offer_id = this.activatedRoute.snapshot.queryParams.offer_id;
		this.month[0] = "Jan";
		this.month[1] = "Feb";
		this.month[2] = "Mar";
		this.month[3] = "Apr";
		this.month[4] = "May";
		this.month[5] = "Jun";
		this.month[6] = "Jul";
		this.month[7] = "Aug";
		this.month[8] = "Sep";
		this.month[9] = "Oct";
		this.month[10] = "Nov";
		this.month[11] = "Dec";
		
		/*this.form = this.fb.group({
			id:[''],
			name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z0-9 \'\-+%/&,().-]+$")]],
			description:[''],	  
		});
	*/ 
	this.generateDetail.getOffer({id:this.app_id,offer_id:this.offer_id}).pipe(first())
    .subscribe(res => {
		this.manday = res.manday;
	})
		this.auditPlanService.getAuditPlan({audit_id:this.id,app_id:this.app_id,invoice_id:this.invoice_id,offer_id:this.offer_id}).pipe(first())
    	.subscribe(res => {
			this.auditPlanData = res;	  
			let appLeadAuditors = [];
			if(res['units'] !== undefined && res['units'].length > 0 ){
				res['units'].forEach((units,unitindex) => {
					//this.auditplanlist['ap_original_manday_'+units.unit_id] = units.original_manday;
					//this.auditplanlist['ap_actual_manday_'+units.unit_id] = units.actual_manday;

					//this.auditplanlist['unit_date_'+units.unit_id] = units.from_date;
					
					

					this.appUnitLeadAuditorsEntries[unitindex] = [];

					if(units.unitdates){
						this.appActualMandayData[unitindex] = units.actual_manday;
						this.auditplanlist['lead_auditor_'+units.id] = units.unit_lead_auditor;
						this.auditplanlist['technical_expert_'+units.id] = units.technical_expert?units.technical_expert:"";
						this.auditplanlist['translator_'+units.id] = units.translator?units.translator:"";
						this.auditplanlist['unit_date_'+units.id] = units.unitdates.join(' | ');
						this.auditplanlist['observer_'+units.id] = units.observer;
                        this.auditplanlist['trainee_auditor_'+units.id] = units.trainee_auditor;
					}

					if(units.auditordetails !==undefined && units.auditordetails.length >0){
						let unitAuditorsEntries = [];
						units.auditordetails.forEach((auditor,auditorindex)=>{
							let expobject:any=[];
							expobject["auditor_id"] = auditor.user_id;
							expobject["auditor_name"] = auditor.auditor_name;
							expobject["auditor_dates"] = auditor.auditor_dates;
							expobject["standards_qual"] = auditor.standards_qual;

							unitAuditorsEntries.push(expobject);

							let leadauditorobject:any=[];
							leadauditorobject["id"] = auditor.user_id;
							leadauditorobject["name"] = auditor.auditor_name;
							this.appUnitLeadAuditorsEntries[unitindex].push(leadauditorobject);
						});
						this.appAuditorsEntries[unitindex]=unitAuditorsEntries;

						//this.auditorList['auditor_name_'+unitindex+'_'+units.id] = units.selauditors;
						this.auditorList[units.id] = units.selauditors;
						this.technicalExpertList[units.id] = units.seltechnicalExpert;
						this.translatorList[units.id] = units.seltranslator;

						let unit_lead_auditor = units.unit_lead_auditor;
						let leadauditorindex = this.appUnitLeadAuditorsEntries[unitindex].findIndex(x=> x.id==unit_lead_auditor);
						//appLeadAuditors[]
						if(appLeadAuditors.findIndex(x=> x.id==unit_lead_auditor)<0){
							appLeadAuditors.push({...this.appUnitLeadAuditorsEntries[unitindex][leadauditorindex]});
						}
						


						//this.appUnitLeadAuditorsEntries[unitindex] = [];
						//console.log(units.seltechnicalExpert);
					}
					if(unitindex==0){
						this.setSelectedDate(units.id);
						if(units.unitdates){
							this.getAuditors(unitindex,units.id);	

							this.auditplanlist['lead_auditor_'+units.id] = units.unit_lead_auditor;
							this.auditplanlist['technical_expert_'+units.id] = units.technical_expert?units.technical_expert:"";
							this.auditplanlist['translator_'+units.id] = units.translator?units.translator:"";
							
						
						}
					}

					//this.getAuditors(unitindex,units.id);	
					//this.auditplanlist['ap_todate_'+units.unit_id] = new Date(standard.to_date);
					/*
					units.standard.forEach(standarddata => {
						//this.auditplanlist['ap_fromdate_'+units.unit_id+'_'+standard.standard_id] = new Date(standard.from_date);
						//this.auditplanlist['ap_todate_'+units.unit_id+'_'+standard.standard_id] = new Date(standard.to_date);
						
						this.auditorList[units.unit_id+'_'+standarddata.standard_id] = standarddata.auditorsArr;
						this.leadAuditorList[units.unit_id+'_'+standarddata.standard_id] = standarddata.leadAuditorsArr;
						if( standarddata.auditors !== undefined &&  standarddata.auditors.length>0){
							this.auditplanlist['ap_auditor_'+units.unit_id+'_'+standarddata.standard_id] = standarddata.auditors.map(String);
							this.auditplanlist['ap_leadauditor_'+units.unit_id+'_'+standarddata.standard_id] = standarddata.lead_auditor_id.toString();
						}
						//this.auditplanlist['ap_auditor_'+units.unit_id+'_'+standard.standard_id] = standard.auditors;
						//this.auditplanlist['ap_leadauditor_'+units.unit_id+'_'+standard.standard_id] = standard.lead_auditor_id;

						
						//this.ap_auditor_239_1 = standard.auditors;
						//console.log('ap_auditor_'+units.unit_id+'_'+standard.standard_id+'==='+standard.auditors);						
					})*/

				});
				//console.log(appLeadAuditors);
				this.appLeadAuditors = appLeadAuditors;
				this.application_lead_auditor = res['application_lead_auditor'];
				
			}
			

		},
		error => {
			this.error = error;
			this.loading = false;
		});
		
		/*
		let expobject = {auditor_name: '1111',
                auditor_dates: '2222'}
				
		this.unitAuditorsEntries.push(expobject);
		
		this.appAuditorsEntries[1]=this.unitAuditorsEntries;
		
		let expobject1 = {auditor_name: '1111',
                auditor_dates: '2222'}
				
		this.unitAuditorsEntries.push(expobject1);
		
		this.appAuditorsEntries[0]=this.unitAuditorsEntries;
		*/
		
		//this.appAuditorsEntries[1]=this.unitAuditorsEntries;
		//console.log(this.appAuditorsEntries);
		
		/*
		console.log(this.unitAuditorsEntries);
		
		this.appAuditorsEntries.push(this.unitAuditorsEntries);
		console.log(this.appAuditorsEntries);
		
		console.log(this.unitAuditorsEntries.length);
		*/
					
		//this.auditplanlist['ap_auditor_239_1'] = ['60','76','77'];
		//this.auditplanlist['ap_auditor_238_4'] = [1,2];
	}
	
	closeUnit()
	{
		this.activeUnit=-1;
	}
	
	editUnit(index,unitid)
	{

		let unitdates = [];
		if(this.auditplanlist['unit_date_'+unitid]){
			unitdates = this.auditplanlist['unit_date_'+unitid].split(' | ');
		}


		this.activeUnit=index;
		this.setSelectedDate(unitid);
		let lead_auditor = this.auditplanlist['lead_auditor_'+unitid];
		let technical_expert = this.auditplanlist['technical_expert_'+unitid];
		let translator = this.auditplanlist['translator_'+unitid];
		if(unitdates.length>0){
			this.getAuditors(index,unitid);	
		}

		this.auditplanlist['lead_auditor_'+unitid]=lead_auditor;
		this.auditplanlist['technical_expert_'+unitid]=technical_expert;
		this.auditplanlist['translator_'+unitid]=translator;
	}
	
	daysSelected: any[] = [];
	event: any;
	isSelected = (event: any) => {
		const date =
		event.getFullYear() +
		"-" +
		("00" + (event.getMonth() + 1)).slice(-2) +
		"-" +
		("00" + event.getDate()).slice(-2);
		return this.daysSelected.find(x => x == date) ? "selected" : null;
	};
	
	/*
	myFilter = (d: Date | null): boolean => {
		const day = (d || new Date()).getDay();
		// Prevent Saturday and Sunday from being selected.
		return day !== 0 && day !== 6;
	}
	*/
	month = new Array();
	select(event: any, calendar: any,unitid,ut=0) 
	{		
		/*const date =
		event.getFullYear() +
		"-" +
		("00" + (event.getMonth() + 1)).slice(-2) +
		"-" +
		("00" + event.getDate()).slice(-2);
		*/

		const date =
		event.getFullYear() +
		"-" +
		("00" + (event.getMonth() + 1)).slice(-2) +
		"-" +
		("00" + event.getDate()).slice(-2);
		
		const displaydate =
		this.month[event.getMonth()] +' '+
		("00" + event.getDate()).slice(-2) + ', '+ event.getFullYear();
		//console.log(displaydate);
		const index = this.daysSelected.findIndex(x => x == date);
		let unitdates = [];
		if(this.auditplanlist['unit_date_'+unitid]){
			unitdates = this.auditplanlist['unit_date_'+unitid].split(' | ');
		}
		const dayindex = unitdates.findIndex(x => x == displaydate);
		
		
		//let unitDate = this.auditplanlist['unit_date_'+unitid];
		/*if(unitDate=='' || unitDate==undefined)
		{
			//this.daysSelected=[];
			unitDate = date	
		}else{
			unitDate = unitDate+', '+date
		}
		*/		
		
		//this.auditplanlist['unit_date_'+unitid]=unitDate;		
		
		if (index < 0){ 
			this.daysSelected.push(date);
			unitdates.push(displaydate);
		}else{
			this.daysSelected.splice(index, 1);
			unitdates.splice(dayindex,1);
		}
		this.auditplanlist['unit_date_'+unitid]=unitdates.join(' | ');
		calendar.updateTodaysDate();
		this.userDateUpdate.next({unitdates,calendar,ut,unitid});

		
		/*
		if(unitdates.length>0){
			this.getAuditors(ut,unitid);
		}else{
			this.auditorList[unitid] = [];
			this.technicalExpertList[unitid] = [];
			this.translatorList[unitid] = [];
		}
		this.emptyAuditorDetails(ut,unitid);

		calendar.updateTodaysDate();
		*/
	}
	
	emptyAuditorDetails(unitindex,unitid){
		this.appAuditorsEntries[unitindex]=[];		
		this.appUnitLeadAuditorsEntries[unitindex]=[];
		
		this.auditorlist['auditor_name_'+unitindex+'_'+unitid]='';
		this.auditorlist['auditor_dates_'+unitindex+'_'+unitid]='';
		
		this.resetUnitAuditor(unitindex,unitid);
		//this.remove_temp_auditor(unitid);
		this.unitAuditorIndex=null;

		this.getApplicationLeadAuditorList();
	}

	postDateFormat(arg){
		var date = new Date(arg);
		let day =('0' +date.getDate()).slice(-2);
		let month = ('0' + (date.getMonth() + 1)).slice(-2);
		let year = date.getFullYear();
		return year+'-'+month+'-'+day;
	}
	

	auditorChange(val){
		
		let auditorlist = this.ngForm.control.get("ap_auditor_"+val).value;
		this.leadAuditorList[val] = this.auditorList[val].filter(x=>auditorlist.includes(x.id));
		this.auditplanlist['ap_leadauditor_'+val] = '';		
		//console.log(auditorlist);
	}
	sectorwiseusersList:any = [];
	getAuditors(index,unitid){
		this.loadings['auditor'] = true;
		let val = unitid;
		let unitid_val = val;

		this.auditorList[val] = [];
		this.technicalExpertList[val] = [];
		this.translatorList[val] = [];
		this.sectorwiseusersList[val] = [];
		let sector_group_ids = this.auditPlanData['business_sector_groups_ids'][unitid];
		/*
		console.log(this.auditplanlist['unit_date_'+unitid]);
		return false;
		//let from_date = eval("f.value.ap_fromdate_"+val);
		//let to_date = eval("f.value.ap_todate_"+val);
		let val=0;
		let from_date = this.ngForm.control.get("ap_fromdate_"+val).value;
		let to_date = this.ngForm.control.get("ap_todate_"+val).value;
		//this.auditplanlist['ap_original_manday_238'] = '';
		//this.auditplanlist['ap_todate_'+val] = undefined;
		if(from_date!== undefined && to_date!== undefined && from_date !='' && to_date!=''){
			if(from_date> to_date){
				this.auditplanlist['ap_todate_'+val] = '';
				//console.log('asdasd');
			}
		}
		//console.log(to_date);
		*/

		//this.auditorList[val] = [];
		//this.auditplanlist['ap_auditor_'+val] = [];
		//this.auditplanlist['ap_leadauditor_'+val] = '';
		
		/*this.auditorList[val] = [];
		this.technicalExpertList[val] = [];
		this.translatorList[val] = [];
		*/
		this.auditplanlist['lead_auditor_'+unitid]='';
		this.auditplanlist['technical_expert_'+unitid]='';
		this.auditplanlist['translator_'+unitid]='';
		this.auditplanlist['justified_'+unitid]='';
		
		let unit_auditor_date = this.auditplanlist['unit_date_'+unitid];

		if(unit_auditor_date!== undefined && unit_auditor_date !=''){
			//let from_dateVal = this.postDateFormat(from_date);
			//let to_dateVal = this.postDateFormat(to_date);
			let curauditdates = this.auditplanlist['unit_date_'+unitid].split(' | ');
			let standards = this.auditPlanData.units[index].standards;
			let unitstandards = [];
			standards.forEach(val=>{
				unitstandards.push(val.id);
			})
			this.auditPlanService.getAuditors({sector_group_ids:sector_group_ids,unitstandards:unitstandards,app_id:this.app_id,audit_id:this.id,unitid:unitid,dates:this.auditplanlist['unit_date_'+unitid]})
			.pipe(first()).subscribe(res => {

				if(res.status){





					let resauditors = res.auditors;
					let resexperts = res.technicalExpert;
					let restranslators = res.translator;
					let sectorwiseusers = res.sectorwiseusers;
					
					let business_sector_groups = this.auditPlanData.business_sector_groups_ids[unitid];
					
					

					/*
					// For remiving duplicates starts here 
					let addedusers = this.getAddedUsers(unitid_val,curauditdates);
					if(addedusers && addedusers.selauditors && addedusers.selauditors.length>0){
						addedusers.selauditors.forEach(auditorid=>{
							let audIndex = resauditors.findIndex(s=>s.id==auditorid);
							if(audIndex!==-1){
								audIndex = audIndex;
								//console.log('=='+audIndex);
								resauditors.splice(audIndex,1);
							}

							
							if(business_sector_groups && business_sector_groups.length>0){
								business_sector_groups.forEach(vv=>{
									let business_sector_group_id = vv;
									let sectorIndex = sectorwiseusers.findIndex(s=>s.sectorid==vv);
									let sectorusers = sectorwiseusers[sectorIndex]['userlist'];
									//console.log(sectorusers);
									let audIndex = sectorusers.findIndex(s=>s.id==auditorid);
									if(audIndex!==-1){
										//console.log('--'+audIndex);
										sectorusers.splice(audIndex,1);

										let userlistids = sectorwiseusers[sectorIndex]['userlistIds'];
										userlistids.splice(audIndex,1);
										sectorwiseusers[sectorIndex]['userlistIds'] = userlistids;


										let userlistnames = sectorwiseusers[sectorIndex]['userlistnames'].split(', ');
										userlistnames.splice(audIndex,1);
										sectorwiseusers[sectorIndex]['userlistnames'] = userlistnames.join(', ');
									}
									sectorwiseusers[sectorIndex]['userlist'] = sectorusers;
								})
							}

							
						})
					}
					//console.log(sectorwiseusers);
					if(addedusers && addedusers.selexperts && addedusers.selexperts.length>0){
						addedusers.selexperts.forEach(auditorid=>{
							let audIndex = resexperts.findIndex(s=>s.id==auditorid);
							//audIndex = audIndex+1;
							if(audIndex!==-1){
								resexperts = resexperts.splice(audIndex,1);
							}


							if(business_sector_groups && business_sector_groups.length>0){
								business_sector_groups.forEach(vv=>{
									let business_sector_group_id = vv;
									let sectorIndex = sectorwiseusers.findIndex(s=>s.sectorid==vv);
									let sectorusers = sectorwiseusers[sectorIndex]['userlist'];

									let audIndex = sectorusers.findIndex(s=>s.id==auditorid);
									if(audIndex!==-1){
										sectorusers.splice(audIndex,1);

										let userlistids = sectorwiseusers[sectorIndex]['userlistIds'];
										userlistids.splice(audIndex,1);
										sectorwiseusers[sectorIndex]['userlistIds'] = userlistids;

										let userlistnames = sectorwiseusers[sectorIndex]['userlistnames'].split(', ');
										userlistnames.splice(audIndex,1);
										sectorwiseusers[sectorIndex]['userlistnames'] = userlistnames.join(', ');
									}
									sectorwiseusers[sectorIndex]['userlist'] = sectorusers;
								})
							}
							
						})
					}
					//console.log(sectorwiseusers);
					if(addedusers && addedusers.seltranslators && addedusers.seltranslators.length>0){
						addedusers.seltranslators.forEach(auditorid=>{
							let audIndex = restranslators.findIndex(s=>s.id==auditorid);
							//audIndex = audIndex+1;
							if(audIndex!==-1){
								restranslators = restranslators.splice(audIndex,1);
							}
						})
					}
					// For remiving duplicates ends here 
					*/
					
					// For remiving duplicates starts here 
					/*
					let addedusers = this.getAddedUsers(unitid_val,curauditdates);
					if(addedusers && addedusers.selauditors && addedusers.selauditors.length>0){
						addedusers.selauditors.forEach(auditorid=>{
							let audIndex = resauditors.findIndex(s=>s.id==auditorid);
							if(audIndex!==-1){
								audIndex = audIndex;
								//console.log('=='+audIndex);
								resauditors.splice(audIndex,1);
							}

							
							if(business_sector_groups && business_sector_groups.length>0){
								business_sector_groups.forEach(vv=>{
									let business_sector_group_id = vv;
									let sectorIndex = sectorwiseusers.findIndex(s=>s.sectorid==vv);
									let sectorusers = sectorwiseusers[sectorIndex]['auditorlist'];
									//console.log(sectorusers);
									let audIndex = sectorusers.findIndex(s=>s.id==auditorid);
									if(audIndex!==-1){
										//console.log('--'+audIndex);
										sectorusers.splice(audIndex,1);

										let userlistids = sectorwiseusers[sectorIndex]['auditorlistIds'];
										userlistids.splice(audIndex,1);
										sectorwiseusers[sectorIndex]['auditorlistIds'] = userlistids;


										let userlistnames = sectorwiseusers[sectorIndex]['auditorlistnames'].split(', ');
										userlistnames.splice(audIndex,1);
										sectorwiseusers[sectorIndex]['auditorlistnames'] = userlistnames.join(', ');
									}
									sectorwiseusers[sectorIndex]['auditorlist'] = sectorusers;
								})
							}

							
						})
					}
					//console.log(sectorwiseusers);
					if(addedusers && addedusers.selexperts && addedusers.selexperts.length>0){
						addedusers.selexperts.forEach(auditorid=>{
							let audIndex = resexperts.findIndex(s=>s.id==auditorid);
							//audIndex = audIndex+1;
							if(audIndex!==-1){
								resexperts = resexperts.splice(audIndex,1);
							}


							if(business_sector_groups && business_sector_groups.length>0){
								business_sector_groups.forEach(vv=>{
									let business_sector_group_id = vv;
									let sectorIndex = sectorwiseusers.findIndex(s=>s.sectorid==vv);
									let sectorusers = sectorwiseusers[sectorIndex]['technicalexpertlist'];

									let audIndex = sectorusers.findIndex(s=>s.id==auditorid);
									if(audIndex!==-1){
										sectorusers.splice(audIndex,1);

										let userlistids = sectorwiseusers[sectorIndex]['technicalexpertlistIds'];
										userlistids.splice(audIndex,1);
										sectorwiseusers[sectorIndex]['technicalexpertlistIds'] = userlistids;

										let userlistnames = sectorwiseusers[sectorIndex]['technicalexpertlistnames'].split(', ');
										userlistnames.splice(audIndex,1);
										sectorwiseusers[sectorIndex]['technicalexpertlistnames'] = userlistnames.join(', ');
									}
									sectorwiseusers[sectorIndex]['technicalexpertlist'] = sectorusers;
								})
							}
							
						})
					}
					//console.log(sectorwiseusers);
					if(addedusers && addedusers.seltranslators && addedusers.seltranslators.length>0){
						addedusers.seltranslators.forEach(auditorid=>{
							let audIndex = restranslators.findIndex(s=>s.id==auditorid);
							//audIndex = audIndex+1;
							if(audIndex!==-1){
								restranslators = restranslators.splice(audIndex,1);
							}
						})
					}
					*/
					// For remiving duplicates ends here 
					 





					
					this.auditorList[val] = resauditors;
					this.technicalExpertList[val] = resexperts;
					this.translatorList[val] = restranslators;
					this.sectorwiseusersList[val] = sectorwiseusers;

					
				}else{
					this.error = {summary:res};
				}
				this.loadings['auditor'] = false;
			},
			error => {
				this.error = {summary:error};
				this.loading = false;
			});
		}else{
			this.loadings['auditor'] = false;
		}	
	}





	/*
	getAddedExpert(unitid_val,curauditdates){
		let seltranslator = [];
		this.auditPlanData.units.forEach((value,key,myMap)=> {	
			let unitid = value.id;

			if(unitid !== unitid_val){
			
				let unitSelectedD = [];
				let valexists = 0;
				
				if(this.auditplanlist['unit_date_'+unitid]!='' && this.auditplanlist['unit_date_'+unitid]!=undefined)
				{
					let selectedDte=this.auditplanlist['unit_date_'+unitid].split(',');
					if(selectedDte.length>0)
					{
						selectedDte.forEach((value,key,myMap)=> {					
							//unitSelectedD.push(value);
							if(curauditdates.indexOf(value)!==-1){
								valexists=1;
							}
						});					
					}			
				}
				if(valexists ==1){
					let unitAuditorWithDate = [];
					seltranslator.push(this.auditplanlist['translator_'+unitid]);
				}
			}
		});
		return seltranslator;
	}

	getAddedTranslator(unitid_val,curauditdates){
		let selexperts = [];
		this.auditPlanData.units.forEach((value,key,myMap)=> {	
			let unitid = value.id;

			if(unitid !== unitid_val){
			
				let unitSelectedD = [];
				let valexists = 0;
				
				if(this.auditplanlist['unit_date_'+unitid]!='' && this.auditplanlist['unit_date_'+unitid]!=undefined)
				{
					let selectedDte=this.auditplanlist['unit_date_'+unitid].split(',');
					if(selectedDte.length>0)
					{
						selectedDte.forEach((value,key,myMap)=> {					
							//unitSelectedD.push(value);
							if(curauditdates.indexOf(value)!==-1){
								valexists=1;
							}
						});					
					}			
				}
				if(valexists ==1){
					let unitAuditorWithDate = [];
					selexperts.push(this.auditplanlist['technical_expert_'+unitid]);

				}
			}
		});
		return selexperts;
	}
	*/



	getAddedUsers(unitid_val,curauditdates){

	

		let selauditors = [];
		let seltranslators = [];
		let selexperts = [];
		//console.log('22');

		this.auditPlanData.units.forEach((value,key,myMap)=> {	
			let unitid = value.id;
			
			if(unitid !== unitid_val){
			
				let unitSelectedD = [];
				let valexists = 0;
				
				if(this.auditplanlist['unit_date_'+unitid]!='' && this.auditplanlist['unit_date_'+unitid]!=undefined)
				{
					let selectedDte=this.auditplanlist['unit_date_'+unitid].split(' | ');
					//console.log(curauditdates);
					if(selectedDte.length>0)
					{
						selectedDte.forEach((value,key,myMap)=> {					
							//unitSelectedD.push(value);
							if(curauditdates.indexOf(value)!==-1){
								valexists=1;
							}
						});					
					}			
				}
				//console.log(this.auditplanlist['translator_'+unitid]);
				//console.log(valexists);
				if(valexists == 1){
					let unitAuditorWithDate = [];
					if(this.appAuditorsEntries[key].length>0)
					{
						this.appAuditorsEntries[key].forEach((vl,ke,yMap)=> {
							selauditors.push(vl.auditor_id);
							//let auditor_with_date = {user_id:vl.auditor_id,date:vl.auditor_dates};				
							//unitAuditorWithDate.push(auditor_with_date);	
						});
					}

					//console.log(this.auditplanlist['translator_'+unitid]);
					if(this.auditplanlist['translator_'+unitid])
						seltranslators.push(this.auditplanlist['translator_'+unitid]);

					if(this.auditplanlist['technical_expert_'+unitid])
						selexperts.push(this.auditplanlist['technical_expert_'+unitid]);
				}

			}
		});
		return {selauditors,seltranslators,selexperts};
	}
		//console.log(selauditors);


		
	/*@Output('closed') closedStream: EventEmitter<void> 
	closed(){
		console.log('sdfsdf');
	}
	*/
	getApplicationLeadAuditorList()
	{
		//console.log(this.application_lead_auditor);
		let application_lead_auditor = this.application_lead_auditor;
		this.appLeadAuditors = [];
		this.auditPlanData.units.forEach((value,key,myMap)=> {	
			let unitid = value.id;
			let leadauditor = this.auditplanlist['lead_auditor_'+unitid];
			//console.log(leadauditor);
			if(leadauditor !='' && leadauditor !== undefined){
				const audIndex = this.appLeadAuditors.findIndex(x=>x.id==leadauditor);
				if(audIndex<0){
					let expobject:any=[];
					expobject["id"] = leadauditor;
					expobject["name"] = this.getSelectedValue(leadauditor,unitid)?this.getSelectedValue(leadauditor,unitid).name:'';
					
					this.appLeadAuditors.push(expobject);
				}
			}
		});
		this.application_lead_auditor = application_lead_auditor;
		/*
		let leadAuditorid=this.auditplanlist['lead_auditor_'+unitid];
		
		let checkAlreadySelectedName='';		
		if(this.appLeadAuditors.length>0)
		{
			checkAlreadySelectedName = this.appLeadAuditors.find(x=> x.id==leadAuditorid);			
		}
		console.log(unitid);
		console.log(this.auditorList[unitid]);
		if(this.auditorList[unitid]){
			let expobject:any=[];
			expobject["id"] = leadAuditorid;
			expobject["name"] = this.getSelectedValue(leadAuditorid,unitid).name;		
			const audIndex = this.appLeadAuditors.findIndex(x=>x.id==leadAuditorid);
			if(audIndex<0){
				this.appLeadAuditors.push(expobject);
			}
		}
		*/
	}

	getSelectedValue(val,unitid)
	{
      return this.auditorList[unitid].find(x=> x.id==val);
	}
	
	
	touchUnitAuditor(unitid){
		//this.ngForm.form.get('auditorName').markAsTouched();
		//this.ngForm.form.get('auditorDate').markAsTouched();
		
		this.ngForm.form.controls["auditor_name_"+unitid].markAsTouched();
		this.ngForm.form.controls["auditor_dates_"+unitid].markAsTouched();
	}
	  
	resetUnitAuditor(unitindex,unitid){
		
		/*
		https://stackoverflow.com/questions/48645671/dynamically-adding-required-to-input-in-template-driven-angular-2-form
		
		this.ngForm.form.get('auditorName').setValidators([]);
		this.ngForm.form.get('auditorDate').setValidators([]);
		
		this.ngForm.form.get('auditorName').updateValueAndValidity();
		this.ngForm.form.get('auditorDate').updateValueAndValidity();
		*/		
		
		this.ngForm.form.controls["auditor_name_"+unitid].setValidators([]);
		this.ngForm.form.controls["auditor_dates_"+unitid].setValidators([]);
		
		this.ngForm.form.controls["auditor_name_"+unitid].updateValueAndValidity();
		this.ngForm.form.controls["auditor_dates_"+unitid].updateValueAndValidity();

		this.auditorlist['auditor_name_'+unitindex+'_'+unitid]='';
		this.auditorlist['auditor_dates_'+unitindex+'_'+unitid]='';	

		

		this.unitAuditorIndex=null;		
				
		//this.f.auditor_name.setValidators([]);
		//this.f.auditor_dates.setValidators([]);

		//this.f.auditor_name.updateValueAndValidity();
		//this.f.auditor_dates.updateValueAndValidity();
		
		//this.form.patchValue({
		  //auditor_name: '',
		  //auditor_dates: ''
		//});
		
		//this.unitAuditorIndex=null;
	}
	
	setUnitAuditor(unitindex,unitid)
	{
		//this.form.controls["lead_auditor_"+unitid].setValidators([Validators.required]);
		
		//this.f.auditorlist['auditor_name_'+unitindex+'_'+unitid].setValidators([Validators.required]);
		
		/*
		this.ngForm.form.get('auditorName').setValidators([Validators.required]);		
		this.ngForm.form.get('auditorDate').setValidators([Validators.required]);
		
		this.ngForm.form.get('auditorName').updateValueAndValidity();		
		this.ngForm.form.get('auditorDate').updateValueAndValidity();
		*/
		
		this.ngForm.form.controls["auditor_name_"+unitid].setValidators([Validators.required]);
		this.ngForm.form.controls["auditor_dates_"+unitid].setValidators([Validators.required]);
		
		this.ngForm.form.controls["auditor_name_"+unitid].updateValueAndValidity();
		this.ngForm.form.controls["auditor_dates_"+unitid].updateValueAndValidity();
		
		this.touchUnitAuditor(unitid);
	}
	
		
	unitAuditorIndex=null;
	addUnitAuditor(unitindex,unitid)
	{	
		let unitAuditorsEntries = this.appAuditorsEntries[unitindex];
		if(unitAuditorsEntries === undefined){
			unitAuditorsEntries=[];
		}
		
		let unitLeadAuditorsEntries = this.appUnitLeadAuditorsEntries[unitindex];
		if(unitLeadAuditorsEntries === undefined){
			unitLeadAuditorsEntries=[];
		}
		
		this.setUnitAuditor(unitindex,unitid);
		
		let auditor_id = this.auditorlist['auditor_name_'+unitindex+'_'+unitid];
		let auditor_dates = this.auditorlist['auditor_dates_'+unitindex+'_'+unitid];
		//console.log(auditor_dates);

		if(auditor_id=='' || auditor_id==undefined || auditor_dates=='' || auditor_dates==undefined){
			return false;
		}
					
		let seluser = this.getSelectedValue(auditor_id,unitid);
		let expobject:any=[];
		let standards_qual = [];
		if(!Array.isArray(seluser.standards_qual)){
			standards_qual = seluser.standards_qual.split(',');
		}
		expobject["auditor_id"] = auditor_id;
		expobject["auditor_name"] = seluser.name;
		expobject["auditor_dates"] = auditor_dates;
		expobject["standards_qual"] = standards_qual; 
		
		let auditorindex=  unitAuditorsEntries.findIndex(s => s.auditor_id ==  auditor_id);
		if(auditorindex!==-1){
			unitAuditorsEntries[auditorindex] = expobject;
		}else{
			unitAuditorsEntries.push(expobject);
		}

		this.save_temp_auditor(auditor_id,auditor_dates,unitid);


		let leadauditorobject:any=[];
		leadauditorobject["id"] = auditor_id;
		leadauditorobject["name"] = this.getSelectedValue(auditor_id,unitid).name;	
		
		let leadauditorindex=  unitLeadAuditorsEntries.findIndex(s => s.id ==  auditor_id);

		if(leadauditorindex!==-1){
			unitLeadAuditorsEntries[leadauditorindex] = leadauditorobject;
		}else{
			unitLeadAuditorsEntries.push(leadauditorobject);
		}		
		
		this.appAuditorsEntries[unitindex]=unitAuditorsEntries;		
		this.appUnitLeadAuditorsEntries[unitindex]=unitLeadAuditorsEntries;
		
		this.auditorlist['auditor_name_'+unitindex+'_'+unitid]='';
		this.auditorlist['auditor_dates_'+unitindex+'_'+unitid]='';
		
		this.resetUnitAuditor(unitindex,unitid);
		
		this.unitAuditorIndex=null;
		this.calculateManday(unitindex,unitid);
	}


	// -----------Temp auditor functionality functions code starts here ----------
	save_temp_auditor(auditor_id,auditor_dates,unit_id)
	{
		this.auditPlanService.savetempauditors({audit_id:this.id,app_id:this.app_id,auditor_id:auditor_id,auditor_dates:auditor_dates,unit_id:unit_id})
		.pipe(first()).subscribe(res => {

		});
	}

	remove_temp_auditor(auditor_id,unit_id)
	{
		this.auditPlanService.removetempauditors({audit_id:this.id,app_id:this.app_id,auditor_id:auditor_id,unit_id:unit_id})
		.pipe(first()).subscribe(res => {

		});
	}
	// -----------Temp auditor functionality functions code ends here ----------

	 
	editUnitAuditor(index:number,unitindex,unitid){
		//this.unitAuditorIndex= index;
		
		let unitAuditorsEntries = this.appAuditorsEntries[unitindex];
		if(unitAuditorsEntries === undefined){
			unitAuditorsEntries=[];
		}
		
		let qual = unitAuditorsEntries[index];
		
		this.auditorlist['auditor_name_'+unitindex+'_'+unitid]=qual.auditor_id;
		this.auditorlist['auditor_dates_'+unitindex+'_'+unitid]=qual.auditor_dates;
		//this.unitAuditorIndex=null;
		//this.appAuditorsEntries[unitindex]=unitAuditorsEntries;
	}
	
    removeUnitAuditor(index,unitindex,unitid){
		let unitAuditorsEntries = this.appAuditorsEntries[unitindex];
		if(unitAuditorsEntries === undefined){
			unitAuditorsEntries=[];
		}
		
		let unitLeadAuditorsEntries = this.appUnitLeadAuditorsEntries[unitindex];
		if(unitLeadAuditorsEntries === undefined){
			unitLeadAuditorsEntries=[];
		}
		
		let qual = unitAuditorsEntries[index];
		if(index != -1)
		  unitAuditorsEntries.splice(index,1);
	  
		if(index != -1)
		  unitLeadAuditorsEntries.splice(index,1);
	  
		this.unitAuditorIndex=null;
		
		this.appAuditorsEntries[unitindex]=unitAuditorsEntries;
		this.appUnitLeadAuditorsEntries[unitindex]=unitLeadAuditorsEntries;
		
		
		this.calculateManday(unitindex,unitid);
		
		this.auditplanlist['lead_auditor_'+unitid]='';
		this.getApplicationLeadAuditorList();

		let auditor_id = qual.auditor_id;
		this.remove_temp_auditor(auditor_id,unitid);
	}

	calculateManday(ut,unitid)
	{
		let auditor_actual_manday:any=0;
		this.appAuditorsEntries[ut].forEach((value,key,myMap)=> {
			auditor_actual_manday=parseFloat(auditor_actual_manday)+parseFloat(value.auditor_dates.length);
		});		
		let technical_expert = this.auditplanlist['technical_expert_'+unitid];
		let translator = this.auditplanlist['translator_'+unitid];
		//if()
		let perVal = parseFloat(this.calculatePercentage(auditor_actual_manday));
		if(technical_expert !='' && technical_expert !== undefined){
			auditor_actual_manday = auditor_actual_manday + perVal;
		}
		if(translator !='' && translator !== undefined){
			auditor_actual_manday = auditor_actual_manday + perVal;
		}
		this.appActualMandayData[ut]=auditor_actual_manday.toFixed(2);
	}
 
	calculatePercentage(val:any){
        let mandays = parseFloat(val); 
        let percentage = 20;
        let perc='0';
        if(isNaN(mandays) || isNaN(percentage)){
            perc='0';
        }else{
           	perc = ((mandays/100) * percentage).toFixed(2);
        }
        return perc;
    }
	setSelectedDate(unitid)
	{
		this.daysSelected = [];
		if(this.auditplanlist['unit_date_'+unitid]!='' && this.auditplanlist['unit_date_'+unitid]!=undefined)
		{
			let selectedDte=this.auditplanlist['unit_date_'+unitid].split(' | ');
			if(selectedDte.length>0)
			{
				selectedDte.forEach((value,key,myMap)=> {
					let dates = (''+value+'').trim();

					let datearr = Date.parse(dates);
					let datevals = new Date(datearr);
					
					let moddate = datevals.getFullYear() +
					"-" +
					("00" + (datevals.getMonth() + 1)).slice(-2) +
					"-" +
					("00" + datevals.getDate()).slice(-2);
					
					
					
					this.daysSelected.push(moddate);
				});					
			}
		}	
	}
	
	
  //get f() { return this.NgForm.controls; }
  
   onSubmit(f:NgForm) {
	  // console.log(this.sectorwiseusersList); return false;
	let formerror = false;
	
	/*
	this.auditPlanData.units.forEach((value,key,myMap)=> {	  
		let unitid = value.id;	  
	  
		let unit_dates = eval("f.value.unit_dates_"+unitid);
		let lead_auditor = eval("f.value.lead_auditor_"+unitid);
	 
		let technical_expert = eval("f.value.technical_expert_"+unitid);
		let translator = eval("f.value.translator_"+unitid);	
	  
		if(unit_dates==null || unit_dates ==''){
			f.controls["unit_dates_"+unitid].markAsTouched();
			formerror=true;
		}
	  
		if(lead_auditor==null || lead_auditor ==''){
			f.controls["lead_auditor_"+unitid].markAsTouched();
			formerror=true;
		}
		
		if(technical_expert==null || technical_expert ==''){
			f.controls["technical_expert_"+unitid].markAsTouched();
			formerror=true;
		}
		
		if(translator==null || translator ==''){
			f.controls["translator_"+unitid].markAsTouched();
			formerror=true;
		}	
		
		if(this.appAuditorsEntries[key]==undefined || this.appAuditorsEntries[key].length==0)
		{
			f.controls["auditor_name_"+unitid].setValidators([Validators.required]);
			f.controls["auditor_dates_"+unitid].setValidators([Validators.required]);
			
			f.controls["auditor_name_"+unitid].updateValueAndValidity();
			f.controls["auditor_dates_"+unitid].updateValueAndValidity();
			
			f.controls["auditor_name_"+unitid].markAsTouched();
			f.controls["auditor_dates_"+unitid].markAsTouched();
		}
		
	});
	
	*/
	let errMsg:any = [];
	let application_lead_auditor = eval("f.value.application_lead_auditor");
	if(application_lead_auditor==null || application_lead_auditor ==''){
		f.controls["application_lead_auditor"].markAsTouched();
		formerror=true;
		errMsg.push('<li>Please add Application Lead Auditor</li>');
	}
	
	
	//let unitData;
	let unit_data = [];
	let unitauditorForAllStdErr:any=[];

	let unitDatesErr:any=[];
	let unitAuditorErr:any=[];
	let unitLeadAuditorErr:any = [];
	let unitMandayErr:any = [];
	

	this.auditPlanData.units.forEach((value,key,myMap)=> {	
		let unitid = value.id;
		let unitSelectedD = [];
		if(this.auditplanlist['unit_date_'+unitid]!='' && this.auditplanlist['unit_date_'+unitid]!=undefined)
		{
			let selectedDte=this.auditplanlist['unit_date_'+unitid].split(' | ');
			if(selectedDte.length>0)
			{
				selectedDte.forEach((value,key,myMap)=> {					
					unitSelectedD.push(value);										
				});					
			}			
		}else{
			unitDatesErr.push(value.name);
		}
		
		let unitstandard = value.standards;
		let unitStd = [];
		
		//console.log(this.auditplanlist['technical_expert_'+unitid]);
		//this.technicalExpertList[unitid]
		
		if(unitstandard.length>0)
		{
			unitstandard.forEach((val,ky,mMap)=> {	
				unitStd.push(val.id);

				if(this.appAuditorsEntries[key] && this.appAuditorsEntries[key].length>0)
				{
					let findedIndex = 0;
					//console.log(this.appAuditorsEntries[key]);
					//standards_qual.findIndex(x=>x==val);
					//console.log(this.appAuditorsEntries[key]);
					this.appAuditorsEntries[key].forEach((vl,ke,yMap)=> {
						//console.log(vl.standards_qual);
						let index = vl.standards_qual.findIndex(x=>x==val.id);
						if(index>=0){
							findedIndex = 1;
						}
					});

					let texpert = this.auditplanlist['technical_expert_'+key];
					let texpertdetails =this.technicalExpertList[unitid].find(x=>x.id==texpert);
					if(texpertdetails !==undefined){
						let index = texpertdetails.standards_qual.split(',').findIndex(x=>x==val.id);
						if(index>=0){
							findedIndex = 1;
						}
					}
					if(findedIndex ==0){
						formerror=true;
						unitauditorForAllStdErr.push(value.name);
					}
					
				}
				//console.log(this.technicalExpertList[key]);
			});		
				
		}		
		//return false;

		let unitAuditorWithDate = [];
		if(this.appAuditorsEntries[key] && this.appAuditorsEntries[key].length>0)
		{
			this.appAuditorsEntries[key].forEach((vl,ke,yMap)=> {
				let auditor_with_date = {user_id:vl.auditor_id,date:vl.auditor_dates};				
				unitAuditorWithDate.push(auditor_with_date);	
			});
		}else{
			unitAuditorErr.push(value.name);
		}
		  
		if(this.auditplanlist['lead_auditor_'+unitid]=='' || this.auditplanlist['lead_auditor_'+unitid]===undefined){
			unitLeadAuditorErr.push(value.name);
		}
		
		//console.log(value.quotation_manday+'===='+this.appActualMandayData[key]);
		//console.log(typeof value.quotation_manday+'===='+typeof this.appActualMandayData[key]);
		let quotation_manday:any = parseFloat(value.quotation_manday);
		let actualcalmanday:any = parseFloat(this.appActualMandayData[key]);
		if(this.appActualMandayData[key] =='' || this.appActualMandayData[key]===undefined || parseFloat(quotation_manday)>parseFloat(actualcalmanday)){
			unitMandayErr.push(value.name);
		}

        let unitData = {unit_id:unitid,unit_lead_auditor:this.auditplanlist['lead_auditor_'+unitid],technical_expert:this.auditplanlist['technical_expert_'+unitid],translator:this.auditplanlist['translator_'+unitid],standard:unitStd,date:unitSelectedD,auditor:unitAuditorWithDate,quotation_manday:value.quotation_manday,actual_manday:this.appActualMandayData[key],observer:this.auditplanlist['observer_'+unitid],trainee_auditor:this.auditplanlist['trainee_auditor_'+unitid]};
		unit_data.push(unitData);
    });
	//return false;
	let app_data = {sectorwiseusersList:this.sectorwiseusersList,audit_id:this.id,app_id:this.app_id,offer_id:this.offer_id,invoice_id:this.invoice_id,units:unit_data,application_lead_auditor:f.value.application_lead_auditor};
	
	if(unitauditorForAllStdErr.length>0){
		errMsg.push('<li>Please add Auditors for all Standards for the unit(s):'+unitauditorForAllStdErr.join(', ')+'</li>');
		formerror = true;
	}
	if(unitDatesErr.length>0){
		errMsg.push('<li>Please add dates for the unit(s):'+unitDatesErr.join(', '));
		formerror = true;
	}
	if(unitAuditorErr.length>0){
		errMsg.push('<li>Please add Auditor for the unit(s):'+unitAuditorErr.join(', '));
		formerror = true;
	}
	if(unitLeadAuditorErr.length>0){
		errMsg.push('<li>Please select Lead Auditor for the unit(s):'+unitLeadAuditorErr.join(', '));
		formerror = true;
	}
	/*
	if(unitLeadAuditorErr.length>0){
		errMsg.push('<li>Please select Lead Auditor for the unit(s):'+unitLeadAuditorErr.join(', '));
		formerror = true;
	}
	*/
	if(unitMandayErr.length>0){
		errMsg.push('<li>Actual mandays should be greater than Quotation mandays for the unit(s):'+unitMandayErr.join(', '));
		formerror = true;
	}

	
	

	if (formerror) {
		let res = '<div class="errorSummary">Please fix the following input errors:<ul>'+errMsg.join('')+'</ul>';
		this.error = {summary:res};
	}
	//formerror = true;
	//formerror=true;
	if (!formerror) {
		this.loading = true;
		this.auditPlanService.createAuditPlan(app_data).pipe(first()).subscribe(res => {
			  	
			  if(res.status){
				this.success = {summary:res.message};
				this.buttonDisable = true;
				//setTimeout(()=>this.router.navigate(['/audit/view-audit-plan']),this.errorSummary.redirectTime);
				
				setTimeout(() => {
					this.router.navigateByUrl('/audit/view-audit-plan?id='+res.audit_id); 
				},this.errorSummary.redirectTime);
			
			  }else if(res.status == 0){
				this.error = {summary:res.message};
			  }else{
				this.error = {summary:res};
			  }
			  this.loading = false;
		  },
		  error => {
			  this.error = {summary:error};
			  this.loading = false;
		});
	  
	} 
   
  }

}