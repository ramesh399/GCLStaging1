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
import { ProcessAdditionService } from '@app/services/change-scope/process-addition.service';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';

@Component({
  selector: 'app-process-view',
  templateUrl: './process-view.component.html',
  styleUrls: ['./process-view.component.scss']
})
export class ProcessViewComponent implements OnInit {

  constructor(private enquiryDetail:EnquiryDetailService, private userservice: UserService,private activatedRoute:ActivatedRoute,
  private applicationDetail:ApplicationDetailService, private modalService: NgbModal,private router:Router,private authservice:AuthenticationService, private errorSummary: ErrorSummaryService, private processService:ProcessService,private additionservice: ProcessAdditionService) {  }

  appform : any = {};
  processList: Process[];
  unitprocessList:any = [];
  additiondata:any = [];
  process_ids=[];
  userdecoded:any;
  id:number;
  app_id:number;
  error:any;
  success:any;
  loading:any=[];
  applicationdata:Application;
  process_names:any=[];
  panelOpenState = true;
  approvalStatusList = [];//[{id:'1',name:'Accept'},{id:'2',name:'Reject'}];
  userList:User[];
  modalss:any;
  processEntries:any=[];
  processerror:any=[];
  units:any;
  model:any = {process_ids:'',user_id:'',approver_user_id:'',status:'',comment:'',reject_comment:''};

  userType:number;
  userdetails:any;
  arrEnumStatus:any[];  

  ngOnInit() {
    this.app_id = this.activatedRoute.snapshot.queryParams.app;
    this.units = this.activatedRoute.snapshot.queryParams.units;
    this.id = this.activatedRoute.snapshot.queryParams.id;

    this.loading['data'] = true;
    this.additionservice.getApplication({id:this.id,app_id:this.app_id,units:this.units,exclude:1})
    .pipe( tap(res=>{
          
          this.processService.getProcessList().pipe(first()).subscribe(resp => {
          this.processList = resp['processes'];   
          this.applicationdata.units.forEach(unit=>{
            //let pl = this.processList.filter(x=>selprocess.includes(x.id));
            this.unitprocessList[unit.id] = [...this.processList];


            if(unit.process_ids && unit.process_ids.length>0){
              unit.process_ids.forEach(existunit=>{
                let index= this.unitprocessList[unit.id].findIndex(s => s.id ==  existunit.id);
              if(index != -1)
                this.unitprocessList[unit.id].splice(index,1);
              })
            }
            


        })
      });
        
      })
      ,first())
    .subscribe(res => {
      this.loading['data'] = false;
      this.applicationdata = res['appdata'];
      this.additiondata = res['additionDetails'];
      this.process_names = res['processnames'];
      if(this.id && res['unitsprocessdetails']){
        
        this.applicationdata.units.forEach(unit=>{
          this.processEntries[unit.id] = res['unitsprocessdetails'][unit.id];
        })
      }else{
        this.applicationdata.units.forEach(unit=>{
          this.processEntries[unit.id] = [];
        })
      }
      //this.approvalStatusList = res['approvalStatusList'];
      //this.arrEnumStatus = res['arrEnumStatus'];

      
      
    },
    error => {
        this.error = {summary:error};
        this.loading['data'] = false;
    });
    

  }

  downloadCompanyFile(filename){
    this.enquiryDetail.downloadCompanyFile({id:this.id})
    .subscribe(res => {
      
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
      this.modalss.close('');
    });
  }

  open(content) {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }


}
