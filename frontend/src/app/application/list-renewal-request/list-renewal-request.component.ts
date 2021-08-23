import {DecimalPipe} from '@angular/common';
import {Directive, Component, QueryList, ViewChildren,OnInit } from '@angular/core';
import {Observable} from 'rxjs';

import { first,tap } from 'rxjs/operators';
import { Application } from '@app/models/application/application';
import { UserService } from '@app/services/master/user/user.service';
import {RenewalListService} from '@app/services/application/list/renewal-list.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/services';
import { User } from '@app/models/master/user';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';

@Component({
  selector: 'app-list-renewal-request',
  templateUrl: './list-renewal-request.component.html',
  styleUrls: ['./list-renewal-request.component.scss'],
  providers: [RenewalListService, DecimalPipe]
})
export class ListRenewalRequestComponent implements OnInit {

  id:number;
  error:any;
  loading = false;
  applicationdata:Application;
  standardList:Standard[];
  applications$: Observable<Application[]>;
  total$: Observable<number>;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal, public service: RenewalListService,private authenticationService:AuthenticationService,private standardservice: StandardService) {
    this.applications$ = service.applications$;
    this.total$ = service.total$;   
  }

  userType:number;
  userdetails:any;
  arrEnumStatus:any;

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

  }

  modalss:any;
  

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

}
