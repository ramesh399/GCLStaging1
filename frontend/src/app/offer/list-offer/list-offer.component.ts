import {DecimalPipe} from '@angular/common';
import {Directive, Component, QueryList, ViewChildren, OnInit } from '@angular/core';
import {Observable} from 'rxjs';
import { Router } from '@angular/router';

import { Offer } from '@app/models/offer/offer';

import {ListOfferService} from '@app/services/offer/list-offer.service';
import {NgbdSortableHeader, SortEvent, PaginationList,commontxt} from '@app/helpers/sortable.directive';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';
import {saveAs} from 'file-saver';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';
import { User } from '@app/models/master/user';
import { UserService } from '@app/services/master/user/user.service';
import { AuthenticationService } from '@app/services/authentication.service';

@Component({
  selector: 'app-list-offer',
  templateUrl: './list-offer.component.html',
  styleUrls: ['./list-offer.component.scss'],
  providers: [ListOfferService]
})
export class ListOfferComponent implements OnInit {

  offers$: Observable<Offer[]>;
  total$: Observable<number>;
  //sno:number;
  paginationList = PaginationList;
  commontxt = commontxt;
  standardList:Standard[];
  franchiseList:User[];
  error:any;
  userType:number;
  userdetails:any;
  userdecoded:any;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal, private authservice:AuthenticationService, public service: ListOfferService, private router: Router,private standardservice: StandardService,private userservice: UserService) {
    this.offers$ = service.offers$;
    this.total$ = service.total$;   
    this.router.routeReuseStrategy.shouldReuseRoute = () => false;
  }
  ngOnInit() {

    this.authservice.currentUser.subscribe(x => {
      if(x){
        
         
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        
      }else{
        this.userdecoded=null;
      }
    });
	this.standardservice.getStandard().subscribe(res => {
		this.standardList = res['standards'];     
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
  open(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  
  downloadFile(offercode,app_id,offer_id){
    this.service.downloadFile({app_id,offer_id})
    .subscribe(res => {
        this.modalss.close();
        saveAs(new Blob([res],{type:'application/pdf'}),'offer_'+offercode+'.pdf');
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
  
  getSelectedFranchiseValue(val)
  {
    return this.franchiseList.find(x=> x.id==val).osp_details;    
  }

}