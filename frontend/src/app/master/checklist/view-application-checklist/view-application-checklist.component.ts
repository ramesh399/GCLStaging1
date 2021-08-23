import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { ApplicationChecklistService } from '@app/services/master/checklist/application-checklist.service';
import { ApplicationChecklist } from '@app/models/master/application-checklist';
import {Observable} from 'rxjs';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-view-application-checklist',
  templateUrl: './view-application-checklist.component.html',
  styleUrls: ['./view-application-checklist.component.scss']
})
export class ViewApplicationChecklistComponent implements OnInit {

  constructor(private activatedRoute:ActivatedRoute,private applicationChecklistService:ApplicationChecklistService) { }
  
  title = 'View Application Checklist';
  id:any;
  error = ''; 
  loading = false;
  applicationdata:ApplicationChecklist;
  category:any;
  ngOnInit() {

    this.id = this.activatedRoute.snapshot.queryParams.id;  

    this.applicationChecklistService.getApplicationChecklist({'id':this.id}).pipe(first())
    .subscribe(res => {
      this.applicationdata = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });
  }

}
