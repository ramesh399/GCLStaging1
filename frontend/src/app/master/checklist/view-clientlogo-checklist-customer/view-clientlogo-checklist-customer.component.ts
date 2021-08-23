import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';
import {CustomerClientlogoChecklist} from '@app/models/master/customer-clientlogo-checklist';

import { StandardService } from '@app/services/standard.service';
import { ProcessService } from '@app/services/master/process/process.service';
import {CustomerClientlogoChecklistService} from '@app/services/master/checklist/customer-clientlogo-checklist.service';

@Component({
  selector: 'app-view-clientlogo-checklist-customer',
  templateUrl: './view-clientlogo-checklist-customer.component.html',
  styleUrls: ['./view-clientlogo-checklist-customer.component.scss']
})
export class ViewClientlogoChecklistCustomerComponent implements OnInit {

  constructor(private activatedRoute:ActivatedRoute,private auditReviewerChecklistService:CustomerClientlogoChecklistService) { }
  id:any;
  error = '';
  loading = false;
  auditreviewerdata:CustomerClientlogoChecklist;

  ngOnInit() {

    this.id = this.activatedRoute.snapshot.queryParams.id;   
    this.auditReviewerChecklistService.getCustomerClientlogoChecklist({'id':this.id}).pipe(first())
    .subscribe(res => {
      this.auditreviewerdata = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });

     
  }

}
