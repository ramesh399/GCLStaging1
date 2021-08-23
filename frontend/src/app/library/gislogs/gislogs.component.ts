import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { GislogService } from '@app/services/library/gis/gislog.service';

import { LogListService } from '@app/services/library/log-list.service';

import {Gislog} from '@app/models/library/gislog';
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
  selector: 'app-gislogs',
  templateUrl: './gislogs.component.html',
  styleUrls: ['./gislogs.component.scss'],
  providers: [LogListService]
})
export class GislogsComponent implements OnInit {
  

  Gislog$: Observable<Gislog[]>;
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
  gislogData:any=[];
  statuslist:any=[];
  logtypelist:any=[];
  
  model: any = {id:null,action:null,type:'',description:'',date:''};
  success:any;
  modalss:any;

  formData:FormData = new FormData();
  userType:number;
  userdetails:any;
  userdecoded:any;

  gislogEntries:any=[];

  gisFileErr = '';

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, public userService:UserService,public service: LogListService, private gislogService: GislogService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { 
    this.Gislog$ = service.gislogs$;
    this.total$ = service.total$;
  }

  ngOnInit() {

    this.form = this.fb.group({
      received_date:['',[Validators.required]],
      type:['',[Validators.required]],
      title:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
      description:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],	 	  
      gis_file:[''],	
      status:['',[Validators.required]]
    });

    this.logForm = this.fb.group({
      date:['',[Validators.required]],
      type:['',[Validators.required]],
      description:['',[Validators.required,this.errorSummary.noWhitespaceValidator]]
    });
	
    this.gislogService.getGisStatusList().pipe(first())
    .subscribe(res => { 
      this.typelist  = res.typelist;
      this.statuslist  = res.statuslist;
      this.logtypelist = res.logtypelist;
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
  get sf() { return this.logForm.controls; }  

  open(content,arg='') {

    
  }

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }


  downloadgisFile(fileid='',filetype='',filename='')
  {
    this.service.downloadGisFile({id:fileid,filetype})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }

  gis_file:any;
  gisfileChange(element) {
    let files = element.target.files;
    this.gisFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("gis_file", files[0], files[0].name);
      this.gis_file = files[0].name;
      
    }else{
      this.gisFileErr ='Please upload valid file';
    }
    element.target.value = '';
   
  }



  viewGis(content,data)
  {
    this.gislogData = data;
    this.getLogViewData(data.id);
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
    //this.gislogviewEntries = 
    this.modalss.result.then((result) => {
      this.gislogviewEntries = [];
    }, (reason) => {
        this.gislogviewEntries = [];
    });
  }

  gisIndex:number=null;
  loading:any=[];
  addgis()
  {
    this.f.received_date.markAsTouched();
    this.f.type.markAsTouched();
    this.f.title.markAsTouched();
    this.f.status.markAsTouched();
    this.f.description.markAsTouched();
    this.gisFileErr = '';
    if(this.gis_file=='' || this.gis_file===undefined){
      this.gisFileErr = 'Please upload file';
      return false;
    }
   // console.log(this.gis_file);
    //console.log(this.gisFileErr);
    if(this.form.valid && this.gisFileErr =='')
    {
      this.buttonDisable = true;
      this.loading['button'] = true;
      let received_date = this.errorSummary.displayDateFormat(this.form.get('received_date').value);
      //this.form.get('received_date').value;

      let type = this.form.get('type').value;
      let title = this.form.get('title').value;
      let description = this.form.get('description').value;
      let status = this.form.get('status').value;

      let expobject:any= {received_date:received_date,type:type,title:title,description:description,status:status};
      
      
      if(1)
      {

        if(this.gisData){
          expobject.id = this.gisData.id;
          expobject.gis_file = this.gisData.gis_file;
        }
        
        this.formData.append('formvalues',JSON.stringify(expobject));

        this.service.addData(this.formData)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              this.gisData = '';
              this.formData = new FormData(); 
              this.service.customSearch();
              this.gisFormreset();
              this.success = {summary:res.message};
              this.buttonDisable = false;
              this.gis_file = '';
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
  resetgis(){
	this.editLogStatus=0;   
	this.editStatus=0;
	this.gisFileErr='';
    this.form.reset();
    this.gisData = '';
    this.formData = new FormData(); 
    this.gislogEntries = [];
    this.gis_file = '';
	
	this.form.patchValue({      
      type:'',         
      status:''
    });
  }

  gisFormreset()
  {
	this.editLogStatus=0;   
	this.editStatus=0;
	this.gisFileErr='';
    this.form.reset();
    this.gisData = '';
    this.formData = new FormData(); 
    this.gislogEntries = [];
    this.gis_file = '';
	
	this.form.patchValue({      
      type:'',         
      status:''
    });
  }

  removegisFile()
  {
    this.gis_file = '';
  }

  addlog(content)
  {
	this.gislogdata = '';
	//this.sf.date.markAsTouched();
    //this.sf.type.markAsTouched();
    //this.sf.description.markAsTouched();
	
	this.logForm.reset();
	
	/*
	this.logForm.patchValue({
      date:'',
      type:'',      
      description:''
    });
	*/
	
	this.date_error =false;
    this.type_error =false;
    this.description_error =false;
	
	this.editLogStatus=0;  
    //this.model.date = '';
    //this.model.description ='';
    //this.model.type ='';
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  gislogviewEntries:any=[];
  getLogViewData(gisdataid){
    this.gislogviewEntries = [];
    this.loading['logviewdata'] =true;
    this.service.getGisLogData({gis_id:gisdataid})
    .pipe(first())
    .subscribe(res => {

      this.loading['logviewdata'] =false;
      if(res.status){
        this.gislogviewEntries = res['data'];
      }else if(res.status == 0){
        this.error = {summary:res};
      }       
    },
    error => {
        this.error = {summary:error};
        this.loading['logviewdata'] =false;
    });
  }


  getLogData(gisdataid){
    this.gislogEntries = [];
    this.loading['logdata'] =true;
    this.service.getGisLogData({gis_id:gisdataid})
    .pipe(first())
    .subscribe(res => {

      this.loading['logdata'] =false;
      if(res.status){
        this.gislogEntries = res['data'];
      }else if(res.status == 0){
        this.error = {summary:res};
      }       
    },
    error => {
        this.error = {summary:error};
        this.loading['logdata'] =false;
    });
  }


  gislogIndex=null;
  removeGislog(content,gislogdata) {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.service.deleteGisLogData({id:gislogdata.id})
        .pipe(first())
        .subscribe(res => {
       
            if(res.status){
              this.getLogData(this.gisData.id);
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
  gislogdata:any;
  editGislog(content,index:number,gislogdata) {
	this.date_error =false;
    this.type_error =false;
    this.description_error =false;
	
	this.editLogStatus=1;  
    this.gislogdata = gislogdata;
	
    //this.model.date = this.errorSummary.editDateFormat(gislogdata.log_date);
    //this.model.description =gislogdata.description;
    //this.model.type =gislogdata.type;
	this.logForm.patchValue({
      date:this.errorSummary.editDateFormat(gislogdata.log_date),
      type:gislogdata.type,      
      description:gislogdata.description
    });

    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  date_error:any;
  type_error:any;
  description_error:any;
  logsuccess:any;
  logerror:any;
  logloading:any;
  submitLogAction(){
    
	this.sf.date.markAsTouched();
    this.sf.type.markAsTouched();
    this.sf.description.markAsTouched();
	if(this.logForm.valid)
	{
	  let date = this.errorSummary.displayDateFormat(this.logForm.get('date').value);
      let type = this.logForm.get('type').value;     
      let description = this.logForm.get('description').value;
	  
  
      let datalog:any = {gis_id:this.gisData.id,date:date,type:type,description:description};
      if(this.gislogdata){
        datalog.id = this.gislogdata.id;
      }

      
      this.loading['logsbutton'] = true;
      this.service.addGisLogData(datalog)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.getLogData(this.gisData.id);
            this.logsuccess = res.message;
            setTimeout(() => {
              this.logsuccess = '';
              this.gislogdata = '';
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


  removeGis(content,gisdata) {

      this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

      this.modalss.result.then((result) => {
          this.resetgis();
          this.service.deleteGisData({id:gisdata.id})
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
  gisData:any;
  editGis(index:number,gisdata) {
	this.date_error =false;
    this.type_error =false;
    this.description_error =false;
	
	this.editStatus=1;  
    this.formData = new FormData(); 
    this.gisData = gisdata;
    this.gisFileErr = '';
    this.gis_file = gisdata.gis_file;
    this.form.patchValue({
      received_date:this.errorSummary.editDateFormat(gisdata.received_date),
      type:gisdata.type,
      title:gisdata.title,
      description:gisdata.description,     
      status:gisdata.status
    });
    this.getLogData(gisdata.id);

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
