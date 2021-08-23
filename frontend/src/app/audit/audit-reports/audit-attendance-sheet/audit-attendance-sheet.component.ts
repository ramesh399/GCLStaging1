import { Component, OnInit,QueryList, ViewChildren, Input } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditReportAttendanceSheet } from '@app/models/audit/audit-attendance-sheet';
import { AuditReportAttendanceSheetService } from '@app/services/audit/audit-attendance-sheet.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-audit-attendance-sheet',
  templateUrl: './audit-attendance-sheet.component.html',
  styleUrls: ['./audit-attendance-sheet.component.scss'],
  providers: [AuditReportAttendanceSheetService]
})
export class AuditAttendanceSheetComponent implements OnInit {
  @Input() cond_viewonly: any;

  title = 'Audit Attendance Sheet'; 
  form : FormGroup; 
  remarkForm : FormGroup; 
  attendances$: Observable<AuditReportAttendanceSheet[]>;
  total$: Observable<number>;
  id:number;
  audit_id:number;
  unit_id:number;
  attendanceData:any;
  AttendanceData:any;
  error:any;
  success:any;
  buttonDisable = false;
  dataloaded = false;
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  openlist:any;
  closelist:any;
  modalss:any;
  loading:any=[];
  isItApplicable=true;
  
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: AuditReportAttendanceSheetService, public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
    this.attendances$ = service.attendances$;
    this.total$ = service.total$;
  }


  ngOnInit() 
  {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
    this.form = this.fb.group({	
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]], 
      position:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]],
      open:['',[Validators.required]],
      close:['']	
    });

	this.remarkForm = this.fb.group({	
		remark:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]]
	});
	
    this.service.getOptionList().pipe(first())
    .subscribe(res => {    
      this.openlist  = res.openlist;
      this.closelist  = res.closelist;
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });


    this.service.getRemarkData({audit_id:this.audit_id,unit_id:this.unit_id,type:'attendance_list'}).pipe(first())
    .subscribe(res => {  
      this.dataloaded = true;
      if(res!==null)
      {
        this.isApplicable = res.status;
        if(res.status==1)
        {
          this.isItApplicable=true; 
        }else{
          this.isItApplicable=false;
        }	 
        
        if(res.comments)
        {
          this.remarkForm.patchValue({
            'remark':res.comments
          });
        }
      }  
      
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
  get rf() { return this.remarkForm.controls; }

  attendanceIndex:number=null;
  addattendance()
  {
    this.f.name.markAsTouched();
    this.f.position.markAsTouched();
    this.f.open.markAsTouched();
    //this.f.close.markAsTouched();
   

    if(this.form.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let name = this.form.get('name').value;
      let position = this.form.get('position').value;
      let open = this.form.get('open').value;
      let close = this.form.get('close').value;

      let expobject:any={audit_id:this.audit_id,unit_id:this.unit_id,name:name,position:position,open:open,close:close,type:'attendance_list'};
      
      if(1)
      {
        if(this.attendanceData)
        {
          expobject.id = this.attendanceData.id;
        }
        
        this.service.addData(expobject)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              this.success = {summary:res.message};
              this.service.customSearch();
              this.attendanceFormreset();
              this.remarkFormreset();
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


  viewAttendance(content,data)
  {
    this.AttendanceData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  editStatus=0;
  editAttendance(index:number,attendancedata) 
  { 
    this.editStatus=1;
    this.success = {summary:''};
    this.attendanceData = attendancedata;
    this.form.patchValue({
      name:attendancedata.name,
      position:attendancedata.position,     
      open:attendancedata.open,
      close:attendancedata.close ? attendancedata.close : ''
    });
    this.scrollToBottom();
  }


  removeAttendance(content,index:number,attendancedata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.attendanceFormreset();
        this.service.deleteData({id:attendancedata.id})
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

  attendanceFormreset()
  {
    this.editStatus=0;
    
    this.attendanceData = '';  
    this.form.reset();
    
    this.form.patchValue({     
      name:'',     
      position:'',
      open:'',
      close:''
    });
  }

  onSubmit(){ }

  scrollToBottom()
  {
    window.scroll({ 
      top: document.body.scrollHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }
  
  isApplicable:number;
  isItApp(arg)
  {
    this.isApplicable = arg;
	  if(arg==1)
	  {
		  this.isItApplicable=true;
	  }else{
		  this.isItApplicable=false;
	  }	  
  }
  
  addRemark()
  {
    this.rf.remark.markAsTouched();

    if(this.remarkForm.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let remark = this.remarkForm.get('remark').value;

      let expobject:any={unit_id:this.unit_id,audit_id:this.audit_id,comments:remark,is_applicable:this.isApplicable,type:'attendance_list'}

      this.service.addRemark(expobject)
      .pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.success = {summary:res.message};
          this.buttonDisable = false;
          this.loading['button'] = false;
          this.service.customSearch();
        }
      },
      error => {
          this.error = {summary:error};
          this.loading['button'] = false;
      });
    }
  }

  remarkFormreset()
  {
    this.editStatus=0;
    this.remarkForm.reset();
    
    this.remarkForm.patchValue({     
      remark:''
    });
  }

}
