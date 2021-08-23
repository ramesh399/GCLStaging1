import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { first } from 'rxjs/operators';

import { StandardService } from '@app/services/standard.service';
import { ProcessService } from '@app/services/master/process/process.service';
import {HqClientlogoChecklistService} from '@app/services/master/checklist/hq-clientlogo-checklist.service';
import {HqClientlogoChecklist} from '@app/models/master/hq-clientlogo-checklist';

@Component({
  selector: 'app-view-clientlogo-checklist-hq',
  templateUrl: './view-clientlogo-checklist-hq.component.html',
  styleUrls: ['./view-clientlogo-checklist-hq.component.scss']
})
export class ViewClientlogoChecklistHqComponent implements OnInit {

  constructor(private activatedRoute:ActivatedRoute,private auditReviewerChecklistService:HqClientlogoChecklistService) { }
  id:any;
  error = '';
  loading = false;
  auditreviewerdata:HqClientlogoChecklist;

  ngOnInit() {

    this.id = this.activatedRoute.snapshot.queryParams.id;   
    this.auditReviewerChecklistService.getHqClientlogoChecklist({'id':this.id}).pipe(first())
    .subscribe(res => {
      this.auditreviewerdata = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });

     
  }

}
