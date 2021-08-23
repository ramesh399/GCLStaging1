import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { MeetingService } from '@app/services/library/meeting/meeting.service';
import { MeetingListService } from '@app/services/library/meeting/meeting-list.service';
import { User } from '@app/models/master/user';
import { tap,first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {Meeting} from '@app/models/library/meeting';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';


@Component({
  selector: 'app-meeting',
  templateUrl: './meeting.component.html',
  styleUrls: ['./meeting.component.scss'],
  providers: [MeetingListService]
})
export class MeetingComponent implements OnInit {

  title = 'Meeting'; 
  form : FormGroup; 
  minuteForm: FormGroup;
  minutelogForm: FormGroup;
  Meetings$: Observable<Meeting[]>;
  total$: Observable<number>;
  id:number;
  meetingData:any;
  meetingViewData:any;
  error:any;
  popupbtnDisable:any;
  success:any;
  typelist:any=[];
  statuslist:any=[];
  raisedlist:any=[];
  classlist:any=[];
  buttonDisable = false;
  model: any = {franchise_id:null};
  franchiseList:User[];
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  minutelogEntries:any=[];
  meetingEntries:any=[];
  minuteEntries:any=[];
  modalss:any;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;


  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private userservice: UserService, private router: Router,private fb:FormBuilder, public userService:UserService,public service: MeetingListService, private meetingService: MeetingService,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { 
    this.Meetings$ = service.meetings$;
    this.total$ = service.total$;
  }
  ngOnInit() {

    this.form = this.fb.group({
      attendees:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
      type:['',[Validators.required]],  
      apologies:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
      location:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]],  	 	  
      meeting_date:['',[Validators.required]]
    });

    this.minuteForm = this.fb.group({
      minute_date:['',[Validators.required]],
      raised_id:['',[Validators.required]],
      class:['',[Validators.required]],
      status:['',[Validators.required]],
      details:['',[this.errorSummary.noWhitespaceValidator]]
    });
	
    this.minutelogForm = this.fb.group({
      log_date:['',[Validators.required]],
      status:['',[Validators.required]],
      description:['',[Validators.required,this.errorSummary.noWhitespaceValidator]]
    });

    this.meetingService.getMeetingStatusList().pipe(first())
    .subscribe(res => {
      //this.meetingEntries = res.meetings;   
      this.typelist  = res.typelist;
      this.statuslist  = res.statuslist;
      this.raisedlist  = res.raisedlist;
      this.classlist  = res.classlist;
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

  }
  
  get f() { return this.form.controls; } 
  get mf() { return this.minuteForm.controls; } 
  get mlf() { return this.minutelogForm.controls; } 

  addMinute(content)
  {
    /*
	this.model.minute_date = '';
    this.model.details ='';
    this.model.raised_id ='';
    this.model.class ='';
    this.model.status ='';
	*/
	
	this.editMinuteStatus=0;
	
	this.minutedata = '';
	
	this.minuteForm.reset();
	
	/*
	this.minuteForm.patchValue({
      minute_date:'',
      raised_id:'',      
      class:'',
	  status:'',
	  details:''
    });
	*/
	
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  meetingListEntries = [];
  meetingIndex:number=null;
  loading:any=[];
  addMeeting()
  {
    this.f.attendees.markAsTouched();
    this.f.type.markAsTouched();
    this.f.apologies.markAsTouched();
    this.f.location.markAsTouched();
    this.f.meeting_date.markAsTouched();
    
    if(this.form.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let meeting_date = this.errorSummary.displayDateFormat(this.form.get('meeting_date').value);
      let attendees = this.form.get('attendees').value;
      let type = this.form.get('type').value;
      let apologies = this.form.get('apologies').value;
      let location = this.form.get('location').value;
      //let meeting_date = this.form.get('meeting_date').value;

      let expobject:any={attendees:attendees,type:type,apologies:apologies,location:location,meeting_date:meeting_date};

      if(1)
      {

        if(this.meetingData){
          expobject.id = this.meetingData.id;
        }
       
        this.service.addData(expobject)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              this.raisedlist  = res.raisedlist;
              
              this.service.customSearch();
              this.meetingFormreset();
              this.success = {summary:res.message};
              this.buttonDisable = false;
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
        
      } else {
        
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.form); 
        
      }   
    }
  }

  editStatus=0;
  editMeeting(index:number,meetingdata) {
    this.minute_date_error = false;
    this.details_error = false;
    this.raised_id_error = false;
    this.class_error = false;
    this.status_error = false;
	
	  this.editStatus=1;
    // this.formData = new FormData(); 
     this.meetingData = meetingdata;
     
     this.form.patchValue({
       attendees:meetingdata.attendees,
       type:meetingdata.type,     
       apologies:meetingdata.apologies,
       location:meetingdata.location,
       meeting_date:this.errorSummary.editDateFormat(meetingdata.meeting_date)
     });

     this.getminuteData(meetingdata.id);
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

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  generatePdfMeeting(meetingdata) 
  {
        this.service.generatePDF({id:meetingdata.id})
        .pipe(first())
        .subscribe(res => {
          this.loading = false;
          this.modalss.close();
          saveAs(new Blob([res],{type:'application/pdf'}),'Meeting_'+meetingdata.id+'.pdf');
        },
        error => {
          this.error = error;
          this.loading = false;
          this.modalss.close();
        });
  }

  removeMeeting(content,meetingdata) {

    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.meetingFormreset();
        this.service.deleteMeetingData({id:meetingdata.id})
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


  viewMeeting(content,data)
  {
    this.meetingViewData = data;
    this.getMinuteViewData(data.id);
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
    this.modalss.result.then((result) => {
      this.minuteviewEntries = [];
    }, (reason) => {
        this.minuteviewEntries = [];
    });
  }

  minuteviewEntries:any=[];
  getMinuteViewData(minutedataid){
    this.minuteviewEntries = [];
    this.loading['minuteviewdata'] =true;
    this.service.getMinuteData({meeting_id:minutedataid})
    .pipe(first())
    .subscribe(res => {

      this.loading['minuteviewdata'] =false;
      if(res.status){
        this.minuteviewEntries = res['data'];
      }else if(res.status == 0){
        this.error = {summary:res};
      }       
    },
    error => {
        this.error = {summary:error};
        this.loading['minuteviewdata'] =false;
    });
  }

  getminuteData(meetingdataid){
    this.minuteEntries = [];
    this.loading['minutedata'] =true;
    this.service.getMinuteData({meeting_id:meetingdataid})
    .pipe(first())
    .subscribe(res => {

      this.loading['minutedata'] =false;
      if(res.status){
        this.minuteEntries = res['data'];
      }else if(res.status == 0){
        this.error = {summary:res};
      }       
    },
    error => {
        this.error = {summary:error};
        this.loading['minutedata'] =false;
    });
  }


  getminutelogData(minutedataid){
    this.minutelogEntries = [];
    this.minutelogData = [];
    this.loading['minutelogdata'] =true;
    this.service.getMinutelogData({minute_id:minutedataid})
    .pipe(first())
    .subscribe(res => {

      this.loading['minutelogdata'] =false;
      if(res.status){
        this.minutelogData = res['data'];
      }else if(res.status == 0){
        this.error = {summary:res};
      }       
    },
    error => {
        this.error = {summary:error};
        this.loading['minutelogdata'] =false;
    });
  }

  viewminuteData:any=[];
  viewMinute(content,data)
  {
    this.viewminuteData = data;
    this.getminutelogData(data.id);
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  minutelogData:any=[];
  minutelogid:any;
  Minutelogs(content,data)
  {
	//this.Logdata = '';
	this.editLogStatus=0;
    this.Logdata = '';
    //this.model.log_date = '';
    //this.model.description ='';
    //this.model.status ='';
	
	this.minutelogForm.reset();
	
	/*
	this.minutelogForm.patchValue({
	  log_date:'',
	  description:'',     
	  status:''
	});
	*/
	
    //this.minutelogData = data.log_data;
    this.getminutelogData(data.id);
    this.minutelogid = data.id;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  minuteIndex=null;
  removeMinute(content,minutedata) {
	this.editMinuteStatus=0;  
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.service.deleteMinuteData({id:minutedata.id})
        .pipe(first())
        .subscribe(res => {
       
            if(res.status){
              this.getminuteData(this.meetingData.id);
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

  minutelogIndex=null;
  removeMinutelog(content,minutelogdata) {
	this.editLogStatus=0;  
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    this.modalss.result.then((result) => {
        this.service.deleteMinutelogData({id:minutelogdata.id})
        .pipe(first())
        .subscribe(res => {
       
            if(res.status){
              this.getminutelogData(minutelogdata.minute_id);
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

  editMinuteStatus=0;  
  minutedata:any;
  editMinute(content,index:number,minutedata) {
	this.minute_date_error = false;
    this.details_error = false;
    this.raised_id_error = false;
    this.class_error = false;
    this.status_error = false;
	this.editMinuteStatus=1;
	
    this.minutedata = minutedata;
	/*
    this.model.minute_date = this.errorSummary.editDateFormat(minutedata.minute_date);
    this.model.details = minutedata.details;
    this.model.raised_id = minutedata.raised_id;
    this.model.class = minutedata.class;
    this.model.status = minutedata.status;
	*/
	
	this.minuteForm.patchValue({
      minute_date:this.errorSummary.editDateFormat(minutedata.minute_date),
      raised_id:minutedata.raised_id,      
      class:minutedata.class,
	  status:minutedata.status,
	  details:minutedata.details
    });
	
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  editLogStatus=0;
  Logdata:any;
  editMinutelog(index:number,minutelogdata) {
    this.Logdata = minutelogdata;
	/*
    this.model.log_date = this.errorSummary.editDateFormat(minutelogdata.log_date);
    this.model.description = minutelogdata.description;
    this.model.status = minutelogdata.status;
	*/
	
	this.minutelogForm.patchValue({
      log_date:this.errorSummary.editDateFormat(minutelogdata.log_date),
      description:minutelogdata.description,     
	  status:minutelogdata.status
    });
	
	this.editLogStatus=1;
  }

  minute_date_error:any;
  details_error:any;
  raised_id_error:any;
  class_error:any;
  status_error:any;
  minutesuccess:any;
  minuteerror:any;
  minuteloading:any;
  submitMinuteAction(){
    
	this.mf.minute_date.markAsTouched();
	this.mf.details.markAsTouched();
	this.mf.raised_id.markAsTouched();
	this.mf.class.markAsTouched();
	this.mf.status.markAsTouched();
	
    this.minute_date_error = false;
    this.details_error = false;
    this.raised_id_error = false;
    this.class_error = false;
    this.status_error = false;
	
	/*
    if(this.model.minute_date ==''){
      this.minute_date_error = true;
    }
    if(this.model.raised_id ==''){
      this.raised_id_error =true;
    }
    if(this.model.class ==''){
      this.class_error =true;
    }
    if(this.model.status ==''){
      this.status_error =true;
    }

    if(this.minute_date_error || this.raised_id_error || this.class_error || this.status_error){
      return false;
    }else{
	*/
	
	if(this.minuteForm.valid){
		
      let details = this.minuteForm.get('details').value;
      let raised_id = this.minuteForm.get('raised_id').value;
	  let minute_date = this.errorSummary.displayDateFormat(this.minuteForm.get('minute_date').value);
	  let status = this.minuteForm.get('status').value;
	  let classid = this.minuteForm.get('class').value;
	  
      let datalog:any = {meeting_id:this.meetingData.id,minute_date: minute_date,raised_id:raised_id,details:details,class:classid,status:status};
      if(this.minutedata){
        datalog.id = this.minutedata.id;
      }

      
      this.loading['logsbutton'] = true;
      this.service.addMinuteData(datalog)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.getminuteData(this.meetingData.id);
            this.minutesuccess = res.message;
            setTimeout(() => {
              this.minutesuccess = '';
              this.minutedata = '';
              this.modalss.close('');
            },this.errorSummary.redirectTime);
            
            this.buttonDisable = true;
          }else if(res.status == 0){
            this.minuteerror = {summary:res};
          }
          this.loading['logsbutton'] = false;
          
          this.buttonDisable = false;
      },
      error => {
          this.loading['logsbutton'] = false;
          this.minuteerror = {summary:error};
          
      });
      
    }    
  }

  log_date_error:any;
  description_error:any;
  logstatus_error:any;
  minutelogsuccess:any;
  minutelogerror:any;
  minutelogloading:any;
  submitMinutelogAction(){
    this.log_date_error = false;
    this.description_error = false;
    this.logstatus_error = false;
	
	this.mlf.log_date.markAsTouched();
	this.mlf.description.markAsTouched();
	this.mlf.status.markAsTouched();
	
	/*
    if(this.model.log_date ==''){
      this.log_date_error = true;
    }
    if(this.model.status ==''){
      this.logstatus_error =true;
    }
    if(this.model.description ==''){
      this.description_error =true;
    }

    if(this.log_date_error || this.logstatus_error || this.description_error){
      return false;
    }else{
		*/
	if(this.minutelogForm.valid){
		
      let status = this.minutelogForm.get('status').value;
      let description = this.minutelogForm.get('description').value;
	  let log_date = this.errorSummary.displayDateFormat(this.minutelogForm.get('log_date').value);
	 
		
      let datalog:any = {minute_id:this.minutelogid,log_date: log_date,description:description,status:status};
      if(this.Logdata){
        datalog.id = this.Logdata.id;
      }

      this.loading['minutelogbutton'] = true;
      this.service.addMinutelogData(datalog)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
			/*  
            this.model.log_date='';
            this.model.description='';
            this.model.status='';
			
			this.minutelogForm.patchValue({
			  log_date:'',
			  description:'',     
			  status:''
			});*/
			this.editLogStatus=0;
			this.minutelogForm.reset();
            this.Logdata = '';
            this.getminutelogData(this.minutelogid);
            this.minutelogsuccess = res.message;
            setTimeout(() => {
              this.minutelogsuccess = '';
              //this.minutedata = '';
              //this.modalss.close('');
            },this.errorSummary.redirectTime);
            
            this.buttonDisable = true;
          }else if(res.status == 0){
            this.minutelogerror = {summary:res};
          }
          this.loading['minutelogbutton'] = false;
          
          this.buttonDisable = false;
      },
      error => {
          this.loading['minutelogbutton'] = false;
          this.minutelogerror = {summary:error};
          
      });
    }
  }

  meetingFormreset()
  {
	this.minute_date_error = false;
    this.details_error = false;
    this.raised_id_error = false;
    this.class_error = false;
    this.status_error = false;
	
	this.editStatus=0;  
    this.form.reset();
    this.meetingData = '';
    this.minuteviewEntries = [];
    this.minutedata = '';
	this.editMinuteStatus=0;
	this.editLogStatus=0;
	
	this.form.patchValue({      
       type:''
     });
  }
  
  fnViewLog(logID)
  {
	let minutesE = this.minuteviewEntries.find(s => s.id ==  logID);	
	if(minutesE != undefined)
	{
		if(minutesE.log_display_status==0)
		{
			minutesE.log_display_status=1;
		}else{
			minutesE.log_display_status=0;
		}
	}
  }

  onSubmit(){ }

}
