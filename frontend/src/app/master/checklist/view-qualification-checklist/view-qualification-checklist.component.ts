import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { QualificationChecklistService } from '@app/services/master/checklist/qualification-checklist.service';
import { QualificationChecklist } from '@app/models/master/qualification-checklist';
import {Observable} from 'rxjs';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-view-qualification-checklist',
  templateUrl: './view-qualification-checklist.component.html',
  styleUrls: ['./view-qualification-checklist.component.scss']
})
export class ViewQualificationChecklistComponent implements OnInit {

  constructor(private activatedRoute:ActivatedRoute,private qualificationChecklistService:QualificationChecklistService) { }
  id:any;
  error = '';
  loading = false;
  qualificationdata:QualificationChecklist;

  ngOnInit() {

    this.id = this.activatedRoute.snapshot.queryParams.id;   
    this.qualificationChecklistService.getQualificationChecklist({'id':this.id}).pipe(first())
    .subscribe(res => {
      this.qualificationdata = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });

     
  }

}