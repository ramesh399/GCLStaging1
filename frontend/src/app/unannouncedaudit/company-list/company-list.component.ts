import {DecimalPipe} from '@angular/common';
import {Directive, Component, QueryList, ViewChildren,OnInit } from '@angular/core';
import {Observable} from 'rxjs';

import { first,tap } from 'rxjs/operators';
import { Application } from '@app/models/application/application';
import { UserService } from '@app/services/master/user/user.service';
import {CompanyListService} from '@app/services/unannouncedaudit/company-list.service';
import {UnannouncedAuditListService} from '@app/services/unannouncedaudit/unannounced-audit-list.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl, Form } from '@angular/forms';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/services';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { User } from '@app/models/master/user';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-company-list',
  templateUrl: './company-list.component.html',
  styleUrls: ['./company-list.component.scss'],
  providers: [CompanyListService, DecimalPipe]
})
export class CompanyListComponent implements OnInit {

  id:number;
  app_id:number;
  error:any;
  AuditData:any;
  msgsuccess:any;
  form : FormGroup;
  loading = false;
  applicationdata:Application;
  approveruserList:User[];
  revieweruserList:User[];
  approvalStatusList = [];
  riskList:any=[];
  statuslist:any=[];
  auditstdlist:any=[];
  unitList:any=[];
  typelist:any=[];
  status:any=[];
  standardList:Standard[];

  model:any = {user_id:'',approver_user_id:'',status:'',comment:''};
  
  applications$: Observable<Application[]>;
  total$: Observable<number>;
  //sno:number;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal, private fb:FormBuilder, public uauditservice: UnannouncedAuditListService, public errorSummary: ErrorSummaryService, private userservice: UserService, private applicationDetail:ApplicationDetailService, public service: CompanyListService,private authenticationService:AuthenticationService,private standardservice: StandardService) {
    this.applications$ = service.applications$;
    this.total$ = service.total$;   
  }
  userType:number;
  userdetails:any;
  arrEnumStatus:any;
  arrEnumType:any;
  ngOnInit() {
    this.authenticationService.currentUser.subscribe(x => {
      if(x){
        let user = this.authenticationService.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
      }
    });
	
	  this.standardservice.getStandard().subscribe(res => {
		this.standardList = res['standards'];     
    });

    this.uauditservice.getRiskoptions().subscribe(res => {
      this.riskList = res['risklist'];   
    });

    this.applicationDetail.getApplicationStatusList().pipe(first())
    .subscribe(res => {
      this.arrEnumStatus = res.enumstatus;
      this.statuslist = res.statuslist;
    },
    error => {
        //this.error = {summary:error};
        //this.loading = false;
    }); 
	
	this.applicationDetail.getApplicationType().pipe(first())
    .subscribe(res => {
      this.arrEnumType = res.enumtype;
      this.typelist = res.typelist;
    },
    error => {
        //this.error = {summary:error};
        //this.loading = false;
    }); 
	
    this.form = this.fb.group({
      app_id:[''],
      standard_id:['',[Validators.required]],
      unit_id:['',[Validators.required]],     
    });

   
    
  }
  

  onSort({column, direction}: SortEvent) {
    // resetting other headers
    //console.log('sdfsdfdsf');
    this.headers.forEach(header => {
      if (header.sortable !== column) {
        header.direction = '';
      }
    });

    this.service.sortColumn = column;
    this.service.sortDirection = direction;
  }
  
  getSelectedValue(val,type)
  {
    if(type=='standard')
    {
      return this.standardList.find(x=> x.id==val).code;
    }
    else if(type=='ra')
    {
      return this.riskList.find(x=> x.id==val).name;    
    }
    else if(type=='unit')
    {
      return this.unitList.find(x=> x.id==val).name;    
    }
  }

  modalss:any;
  openmodal(content,data) 
  {
    this.resetform();
    this.AuditData = data;
    this.app_id = this.AuditData.id;
    this.auditstdlist = this.AuditData.standardlist;
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  getUnits(value)
  {
    this.unitList = [];
    if(value)
    {
      this.form.patchValue({
        unit_id:''
      });
      
      this.uauditservice.getUnitsbystds({app_id:this.app_id,standard_id:value}).subscribe(res => {
        if(res.status)
        {
          this.unitList = res.units;
        }
      });
    }
    else{    
      this.unitList = [];
      this.form.patchValue({unit_id:''});    
    }
  }
  get f() { return this.form.controls; }

  SubmitForm()
  {
    this.f.standard_id.markAsTouched();
    this.f.unit_id.markAsTouched();

    if(this.form.valid)
    {
      this.loading = true;
      let standard_id = this.form.get('standard_id').value;
      let unit_id = this.form.get('unit_id').value;

      this.uauditservice.SaveAudit({app_id:this.app_id,standard_id:standard_id,unit_id:unit_id}).subscribe(res => {
        if(res.status)
        {
          this.msgsuccess = res.message;
          this.resetform();
          setTimeout(() => {
            this.msgsuccess = '';
            this.loading = false;
            this.modalss.close('');
          },this.errorSummary.redirectTime);
        }
        else
        {
          this.error = {summary:res};
        }
       
      });
    }
    else
    {
      this.loading = false;
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form);
    }
  }


  resetform()
  {
    this.form.reset();
    this.loading = false;

    this.form.patchValue({
      standard_id:'',
      unit_id:''
    });
  }
  
}
