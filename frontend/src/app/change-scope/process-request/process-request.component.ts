import { Component, OnInit } from '@angular/core';


import { ActivatedRoute ,Params, Router } from '@angular/router';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { Application } from '@app/models/application/application';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import { AuthenticationService } from '@app/services';
import {Observable,Subject} from 'rxjs';
import { first, debounceTime, distinctUntilChanged, map,tap } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {saveAs} from 'file-saver';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Process } from '@app/models/master/process';
import { NgForm } from '@angular/forms';
import { ProcessService } from '@app/services/master/process/process.service';
import { ProcessAdditionService } from '@app/services/change-scope/process-addition.service';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';

@Component({
  selector: 'app-process-request',
  templateUrl: './process-request.component.html',
  styleUrls: ['./process-request.component.scss']
})
export class ProcessRequestComponent implements OnInit {

   constructor(private enquiryDetail:EnquiryDetailService, private userservice: UserService,private activatedRoute:ActivatedRoute,
    private applicationDetail:ApplicationDetailService, private modalService: NgbModal
    ,private router:Router,private authservice:AuthenticationService,private errorSummary: ErrorSummaryService, private processService:ProcessService,private additionservice: ProcessAdditionService) { 
    
     
    }
    appform : any = {};
  processList: Process[];
  unitprocessList:any = [];
  process_ids=[];
  userdecoded:any;
  id:number;
  app_id:number;
  new_app_id:number;
  error:any;
  success:any;
  loading:any=[];
  applicationdata:Application;
  panelOpenState = true;
  approvalStatusList = [];//[{id:'1',name:'Accept'},{id:'2',name:'Reject'}];
  userList:User[];
  modalss:any;
  processEntries:any=[];
  processerror:any=[];
  units:any;
  model:any = {process_ids:'',user_id:'',approver_user_id:'',status:'',comment:'',reject_comment:''};

  userType:number;
  userdetails:any;
  arrEnumStatus:any[];
  
  redirecttype:any;
  ngOnInit() {
  	this.app_id = this.activatedRoute.snapshot.queryParams.app;
  	this.units = this.activatedRoute.snapshot.queryParams.units;
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.new_app_id = this.activatedRoute.snapshot.queryParams.new_app_id;
    this.redirecttype = this.activatedRoute.snapshot.queryParams.redirecttype;

  	this.loading['data'] = true;
  	this.additionservice.getApplication({id:this.id,app_id:this.app_id,units:this.units,exclude:1})
    .pipe( tap(res=>{
         
         this.processService.getProcessList().pipe(first()).subscribe(resp => {
	      	this.processList = resp['processes'];   
	        this.applicationdata.units.forEach(unit=>{
	        	//let pl = this.processList.filter(x=>selprocess.includes(x.id));
		      	this.unitprocessList[unit.id] = [...this.processList];


		      	if(unit.process_ids && unit.process_ids.length>0){
		      		unit.process_ids.forEach(existunit=>{
		      			let index= this.unitprocessList[unit.id].findIndex(s => s.id ==  existunit.id);
					    if(index != -1)
					      this.unitprocessList[unit.id].splice(index,1);
		      		})
		      	}
		      	


		    })
	    });
        
      })
      ,first())
    .subscribe(res => {
    	this.loading['data'] = false;
      this.applicationdata = res['appdata'];
      this.applicationdata.units.forEach(unit=>{
        this.processEntries[unit.id] = [];
      })

      if(this.id && res['unitsprocessdetails']){
       
        this.applicationdata.units.forEach(unit=>{
          this.processEntries[unit.id] = res['unitsprocessdetails'][unit.id];
        })
      }
      //this.approvalStatusList = res['approvalStatusList'];
      //this.arrEnumStatus = res['arrEnumStatus'];

      
      
    },
    error => {
        this.error = {summary:error};
        this.loading['data'] = false;
    });


   

  }
  removeProcess(processId:number,unitid) {
    let index= this.processEntries[unitid].findIndex(s => s.id ==  processId);
    if(index != -1)
      this.processEntries[unitid].splice(index,1);
  }
  processErrors:any;
  addProcess(unit_id){
  	let selprocess;
    let processId = this.process_ids['qtd_'+unit_id]; //this.enquiryForm.get('sel_process').value;
    if(processId!='')
    	selprocess = this.processList.find(s => s.id ==  processId);
    
    this.processErrors = '';
    
    
    if(selprocess=== undefined){
      return false;
    }
    
    let entry:any;
    if(this.processEntries[unit_id]){
      entry = this.processEntries[unit_id].find(s => s.id ==  processId);
    }else{
      this.processEntries[unit_id] = [];
    }
    if(entry === undefined){
      let expobject:any=[];
      expobject["id"] = selprocess.id;
      expobject["name"] = selprocess.name;//this.registrationForm.get('expname').value;
      this.processEntries[unit_id].push(expobject);
      this.processerror[unit_id] = '';
    }
    
    this.process_ids['qtd_'+unit_id] = '';
  }

  downloadCompanyFile(filename){
    this.enquiryDetail.downloadCompanyFile({id:this.app_id})
    .subscribe(res => {
      
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
      this.modalss.close('');
    });
  }

  open(content) {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  
  buttonDisable=false;
  onSubmit(type)
  {


  	let formerror=false;
  	this.applicationdata.units.forEach(unit=>{
    	let unitprocessentries = this.processEntries[unit.id];
    	if(unitprocessentries.length<=0){
    		this.processerror[unit.id] = 'true';
    		formerror=true;
    	}else{
    		this.processerror[unit.id] = '';
    	}
    });
    if(formerror){
    	 this.error = {summary:this.errorSummary.errorSummaryText}
    	return false;
    }

    if (1) {
      

 
    this.buttonDisable = true;       
       this.loading['button'] = true;
      let reqobj:any={};
      reqobj['units'] = [];
      this.applicationdata.units.forEach(unit=>{
			let unitprocessentries=[];
			
			this.processEntries[unit.id].forEach((val)=>{
		        unitprocessentries.push(val.id); 
		    });
			reqobj['units'].push({unit_id:unit.id,process:unitprocessentries});
		});

      
      
      reqobj['app_id'] = this.app_id;
      reqobj['new_app_id'] = this.new_app_id;      
      reqobj['type'] = type;
      reqobj['id'] = this.id;
      
      this.additionservice.updateAppProcessData(reqobj)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            
            this.success = {summary:res.message};
                     
            setTimeout(() => {
              if(type == 'draft'){

                //this.router.navigateByUrl('/change-scope/process-addition/list'); 
                //this.router.navigateByUrl('/change-scope/process-addition/list'); 
                if(this.redirecttype!='process' && this.new_app_id){
                  this.buttonDisable = true;  
                  this.router.navigateByUrl('/application/apps/view?id='+this.new_app_id); 
                }
                 this.buttonDisable = false;
              }else{
                this.router.navigateByUrl('/application/apps/view?id='+res.new_app_id); 
              }
              
            }, this.errorSummary.redirectTime);           
          }else{
          this.buttonDisable = false;
            //this.submittedError =1;
            this.error = {summary:res};
          }
          this.loading['button'] = false;
         
      },
      error => {
          this.error = {summary:error};
          this.loading['button'] = false;
          this.buttonDisable = false;
      });
      

    }else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      //this.errorSummary.validateAllFormFields(this.form); 
      
    }
  }
}
