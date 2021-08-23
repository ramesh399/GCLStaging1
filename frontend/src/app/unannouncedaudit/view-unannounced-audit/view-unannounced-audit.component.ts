import { Component, OnInit,Input,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import {UnannouncedAuditListService} from '@app/services/unannouncedaudit/unannounced-audit-list.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first,map } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import {saveAs} from 'file-saver';

@Component({
  selector: 'app-view-unannounced-audit',
  templateUrl: './view-unannounced-audit.component.html',
  styleUrls: ['./view-unannounced-audit.component.scss']
})
export class ViewUnannouncedAuditComponent implements OnInit {

  id:number;
  audit_id:number;
  unit_id:number;
  error:any;
  userType:number;
  userdetails:any;
  userdecoded:any;
  modalss:any;
  loading:any=[];
  answerArr:any;
  auditdetails:any;
  isItApplicable=true;
  panelOpenState = true;
  
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: UnannouncedAuditListService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
   
  }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
  

    this.service.getUnannouncedAudit({id:this.id}).pipe(first())
    .subscribe(res => {    
      this.auditdetails = res.data;
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

}
