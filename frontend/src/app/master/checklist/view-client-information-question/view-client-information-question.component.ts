import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';

import {ClientInformationChecklist} from '@app/models/master/client-information-checklist';
import {ClientInformationChecklistService} from '@app/services/master/checklist/client-information-checklist.service';

@Component({
  selector: 'app-view-client-information-question',
  templateUrl: './view-client-information-question.component.html',
  styleUrls: ['./view-client-information-question.component.scss']
})
export class ViewClientInformationQuestionComponent implements OnInit {

  constructor(private activatedRoute:ActivatedRoute,private auditReviewerChecklistService:ClientInformationChecklistService) { }
  id:any;
  error = '';
  loading = false;
  auditreviewerdata:ClientInformationChecklist;

  ngOnInit() {

    this.id = this.activatedRoute.snapshot.queryParams.id;   
    this.auditReviewerChecklistService.getClientInformationChecklist({'id':this.id}).pipe(first())
    .subscribe(res => {
      this.auditreviewerdata = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });

     
  }
}
