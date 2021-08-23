import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditReviewerChecklistService } from '@app/services/master/checklist/audit-reviewer-checklist.service';
import { AuditReviewerChecklist } from '@app/models/master/audit-reviewer-checklist';
import {Observable} from 'rxjs';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-view-audit-reviewer-checklist',
  templateUrl: './view-audit-reviewer-checklist.component.html',
  styleUrls: ['./view-audit-reviewer-checklist.component.scss']
})
export class ViewAuditReviewerChecklistComponent implements OnInit {

  constructor(private activatedRoute:ActivatedRoute,private auditReviewerChecklistService:AuditReviewerChecklistService) { }
  id:any;
  error = '';
  loading = false;
  auditreviewerdata:AuditReviewerChecklist;

  ngOnInit() {

    this.id = this.activatedRoute.snapshot.queryParams.id;   
    this.auditReviewerChecklistService.getAuditReviewerChecklist({'id':this.id}).pipe(first())
    .subscribe(res => {
      this.auditreviewerdata = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });

     
  }

}