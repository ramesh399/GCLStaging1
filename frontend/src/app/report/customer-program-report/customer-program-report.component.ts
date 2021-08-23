import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { CustomerProgramReportService } from '@app/services/report/customer-program-report.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Standard } from '@app/services/standard';
import { User } from '@app/models/master/user';
import { StandardService } from '@app/services/standard.service';
import { UserService } from '@app/services/master/user/user.service';

@Component({
  selector: 'app-customer-program-report',
  templateUrl: './customer-program-report.component.html',
  styleUrls: ['./customer-program-report.component.scss']
})
export class CustomerProgramReportComponent implements OnInit {

  title = 'Customer Wise Program Report';	
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  maxDate = new Date();
  error:any;
  success:any;
  data:any=[];

  standardList:Standard[];
  franchiseList:User[];
  modalss:any;

  userType:number;
  userdetails:any;
  userdecoded:any;
  appdata:any=[];
  audittypedata:any=[];
  constructor(private modalService: NgbModal, private userservice: UserService,private router: Router,private fb:FormBuilder,private standardservice: StandardService, private authservice:AuthenticationService,private errorSummary: ErrorSummaryService,public service: CustomerProgramReportService) { }

  ngOnInit() {
    this.form = this.fb.group({
      app_id:[''],
      audit_type:[''],
      oss_id:[''],
    });
    this.service.getAppData().pipe(first())
    .subscribe(res => {
      if(res.status)
      {
        this.appdata = res.appdata; 
        this.audittypedata = res.audittypedata;        
      }
    });
    /*
    this.standardservice.getStandard().subscribe(res => {
      this.standardList = res['standards'];     
    });

    this.userservice.getAllUser({type:3}).pipe(first())
    .subscribe(res => {
      this.franchiseList = res.users;
    });   
    */
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

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  getSelectedstdValue(val)
  {
    return this.standardList.find(x=> x.id==val).code;    
  }

  getSelectedCompanyValue(val)
  {
    return this.appdata.find(x=> x.id==val).company_name;    
  }
  
  onchangeHandler()
  {
    this.data=[];
  }

  
  fieldErrors:any;
  from_dateErr:any;
  to_dateErr:any;
  onSubmit(type,filename='')
  {
    this.f.audit_type.markAsTouched();
    this.f.app_id.markAsTouched();
    //this.f.standard_id.markAsTouched();
    //this.f.oss_id.markAsTouched();

    this.fieldErrors='';
    //this.from_dateErr='';
    //this.to_dateErr='';
    
    let app_id = this.form.get('app_id').value;
    let audit_type = this.form.get('audit_type').value;
    //let standard_id = this.form.get('standard_id').value;
    //let oss_id = this.form.get('oss_id').value;
  
    if((app_id=='' || app_id===null))
    {
      this.fieldErrors="Please select atleast one Company";
      return false;
    }
     

    if (this.fieldErrors=='') 
    {
      
      let expobject:any={audit_type:audit_type,app_id:app_id,type:type};
      if(1)
      {
        if(type=='submit')
        {
          this.data=[];
          this.loading = true;
          this.service.getData(expobject)
          .pipe(first())
          .subscribe(res => {        
              if(res.status){
                this.data = res.applications;
                this.buttonDisable = false;
              }else if(res.status == 0){
                this.error = {summary:res};
              }
              this.loading = false;
              this.buttonDisable = false;
          },
          error => {
              this.error = {summary:error};
              this.loading = false;
              this.buttonDisable = false;
          });
        }
        else
        {
          this.service.downloadFile(expobject)
          .pipe(first())
          .subscribe(res => {        
            this.modalss.close();
            let fileextension = filename.split('.').pop(); 
            let contenttype = this.errorSummary.getContentType(filename);
            saveAs(new Blob([res],{type:contenttype}),filename);
          },
          error => {
              this.error = {summary:error};
          });
        }
        
        
      } else {
        
        this.error = {summary:this.errorSummary.errorSummaryText};
        //this.errorSummary.validateAllFormFields(this.form); 
        
      }   
    }
    
  }

}
