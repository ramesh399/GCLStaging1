import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { Legislation } from '@app/models/library/legislation';
import {LegislationListService} from '@app/services/library/legislation/legislation-list.service';
import {LegislationService} from '@app/services/library/legislation/legislation.service';
import { User } from '@app/models/master/user';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Country } from '@app/services/country';
import { CountryService } from '@app/services/country.service';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';

@Component({
  selector: 'app-legislation',
  templateUrl: './legislation.component.html',
  styleUrls: ['./legislation.component.scss'],
  providers: [LegislationListService]
})
export class LegislationComponent implements OnInit {

  title = 'Legislation'; 
  form : FormGroup; 
  legislations$: Observable<Legislation[]>;
  total$: Observable<number>;
  id:number;
  legislationData:any;
  LegislationData:any;
  error:any;
  success:any;
  buttonDisable = false;
  model: any = {status:'',changed_id:'',details:''};
  franchiseList:User[];
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  legislationEntries:any=[];
  modalss:any;
  countryList:Country[];
  standardList:Standard[];
  logEntries:any=[];
  arrLogstatus:any=[];
  arrChanged:any=[];
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal,private countryservice: CountryService,private standardservice: StandardService,private activatedRoute:ActivatedRoute, private userservice: UserService, private router: Router,private fb:FormBuilder, public userService:UserService,public service: LegislationListService, private legislationService: LegislationService,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
    this.legislations$ = service.legislations$;
    this.total$ = service.total$;
  }

  getSelectedValue(type,val)
  {
    if(type=='country_id'){
      return this.countryList.find(x=> x.id==val).name;
    }if(type=='relevant_to_id'){
      return this.standardList.find(x=> x.id==val).name;
    }
  }
  /*
  get relevantstandards(): FormArray { 
    return this.form.get('relevantstandards') as FormArray; 
 }
 */
  ngOnInit() 
  {
    this.standardservice.getStandard().subscribe(res => {
		  this.standardList = res['standards'];
    });
	
	  this.countryservice.getCountry().subscribe(res => {
      this.countryList = res['countries'];
    });

    this.userservice.getAllUser({type:3}).pipe(first())
    .subscribe(res => {
      this.franchiseList = res.users;
    },
    error => {
        this.error = {summary:error};
    });

	  this.service.getStatusList().pipe(first())
    .subscribe(res => {
      this.arrLogstatus  = res.arrStatus;
      this.arrChanged  = res.arrChanged;
      
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });


	  this.form = this.fb.group({	      
	  title:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      description:['',[this.errorSummary.noWhitespaceValidator]],  
      country_id:['',[Validators.required]],
      relevant_to_id:['',[Validators.required]],
      update_method_id:['',[Validators.required]],
	    
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

    // relevantstandards: ['',[Validators.required]],
	 //new FormArray([]),
  }
  
  /*
  get relevantstandards() : FormArray {
	return this.form.get("relevantstandards") as FormArray
  }
  */
  
  createRelevantstandards(): FormGroup {
	return this.fb.group({
		relevant: ''		
	});
  }

  get f() { return this.form.controls; } 

  legislationListEntries = [];
  legislationIndex:number=null;
  loading:any=[];
  addlegislation()
  {

    

    this.f.title.markAsTouched();
    this.f.relevant_to_id.markAsTouched();
    this.f.update_method_id.markAsTouched();
    //this.f.description.markAsTouched();
    this.f.country_id.markAsTouched();
    
    if(this.form.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] = true;
      let title = this.form.get('title').value;
      let description = this.form.get('description').value;
      let country_id = this.form.get('country_id').value;
      let relevant_to_id = this.form.get('relevant_to_id').value;
      let update_method_id = this.form.get('update_method_id').value;
      /*
      for(let i = 0; i < this.relevantstandards.length; i++) {
       // console.log(this.relevantstandards.at(i).value);
      } 
      */

      let expobject:any={title:title,description:description,relevant_to_id:relevant_to_id,update_method_id:update_method_id,country_id:country_id};
      
      if(1)
      {
        if(this.legislationData){
          expobject.id = this.legislationData.id;
        }
        
       // this.formData.append('formvalues',JSON.stringify(expobject));
        this.service.addData(expobject)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              
              this.service.customSearch();
              this.legislationFormreset();
              this.success = {summary:res.message};
              this.buttonDisable = false;
              
              /*
              setTimeout(() => {
                
              },this.errorSummary.redirectTime);
              */
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
            this.buttonDisable = false;
        });
        
      } else {
        
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.form); 
        
      }   
    }
  }
  viewlogData:any=[];
  viewLog(content,data)
  {
    this.viewlogData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  legislationviewEntries:any=[];
  getLegislationViewData(legisid){
    this.legislationviewEntries = [];
    this.loading['logviewdata'] =true;
    this.service.getLogData({data_id:legisid})
    .pipe(first())
    .subscribe(res => {

      this.loading['logviewdata'] =false;
      if(res.status){
        this.legislationviewEntries = res['data'];
      }else if(res.status == 0){
        this.error = {summary:res};
      }       
    },
    error => {
        this.error = {summary:error};
        this.loading['logviewdata'] =false;
    });
  }

  viewLegislation(content,data)
  {
    this.LegislationData = data;
    this.getLegislationViewData(data.id);
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  editStatus=0;
  editLegislation(index:number,legislationdata) 
  { 
    this.editStatus=1;
    this.legislationData = legislationdata;
    
    this.form.patchValue({
      title:legislationdata.title,
      description:legislationdata.description,     
      country_id:legislationdata.country_id,
      relevant_to_id:legislationdata.relevant_to_id,
      update_method_id:legislationdata.update_method_id
    });
    this.getLogData(legislationdata.id);
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
  removeLegislation(content,index:number,legislationdata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.legislationFormreset();
        this.service.deleteLegislationData({id:legislationdata.id})
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

  legislationFormreset()
  {
	this.editLogStatus=0;
	this.editStatus=0;
    this.form.reset();
    this.form.patchValue({
      
      country_id:'',
      
      update_method_id:''
    });
    this.legislationData = '';
    this.logviewEntries = [];
  }


  addlog(content)
  {
	this.editLogStatus=0;
    this.model.status = '';
    this.model.changed_id ='';
    this.model.details ='';
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
              this.getLogData(this.legislationData.id);
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
	this.editLogStatus=1;
    this.logsuccess = '';
    this.logdata = logdata;
    this.model.status = logdata.status;
    this.model.changed_id =  logdata.changed_id;
    this.model.details =logdata.details;
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  
  logsuccess:any;
  logerror:any;
  logloading:any;

  status_error:any;
  changed_id_error:any;
  details_error:any;

  submitLogAction(){
    
    this.status_error =false;
    this.changed_id_error =false;
    this.details_error =false;
     
    let formerror= false;
    if(this.model.status ==''){
      this.status_error =true;
      formerror = true;
    }
    if(this.model.changed_id ==''){
      this.changed_id_error =true;
      formerror = true;
    }
    
    if(this.model.details ==''){
      this.details_error =true;
      formerror = true;
    }

    if(formerror){
      return false;
    }else{
      let datalog:any = {data_id:this.legislationData.id,status:this.model.status,changed_id:this.model.changed_id,details:this.model.details};
      if(this.logdata){
        datalog.id = this.logdata.id;
      }

      
      this.loading['logsbutton'] = true;
      this.service.addLogData(datalog)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.getLogData(this.legislationData.id);
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

  onSubmit(){ }

}
