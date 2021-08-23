import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditPlanningChecklistService } from '@app/services/master/checklist/audit-planning-checklist.service';
import { AuditPlanningChecklist } from '@app/models/master/audit-planning-checklist';
import {Observable} from 'rxjs';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-view-audit-planning-checklist',
  templateUrl: './view-audit-planning-checklist.component.html',
  styleUrls: ['./view-audit-planning-checklist.component.scss']
})
export class ViewAuditPlanningChecklistComponent implements OnInit {

  constructor(private activatedRoute:ActivatedRoute,private auditPlanningChecklistService:AuditPlanningChecklistService) { }
  
  title = '';
  id:any;
  category:number;   
  error = '';
  loading = false;
  auditplanningdata:AuditPlanningChecklist;

  ngOnInit() {

    this.id = this.activatedRoute.snapshot.queryParams.id;  
	this.category = this.activatedRoute.snapshot.queryParams.category;	
	
	if(this.category == 2){
      this.title = 'View Audit Planning Unit Review Checklist';
    }else{
      this.title = 'View Audit Planning Review Checklist';
    }
	
    this.auditPlanningChecklistService.getAuditPlanningChecklist({'id':this.id}).pipe(first())
    .subscribe(res => {
      this.auditplanningdata = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });

     
  }

}