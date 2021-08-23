import { Component, OnInit,EventEmitter } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,Form } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Router,ActivatedRoute ,Params } from '@angular/router';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-audit-reviewer-remediation',
  templateUrl: './audit-reviewer-remediation.component.html',
  styleUrls: ['./audit-reviewer-remediation.component.scss']
})
export class AuditReviewerRemediationComponent implements OnInit {
	
  title = 'List Unit Findings';
  loading:any;

  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {
  }
  onSubmit(){

  }
  

}
