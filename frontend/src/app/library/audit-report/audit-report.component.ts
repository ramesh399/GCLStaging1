import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { AuditReport } from '@app/models/library/auditreport';
import { AuditReportService } from '@app/services/library/audit-report/audit-report.service';
import { User } from '@app/models/master/user';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-audit-report',
  templateUrl: './audit-report.component.html',
  styleUrls: ['./audit-report.component.scss'],
  providers: [AuditReportService]
})
export class AuditReportComponent implements OnInit {

  title = 'Audit Report'; 
  form : FormGroup; 
  auditreports$: Observable<AuditReport[]>;
  total$: Observable<number>;
  id:number;
  auditreportData:any;
  AuditreportData:any;
  error:any;
  success:any;
  buttonDisable = false;
  model: any = {franchise_id:null};
  franchiseList:User[];
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  reviewerlist:any;
  accesslist:any;
  typelist:any;
  auditreportEntries:any=[];
  source_file:any;
  modalss:any;
  loading:any=[];
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  sourceFileErr ='';

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private userservice: UserService, private router: Router,private fb:FormBuilder, public service: AuditReportService, public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
    this.auditreports$ = service.auditreports$;
    this.total$ = service.total$;
  }

  ngOnInit() {
    this.form = this.fb.group({	
      description:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],  
      franchise_id:['',[Validators.required]],
      reviewer:['',[Validators.required]],
      access_id:['',[Validators.required]],
      date:['',[Validators.required]],
      source_file:['']	
    });

    this.service.getTypeList().pipe(first())
    .subscribe(res => {   
      this.reviewerlist  = res.reviewerlist;
      this.accesslist  = res.accesslist;
	 
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
    this.userservice.getAllUser({type:3,filteruser:1}).pipe(first())
    .subscribe(res => {
      this.franchiseList = res.users;
	  
    },
    error => {
        this.error = {summary:error};
    });
  }

  get f() { return this.form.controls; }

  auditreportListEntries = [];
  auditreportIndex:number=null;
  addauditreport()
  {
    this.f.description.markAsTouched();
    this.f.franchise_id.markAsTouched();
    this.f.reviewer.markAsTouched();
    this.f.access_id.markAsTouched();
    this.f.date.markAsTouched();
    this.sourceFileErr = '';
    if(this.source_file=='' || this.source_file===undefined){
      this.sourceFileErr = 'Please upload file';
      return false;
    }

    if(this.form.valid && this.sourceFileErr =='')
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let description = this.form.get('description').value;
      let reviewer = this.form.get('reviewer').value;
      let franchise_id = this.form.get('franchise_id').value;
      let access_id = this.form.get('access_id').value;
      let date = this.errorSummary.displayDateFormat(this.form.get('date').value);

      let expobject:any={description:description,reviewer:reviewer,franchise_id:franchise_id,report_date:date,access_id:access_id};
      
      if(1)
      {
        if(this.auditreportData){
          expobject.id = this.auditreportData.id;
          expobject.source_file = this.auditreportData.source_file;
        }
        
        this.formData.append('formvalues',JSON.stringify(expobject));
        this.service.addData(this.formData)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              
              this.service.customSearch();
              this.auditreportFormreset();
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

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  viewAuditreport(content,data)
  {
    this.AuditreportData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  editStatus=0;
  editAuditreport(auditreportdata) 
  { 
    this.sourceFileErr = '';
    this.editStatus=1;
    this.formData = new FormData(); 
    this.auditreportData = auditreportdata;
    this.source_file = auditreportdata.source_file;
    this.form.patchValue({
      date:this.errorSummary.editDateFormat(auditreportdata.date),
      description:auditreportdata.description,
      reviewer:auditreportdata.reviewer,     
      franchise_id:auditreportdata.franchise_id,
      access_id:auditreportdata.access_id
    });

    this.scrollToBottom();
  }

  auditreportChange(element) 
  {
    let files = element.target.files;
    this.sourceFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("source_file", files[0], files[0].name);
      this.source_file = files[0].name;
      
    }else{
      this.sourceFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }


  downloadFile(fileid='',filetype='',filename='')
  {
    this.service.downloadAuditReportFile({id:fileid,filetype})
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


  removeAuditreport(content,auditreportdata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.resetauditreport();
        this.service.deleteAuditReportData({id:auditreportdata.id})
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
  
  getSelectedValue(val)
  {
    
    return this.accesslist.find(x=> x.id==val).name;
    
  }

  getSelectedFranchiseValue(val)
	{
		return this.franchiseList.find(x=> x.id==val).osp_details;    
  }


  resetauditreport()  
  {
	this.editStatus=0;  
	this.sourceFileErr='';
    this.form.reset();
    this.auditreportData = '';
    this.formData = new FormData();   
    this.source_file = '';
	
	this.form.patchValue({      
      reviewer:'',     
      franchise_id:'',
      access_id:''
    });
  }

  auditreportFormreset()
  {
	this.editStatus=0;
	this.sourceFileErr='';  
    this.form.reset();
    this.source_file = '';
    this.auditreportData = '';
    this.formData = new FormData(); 
	
	this.form.patchValue({      
      reviewer:'',     
      franchise_id:'',
      access_id:''
    });
  }

  removeauditreportfile()
  {
    this.source_file = '';
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
