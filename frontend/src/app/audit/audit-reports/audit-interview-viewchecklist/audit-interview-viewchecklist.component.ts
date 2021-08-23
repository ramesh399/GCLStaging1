import { Component, OnInit,Input,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditReportInterviewEmployee } from '@app/models/audit/audit-interview-employee';
import { AuditReportInterviewEmployeeService } from '@app/services/audit/audit-interview-employee.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first,map } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-audit-interview-viewchecklist',
  templateUrl: './audit-interview-viewchecklist.component.html',
  styleUrls: ['./audit-interview-viewchecklist.component.scss']
})
export class AuditInterviewViewchecklistComponent implements OnInit {
  @Input() cond_viewonly: any;
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
  interviewrequirements:any;
  isItApplicable=true;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: AuditReportInterviewEmployeeService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
   
  }

  ngOnInit() {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;



    this.service.getInterviewchecklistQuestions({audit_id:this.audit_id,unit_id:this.unit_id}).pipe(first())
    .subscribe(res => {    
      this.interviewrequirements = res.requirements;
      this.answerArr = res.answer;
      
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

  isApplicable:number;
  isItApp(arg)
  {
    this.isApplicable = arg;
	  if(arg==1)
	  {
		  this.isItApplicable=true;
	  }else{
		  this.isItApplicable=false;
	  }	  
  }

}
