import {DecimalPipe} from '@angular/common';
import {Directive, Component, QueryList, ViewChildren,OnInit } from '@angular/core';
import {Observable} from 'rxjs';

import { first,tap } from 'rxjs/operators';
import { Application } from '@app/models/brand/brand';
import { UserService } from '@app/services/master/user/user.service';
import {ApplicationListService} from '@app/services/brand/list/brand-list.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/services';
import { ApplicationDetailService } from '@app/services/brand/list/brand-detail.service';
import { User } from '@app/models/master/user';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';



@Component({
  selector: 'app-list',
  templateUrl: './list.component.html',
  styleUrls: ['./list.component.scss'],
  providers: [ApplicationListService, DecimalPipe]
})
export class ListComponent implements OnInit {

  id:number;
  error:any;
  loading = false;
  applicationdata:Application;
  approveruserList:User[];
  revieweruserList:User[];
  approvalStatusList = [];
  statuslist:any=[];
  typelist:any=[];
  status:any=[];
  standardList:Standard[];
  franchiseList:User[];

  model:any = {user_id:'',approver_user_id:'',status:'',comment:''};
  
  applications$: Observable<Application[]>;
  total$: Observable<number>;
  //sno:number;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal, private userservice: UserService, private applicationDetail:ApplicationDetailService, public service: ApplicationListService,private authenticationService:AuthenticationService,private standardservice: StandardService) {
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
      //  console.log(user);
      }
    });
	
	this.standardservice.getStandard().subscribe(res => {
		this.standardList = res['standards'];     
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

	this.userservice.getAllUser({type:3}).pipe(first())
	.subscribe(res => {
	  this.franchiseList = res.users;
	},
	error => {
		this.error = {summary:error};
	});   
    
  }

  modalss:any;
  open(content,app_id) {
    this.loading = true;
    this.modalss = this.modalService.open(content, {size:'lg',ariaLabelledBy: 'modal-basic-title'});
    
    this.applicationDetail.getApplication(app_id).pipe(first())
    .subscribe(res => {
      this.applicationdata = res;
      this.loading = false;
	
    },
    error => {
        this.error = {summary:error};
        this.loading = false;
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
  
  getSelectedValue(val)
  {
    return this.standardList.find(x=> x.id==val).code;    
  }
  
  getSelectedTypeValue(val)
  {
    return this.typelist[val];    
  }
  
  getSelectedFranchiseValue(val)
  {
    return this.franchiseList.find(x=> x.id==val).osp_details;    
  }
  

}

