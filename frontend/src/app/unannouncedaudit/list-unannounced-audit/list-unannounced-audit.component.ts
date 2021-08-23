import {DecimalPipe} from '@angular/common';
import {Directive, Component, QueryList, ViewChildren,OnInit } from '@angular/core';
import {Observable} from 'rxjs';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl, Form } from '@angular/forms';
import { first,tap } from 'rxjs/operators';
import { Application } from '@app/models/application/application';
import { UserService } from '@app/services/master/user/user.service';
import {UnannouncedAuditListService} from '@app/services/unannouncedaudit/unannounced-audit-list.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/services';
import { User } from '@app/models/master/user';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';


@Component({
  selector: 'app-list-unannounced-audit',
  templateUrl: './list-unannounced-audit.component.html',
  styleUrls: ['./list-unannounced-audit.component.scss'],
  providers: [UnannouncedAuditListService, DecimalPipe]
})
export class ListUnannouncedAuditComponent implements OnInit {

  id:number;
  app_id:number;
  error:any;
  AuditData:any;
  loading = false;
  form : FormGroup;
  applicationdata:Application;
  standardList:Standard[];
  statusList:any=[];
  riskList:any=[];
  auditstdlist:any=[];
  unitList:any=[];
  applications$: Observable<Application[]>;
  total$: Observable<number>;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal, private fb:FormBuilder, public errorSummary: ErrorSummaryService, public service: UnannouncedAuditListService,private authenticationService:AuthenticationService,private standardservice: StandardService) {
    this.applications$ = service.applications$;
    this.total$ = service.total$;   
  }

  userType:number;
  userdetails:any;
  arrEnumStatus:any;

  ngOnInit() {

    this.form = this.fb.group({
      app_id:[''],
      standard_id:['',[Validators.required]],
      unit_id:['',[Validators.required]],     
    });

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

    this.service.getRiskoptions().subscribe(res => {
      this.riskList = res['risklist'];
      this.statusList = res['statuslist'];     
      });

  }

  modalss:any;
  openmodal(content,data) 
  {
    this.form.patchValue({
      standard_id:'',
      unit_id:''
    });
    
    this.AuditData = data;
    this.app_id = this.AuditData.id;
    this.auditstdlist = this.AuditData.audit_standard;
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
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

  get f() { return this.form.controls; }

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
    else if(type=='status')
    {
      return this.statusList[val];    
    }
  }



  


}
