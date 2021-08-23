import {DecimalPipe} from '@angular/common';
import {Directive, Component, QueryList, ViewChildren,OnInit } from '@angular/core';
import {Observable} from 'rxjs';
import { Router } from '@angular/router';

import { Application } from '@app/models/application/application';

import { GenerateDetailService } from '@app/services/offer/generate-detail.service';

import {GenerateListService} from '@app/services/offer/generate-list.service';
import {NgbdSortableHeader, SortEvent, PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { AuthenticationService } from '@app/services';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
//import { getFileNameFromResponseContentDisposition, saveFile } from '@app/helpers/file-download-helper';
import { first } from 'rxjs/operators';
import {saveAs} from 'file-saver';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';
import { User } from '@app/models/master/user';
import { UserService } from '@app/services/master/user/user.service';

@Component({
  selector: 'app-offer-generation-list',
  templateUrl: './offer-generation-list.component.html',
  styleUrls: ['./offer-generation-list.component.scss'],
  providers: [GenerateListService]
})
export class OfferGenerationListComponent implements OnInit {

  applications$: Observable<Application[]>;
  total$: Observable<number>;
  userType:number;
  userdetails:any;
  error:any;
  paginationList = PaginationList;
  commontxt = commontxt;
  statuslist:any=[];
  standardList:Standard[];
  franchiseList:User[];

  
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal, public service: GenerateListService, private router: Router,private generateDetail:GenerateDetailService,
    private authenticationService: AuthenticationService,private standardservice: StandardService,private userservice: UserService) {

    this.applications$ = service.applications$;
    this.total$ = service.total$;   
    this.router.routeReuseStrategy.shouldReuseRoute = () => false;
  }
  
  ngOnInit() {
    this.authenticationService.currentUser.subscribe(x => {
      if(x){
        let user = this.authenticationService.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
      }
    });

    this.service.getData().pipe(first())
    .subscribe(res => {
      this.statuslist  = res.status;
      
    },
    error => {
        this.error = error;
        // this.loading = false;
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


  downloadFile(appCode:string,app_id,offer_id){
    //console.log('asdfdsf');

    this.generateDetail.downloadOfferFile({app_id,offer_id})
    .subscribe(res => {
        this.modalss.close();
        saveAs(new Blob([res],{type:'application/pdf'}),'offer_'+appCode+'.pdf');
       // console.log(res);
        //saveFile(res.blob(), 'test.txt');
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
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