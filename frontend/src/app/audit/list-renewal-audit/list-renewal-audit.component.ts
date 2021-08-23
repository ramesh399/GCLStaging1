import {DecimalPipe} from '@angular/common';
import {Directive, Component, QueryList, ViewChildren } from '@angular/core';
import {Observable} from 'rxjs';
import { Router } from '@angular/router';

import { Invoice } from '@app/models/invoice/invoice';

import {ListRenewalAuditService} from '@app/services/audit/list-renewal-audit.service';

import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { first } from 'rxjs/operators';
import {saveAs} from 'file-saver';
import { AuthenticationService } from '@app/services/authentication.service';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';

@Component({
  selector: 'app-list-renewal-audit',
  templateUrl: './list-renewal-audit.component.html',
  styleUrls: ['./list-renewal-audit.component.scss'],
  providers: [ListRenewalAuditService]
})
export class ListRenewalAuditComponent {

  listauditplan$: Observable<Invoice[]>;
  total$: Observable<number>;
  statuslist:any=[];
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  userType:number;
  userdetails:any;
  userdecoded:any;
  auditStatus:any;
  standardList:Standard[];
  
  constructor(public service: ListRenewalAuditService, private router: Router, private authservice:AuthenticationService,private standardservice: StandardService) {
    this.listauditplan$ = service.listauditplan$;
    this.total$ = service.total$;   
    this.router.routeReuseStrategy.shouldReuseRoute = () => false;

    this.service.auditStatus().subscribe(data=>{
      this.auditStatus = data.enumStatus;
      this.statuslist  = data.status;
    })
	
	this.standardservice.getStandard().subscribe(res => {
		this.standardList = res['standards'];     
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
  
  
  downloadInvoiceFile(invoicecode,invoice_id,offer_id){
    this.service.downloadInvoiceFile({invoice_id,offer_id})
    .subscribe(res => {
        saveAs(new Blob([res],{type:'application/pdf'}),'invoice_'+invoicecode+'.pdf');
    });
  }
  

  onSort({column, direction}: SortEvent) {
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
