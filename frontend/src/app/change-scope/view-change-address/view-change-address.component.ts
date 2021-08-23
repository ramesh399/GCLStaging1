import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { Application } from '@app/models/application/application';
import { UserService } from '@app/services/master/user/user.service';
import { AuthenticationService } from '@app/services';
import {Observable,Subject} from 'rxjs';
import { first, debounceTime, distinctUntilChanged, map,tap } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { NgForm } from '@angular/forms';
import { ChangeAddressService } from '@app/services/change-scope/change-address.service';
import { Country } from '@app/services/country';
import { State } from '@app/services/state';
import { CountryService } from '@app/services/country.service';
import { StandardService } from '@app/services/standard.service';


@Component({
  selector: 'app-view-change-address',
  templateUrl: './view-change-address.component.html',
  styleUrls: ['./view-change-address.component.scss']
})
export class ViewChangeAddressComponent implements OnInit {

  loading:any={};
  buttonDisable = false;
  showform = false; 
  unitypename = '';
  id:number;
  app_id:number;
  error:any;
  success:any;
  requestdata:any=[];
  addressdata:any=[];
  appdata:any=[];
  unitlist:any=[];
  unitdata:any=[];
  units:any;
  panelOpenState:any=false;
  countryList:Country[];
  stateList:State[];

  userType:number;
  userdetails:any;
  arrEnumStatus:any[];
  salutationList = [{"id":1,"name":"Mr"},{"id":2,"name":"Mrs"},{"id":3,"name":"Ms"},{"id":4,"name":"Dr"}];
  
  constructor( private userservice: UserService,private addressservice: ChangeAddressService,private activatedRoute:ActivatedRoute,
  private applicationDetail:ApplicationDetailService, private modalService: NgbModal, private router:Router,private authservice:AuthenticationService,private errorSummary: ErrorSummaryService) {  }

  ngOnInit() {
    this.app_id = this.activatedRoute.snapshot.queryParams.app;
    this.id = this.activatedRoute.snapshot.queryParams.id;

    this.addressservice.getAddress({id:this.id}).pipe(first())
    .subscribe(res => {
        this.addressdata = res.data;
    },
    error => {
        this.error = error;
    });


    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
      }
    });
  }

}
