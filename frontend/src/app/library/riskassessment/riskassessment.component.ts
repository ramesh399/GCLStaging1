import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';

import { RiskassessmentListService } from '@app/services/library/riskassessment-list.service';
import { User } from '@app/models/master/user';
import {Riskassessment} from '@app/models/library/riskassessment';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';

import { ActivatedRoute,Params,Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {saveAs} from 'file-saver';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-riskassessment',
  templateUrl: './riskassessment.component.html',
  styleUrls: ['./riskassessment.component.scss'],
  providers: [RiskassessmentListService]
})
export class RiskassessmentComponent implements OnInit {

	
  Riskassessment$: Observable<Riskassessment[]>;
  total$: Observable<number>;
  auditplanStatus$: Observable<any>;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;


  form : FormGroup;
  logForm: FormGroup;
  
  buttonDisable = false;
  error:any;
  id:number;
  typelist:any=[];
  logData:any=[];
  viewlogData:any=[];
  statuslist:any=[];
  
  model: any = {id:null,action:null,target_date:'',details:'',updated_id:'',reason_id:'',log_date:''};
  success:any;
  modalss:any;

  formData:FormData = new FormData();
  userType:number;
  userdetails:any;
  userdecoded:any;

  logEntries:any=[];
 	
  franchiseList:User[];

  	arrThreat = [];
	arrProbability = [];
	arrImpact = [];

	arrUpdated = [];
	arrReason = [];


	constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, public userService:UserService,public service: RiskassessmentListService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { 

		this.Riskassessment$ = service.riskassessment$;
		this.total$ = service.total$;
  }
  
  getSelectedFranchiseValue(val)
	{
		return this.franchiseList.find(x=> x.id==val).osp_details;    
  }

  
  ngOnInit() {

    this.form = this.fb.group({
      franchise_id:['',[Validators.required]],
      threat_id:['',[Validators.required]],
      vulnerability:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
      probability:['',[Validators.required]],	 	  
      impact:['',[Validators.required]],	
      controls:['',[Validators.required,this.errorSummary.noWhitespaceValidator]]
    });
    /* date:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]], */
    this.logForm = this.fb.group({
       updated_id:['',[Validators.required]],
       log_date:['',[Validators.required]],
       target_date:['',[Validators.required]],
       reason_id:['',[Validators.required]],
       details:['',[Validators.required,this.errorSummary.noWhitespaceValidator]]
    });

    this.service.getStatusList().pipe(first())
    .subscribe(res => {
      this.arrThreat  = res.arrThreat;
      this.arrProbability  = res.arrProbability;
      this.arrImpact  = res.arrImpact;

      this.arrUpdated  = res.arrUpdated;
      this.arrReason  = res.arrReason;
      
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
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

	this.userService.getAllUser({type:3,filteruser:1}).pipe(first())
    .subscribe(res => {
      this.franchiseList = res.users;
      if(this.franchiseList.length==1){
        this.form.patchValue({
          franchise_id:this.franchiseList[0].id
        })
      }
      
    },
    error => {
        this.error = {summary:error};
    });

  }

  get f() { return this.form.controls; } 
  get sf() { return this.logForm.controls; }  

  open(content,arg='') {

    
  }

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
 
  view(content,data)
  {
    this.logData = data;
    this.getLogViewData(data.id);
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
    
    this.modalss.result.then((result) => {
      this.logviewEntries = [];
    }, (reason) => {
        this.logviewEntries = [];
    });
  }

  viewLog(content,data)
  {
    this.viewlogData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  listEntries = [];
  dataIndex:number=null;
  loading:any=[];
  addData()
  {
    this.f.franchise_id.markAsTouched();
    this.f.threat_id.markAsTouched();
    this.f.vulnerability.markAsTouched();
    this.f.probability.markAsTouched();
    this.f.impact.markAsTouched();
    this.f.controls.markAsTouched();

     
    if(this.form.valid )
    {
      this.buttonDisable = true;
      this.loading['button'] = true;
      //let received_date = this.errorSummary.displayDateFormat(this.form.get('received_date').value);
      //this.form.get('received_date').value;

      let franchise_id = this.form.get('franchise_id').value;
      let threat_id = this.form.get('threat_id').value;
      let vulnerability = this.form.get('vulnerability').value;
      let probability = this.form.get('probability').value;
      let impact = this.form.get('impact').value;
      let controls = this.form.get('controls').value;

      let expobject:any= {franchise_id,threat_id,vulnerability,probability,impact,controls};
      
      if(1)
      {
      //console.log('1');
         

        if(this.riskData){
          expobject.id = this.riskData.id;
        }
        
        //this.formData.append('formvalues',JSON.stringify(expobject));

        this.service.addData(expobject)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              
              this.service.customSearch();
              this.formreset();
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
  }
  resetdata(){
	this.log_date_error =false;
    this.target_date_error =false;
    this.updated_id_error =false;
    this.reason_id_error = false; 
    this.details_error = false; 
	
	this.editStatus=0;  
	this.editLogStatus=0;
    this.form.reset();
    this.riskData = '';
    this.formData = new FormData();

	this.form.patchValue({
      franchise_id:'',
      threat_id:'',      
      probability:'',
      impact:''      
    });	
   
  }

  formreset()
  {
	this.log_date_error =false;
    this.target_date_error =false;
    this.updated_id_error =false;
    this.reason_id_error = false; 
    this.details_error = false; 
	
	this.editStatus=0;
	this.editLogStatus=0;
  	this.riskData = '';
    this.logviewEntries = [];
    this.form.reset();
	
	this.form.patchValue({
      franchise_id:'',
      threat_id:'',      
      probability:'',
      impact:''      
    });
  }
 	

  addlog(content)
  {
	this.logdata = '';
	
	this.logForm.reset();
	
	/*	
	this.logForm.patchValue({
      updated_id:'',
      log_date:'',      
      target_date:'',
	  reason_id:'',
	  details:''
    });
	*/
	
	this.editLogStatus=0;
	/*
    this.model.updated_id = '';
    this.model.log_date ='';
    this.model.target_date ='';
    this.model.reason_id = '';
    this.model.details ='';
	*/
    this.logdata = '';

    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  logviewEntries:any=[];
  getLogViewData(dataid){
    this.logviewEntries = [];
    this.loading['logviewdata'] =true;
    this.service.getLogData({data_id:dataid})
    .pipe(first())
    .subscribe(res => {

      this.loading['logviewdata'] =false;
      if(res.status){
        this.logviewEntries = res['data'];
      }else if(res.status == 0){
        this.error = {summary:res};
      }       
    },
    error => {
        this.error = {summary:error};
        this.loading['logviewdata'] =false;
    });
  }


  getLogData(dataid){
    this.logEntries = [];
    this.loading['logdata'] =true;
    this.service.getLogData({data_id:dataid})
    .pipe(first())
    .subscribe(res => {

      this.loading['logdata'] =false;
      if(res.status){
        this.logEntries = res['data'];
      }else if(res.status == 0){
        this.error = {summary:res};
      }       
    },
    error => {
        this.error = {summary:error};
        this.loading['logdata'] =false;
    });
  }


  logIndex=null;
  removeLog(content,logdata) {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.service.deleteLogData({id:logdata.id})
        .pipe(first())
        .subscribe(res => {
       
            if(res.status){
              this.getLogData(this.riskData.id);
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
  
  editLogStatus=0;
  logdata:any;
  editLog(content,index:number,logdata) {
	this.log_date_error =false;
    this.target_date_error =false;
    this.updated_id_error =false;
    this.reason_id_error = false; 
    this.details_error = false; 
	
	this.editLogStatus=1;  
    this.logsuccess = '';
    this.logdata = logdata;
	
	/*
    this.model.log_date = this.errorSummary.editDateFormat(logdata.log_date);
    this.model.target_date = this.errorSummary.editDateFormat(logdata.target_date);
    this.model.updated_id =logdata.updated_id;
    this.model.reason_id =logdata.reason_id;
    this.model.details =logdata.details;
	*/
	
	this.logForm.patchValue({
      updated_id:logdata.updated_id,
      log_date:this.errorSummary.editDateFormat(logdata.log_date),      
      target_date:this.errorSummary.editDateFormat(logdata.target_date),
	  reason_id:logdata.reason_id,
	  details:logdata.details
    });
	
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  date_error:any;
  type_error:any;
  description_error:any;
  logsuccess:any;
  logerror:any;
  logloading:any;

  log_date_error:any;
  target_date_error:any;
  updated_id_error:any;
  reason_id_error:any;
  details_error:any;

  submitLogAction(){
    
	this.sf.log_date.markAsTouched();
	this.sf.target_date.markAsTouched();
	this.sf.updated_id.markAsTouched();
	this.sf.reason_id.markAsTouched();
	this.sf.details.markAsTouched();
	
    this.log_date_error =false;
    this.target_date_error =false;
    this.updated_id_error =false;
    this.reason_id_error = false; 
    this.details_error = false; 
	
	/*
    let formerror= false;
    if(this.model.log_date ==''){
      this.log_date_error =true;
      formerror = true;
    }
    if(this.model.target_date ==''){	
      this.target_date_error =true;
      formerror = true;
    }
    if(this.model.updated_id ==''){
      this.updated_id_error =true;
      formerror = true;
    }
    if(this.model.reason_id ==''){
      this.reason_id_error =true;
      formerror = true;
    }
    console.log(this.model.details);
    if(this.model.details ==''){
      this.details_error =true;
      formerror = true;
    }

    if(formerror){
      return false;
    }else{
	*/
	if(this.logForm.valid)
	{
	  let log_date = this.errorSummary.displayDateFormat(this.logForm.get('log_date').value);
	  let target_date = this.errorSummary.displayDateFormat(this.logForm.get('target_date').value);
      let updated_id = this.logForm.get('updated_id').value;     
      let reason_id = this.logForm.get('reason_id').value;
	  let details = this.logForm.get('details').value;
	  
      let datalog:any = {data_id:this.riskData.id,log_date:log_date,target_date:target_date,updated_id:updated_id,reason_id:reason_id,details:details};
      if(this.logdata){
        datalog.id = this.logdata.id;
      }

      
      this.loading['logsbutton'] = true;
      this.service.addLogData(datalog)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.getLogData(this.riskData.id);
            this.logsuccess = res.message;
            setTimeout(() => {
              this.logsuccess = '';
              this.logdata = '';
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


  removeData(content,data) {

      this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

      this.modalss.result.then((result) => {
      		this.formreset();
          this.service.deleteData({id:data.id})
          .pipe(first())
          .subscribe(res => {

              if(res.status){
                this.service.customSearch();
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
      })
      
    
  }

  editStatus=0;
  riskData:any;
  edit(index:number,data) {
	this.editStatus=1;
    this.formData = new FormData(); 
    this.riskData = data;
    
    // this.errorSummary.editDateFormat(data.received_date)
    this.form.patchValue({
      franchise_id:data.franchise_id,
      threat_id:data.threat_id,
      vulnerability:data.vulnerability,
      probability:data.probability,
      impact:data.impact,
      risk_value:data.risk_value,
      controls:data.controls
    });
    this.getLogData(data.id);
    this.scrollToBottom();
  }

  scrollToBottom()
  {
    window.scroll({ 
      top: window.innerHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }

  onSubmit(){ }

}
