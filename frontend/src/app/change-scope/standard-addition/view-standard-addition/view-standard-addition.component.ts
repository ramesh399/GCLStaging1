import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { Application } from '@app/models/application/application';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import { AuthenticationService } from '@app/services';
import {Observable,Subject} from 'rxjs';
import { first, debounceTime, distinctUntilChanged, map,tap } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {saveAs} from 'file-saver';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Process } from '@app/models/master/process';
import { NgForm } from '@angular/forms';
import { ProcessService } from '@app/services/master/process/process.service';

import { Country } from '@app/services/country';
import { State } from '@app/services/state';
import { CountryService } from '@app/services/country.service';
import { StandardService } from '@app/services/standard.service';

import { BusinessSector } from '@app/models/master/business-sector';
import { BusinessSectorService } from '@app/services/master/business-sector/business-sector.service';
import { StandardAdditionService } from '@app/services/change-scope/standard-addition.service';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';

@Component({
  selector: 'app-view-standard-addition',
  templateUrl: './view-standard-addition.component.html',
  styleUrls: ['./view-standard-addition.component.scss']
})
export class ViewStandardAdditionComponent implements OnInit {

    constructor(private enquiryDetail:EnquiryDetailService, private userservice: UserService,private activatedRoute:ActivatedRoute,
    private applicationDetail:ApplicationDetailService, private modalService: NgbModal
    ,private router:Router,private authservice:AuthenticationService,private errorSummary: ErrorSummaryService, private processService:ProcessService, private additionservice: StandardAdditionService) {  }
  
    appform : any = {};
    processList: Process[];
    unitprocessList:any = [];
    additiondetails:any = [];
    process_ids=[];
    userdecoded:any;
    id:number;
    app_id:number;
    new_app_id:number;
    error:any;
    success:any;
    loading:any=[];
    applicationdata:Application;
    panelOpenState = true;
    approvalStatusList = [];//[{id:'1',name:'Accept'},{id:'2',name:'Reject'}];
    userList:User[];
    modalss:any;
    productEntries:any=[];
    processerror:any=[];
    units:any;
    model:any = {process_ids:'',user_id:'',approver_user_id:'',status:'',comment:'',reject_comment:''};
  
    userType:number;
    userdetails:any;
    arrEnumStatus:any[];
    
    countryList:Country[];
    stateList:State[];
    bsectorList:BusinessSector[];

  ngOnInit() {
    this.new_app_id = this.activatedRoute.snapshot.queryParams.new_app_id;
    this.app_id = this.activatedRoute.snapshot.queryParams.app;
    this.id = this.activatedRoute.snapshot.queryParams.id;
    
    this.additionservice.getData(this.id).pipe(first())
    .subscribe(res => {
        this.additiondetails = res.data;
    });
  }

}
