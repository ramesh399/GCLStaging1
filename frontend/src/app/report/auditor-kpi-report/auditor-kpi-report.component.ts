import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditorKpiReportService } from '@app/services/report/auditor-kpi-report.service';
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
  selector: 'app-auditor-kpi-report',
  templateUrl: './auditor-kpi-report.component.html',
  styleUrls: ['./auditor-kpi-report.component.scss']
})
export class AuditorKpiReportComponent implements OnInit {

  title = 'Auditor KPI Report';	
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  maxDate = new Date();
  error:any;
  success:any;
  data:any=[];
  appdata:any=[];

  standardList:Standard[];
  franchiseList:User[];
  modalss:any;

  userType:number;
  userdetails:any;
  userdecoded:any;


  constructor(private modalService: NgbModal, private userservice: UserService,private router: Router,private fb:FormBuilder,private standardservice: StandardService, private authservice:AuthenticationService,private errorSummary: ErrorSummaryService,public service: AuditorKpiReportService) { }

  ngOnInit() {
    this.form = this.fb.group({
      from_date:[''],
      to_date:[''],
      standard_id:[''],
      oss_id:[''],
    });

    this.standardservice.getStandard().subscribe(res => {
      this.standardList = res['standards'];     
    });

    this.userservice.getAllUser({type:3}).pipe(first())
    .subscribe(res => {
      this.franchiseList = res.users;
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

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  getSelectedstdValue(val)
  {
    return this.standardList.find(x=> x.id==val).code;    
  }

  getSelectedFranchiseValue(val)
  {
    return this.franchiseList.find(x=> x.id==val).osp_details;    
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
    this.f.from_date.markAsTouched();
    this.f.to_date.markAsTouched();
    this.f.standard_id.markAsTouched();
    this.f.oss_id.markAsTouched();

    this.fieldErrors='';
    this.from_dateErr='';
    this.to_dateErr='';
    
    let from_date = this.form.get('from_date').value;
    let to_date = this.form.get('to_date').value;
    let standard_id = this.form.get('standard_id').value;
    let oss_id = this.form.get('oss_id').value;
  
    if((from_date=='' || from_date===null) && (to_date=='' || to_date===null) && standard_id=='' && oss_id=='')
    {
      this.fieldErrors="Please add atleast one Value";
      return false;
    }
    
    if((from_date=='' || from_date===null) && (to_date!='' && to_date!==null))
    {
      this.from_dateErr="Please add From date";
      return false;
    }

    if((from_date!='' && from_date!==null) && (to_date=='' || to_date===null))
    {
      this.to_dateErr="Please add To date";
      return false;
    }
    

    if (this.fieldErrors=='' && this.from_dateErr=='' && this.to_dateErr=='') 
    {
      if(from_date!='' && from_date!==null)
      {
        from_date=this.errorSummary.displayDateFormat(from_date);
      }

      if(to_date!='' && to_date!==null)
      {
        to_date=this.errorSummary.displayDateFormat(to_date);
      }

      let expobject:any={from_date:from_date,to_date:to_date,standard_id:standard_id,oss_id:oss_id,type:type};
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
