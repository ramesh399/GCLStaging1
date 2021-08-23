import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { StandardMonthlyReportService } from '@app/services/report/standard-monthly-report.service';
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
  selector: 'app-standard-monthly-report',
  templateUrl: './standard-monthly-report.component.html',
  styleUrls: ['./standard-monthly-report.component.scss']
})
export class StandardMonthlyReportComponent implements OnInit {

  title = 'TE Monthly Certified Report';	
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

  monthList:any = [];
  yearList:any = [];

  constructor(private modalService: NgbModal, private userservice: UserService,private router: Router,private fb:FormBuilder,private standardservice: StandardService, private authservice:AuthenticationService,private errorSummary: ErrorSummaryService,public service: StandardMonthlyReportService) { }

  ngOnInit() {
    //from_date:[''],
    //to_date:[''],
    this.form = this.fb.group({
     
      standard_id:['',[Validators.required]],
      oss_id:[''],
      month_id:['',[Validators.required]],
      year_id:['',[Validators.required]]
    });

    //GetMonthyear
    this.service.getMonthYear({type:'standardmonth'}).subscribe(res=>{
      this.monthList = res.months;
      this.yearList = res.years;
    });

    this.standardservice.getStandard().subscribe(res => {
      this.standardList = res['standards'];     
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

  month_idErr:any;
  year_idErr:any;
  onSubmit(type,filename='')
  {
    this.f.month_id.markAsTouched();
    this.f.year_id.markAsTouched();
    this.f.standard_id.markAsTouched();
    this.f.oss_id.markAsTouched();

    this.fieldErrors='';
    this.month_idErr='';
    this.year_idErr='';

    
    //let from_date = this.form.get('from_date').value;
    //let to_date = this.form.get('to_date').value;
    let month_id = this.form.get('month_id').value;
    let year_id = this.form.get('year_id').value;
    let standard_id = this.form.get('standard_id').value;
    let oss_id = this.form.get('oss_id').value;
    
    if (this.form.valid) 
    {
      let expobject:any={month_id:month_id,year_id:year_id,standard_id:standard_id,oss_id:oss_id,type:type};
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
          
          const monthname = this.monthList.find(x=>x.id==month_id).mvalue;
          const standardname = this.standardList.find(x=>x.id==standard_id).code;
          
          
          const filename = `${standardname} Monthly Data - ${monthname} ${year_id}.xlsx`;
          this.service.downloadFile(expobject)
          .pipe(first())
          .subscribe(res => {        
            this.modalss.close();
            //let fileextension = filename.split('.').pop(); 
            let contenttype = this.errorSummary.getContentType(filename);
            saveAs(new Blob([res],{type:contenttype}),filename);
          },
          error => {
              this.error = {summary:error};
          });
        }
        
        
      } else {
        
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.form); 
        
      }   
    }
    
  }

}
