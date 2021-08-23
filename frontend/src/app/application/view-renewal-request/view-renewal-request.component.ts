import {Directive, Component, QueryList, ViewChildren,OnInit } from '@angular/core';
import {Observable} from 'rxjs';
import { ActivatedRoute ,Params, Router } from '@angular/router';
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
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';


@Component({
  selector: 'app-view-renewal-request',
  templateUrl: './view-renewal-request.component.html',
  styleUrls: ['./view-renewal-request.component.scss']
})
export class ViewRenewalRequestComponent implements OnInit {

  userdecoded:any;
  bsector_group_id=[];
  id:number;
  app_id:number;
  error:any;
  success:any;
  loading = false;
  applicationdata:Application;
  panelOpenState = true;
  modalss:any;
  userType:number;
  userdetails:any;
  arrEnumStatus:any[];
  requestdata:any=[];

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private userservice: UserService, public service: RenewalListService,private router:Router,private authservice:AuthenticationService,private errorSummary: ErrorSummaryService,private standardservice: StandardService) {
   
  }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.app_id = this.activatedRoute.snapshot.queryParams.app;

    this.authservice.currentUser.subscribe(x => {
      if(x){
        
         
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        
      }else{
        this.userdecoded=null;
      }
    });

    this.service.getRequest({id:this.id})
    .pipe(first())
    .subscribe(res => {
      this.requestdata = res.data;
      
    },
    error => {
        this.error = {summary:error};
        this.loading = false;
    });
  }

  openmodal(content) {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

}
