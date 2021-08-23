import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditExecutionChecklistService } from '@app/services/master/checklist/audit-execution-checklist.service';
import { AuditExecutionChecklist } from '@app/models/master/audit-execution-checklist';
import {Observable} from 'rxjs';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-view-audit-execution-checklist',
  templateUrl: './view-audit-execution-checklist.component.html',
  styleUrls: ['./view-audit-execution-checklist.component.scss']
})
export class ViewAuditExecutionChecklistComponent implements OnInit {

  constructor(private activatedRoute:ActivatedRoute,private AuditExecutionChecklistService: AuditExecutionChecklistService) { }
  id:any;
  error = '';
  loading = false;
  auditexecutiondata:AuditExecutionChecklist;

  ngOnInit() {

    this.id = this.activatedRoute.snapshot.queryParams.id;   
    this.AuditExecutionChecklistService.getAuditExecutionChecklist({'id':this.id}).pipe(first())
    .subscribe(res => {
      this.auditexecutiondata = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });

  }

}
