import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { BusinessSectorGroupService } from '@app/services/master/business-sector-group/business-sector-group.service';
import { BusinessSectorGroup } from '@app/models/master/business-sector-group';
import {Observable} from 'rxjs';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-view-business-sector-group',
  templateUrl: './view-business-sector-group.component.html',
  styleUrls: ['./view-business-sector-group.component.scss']
})
export class ViewBusinessSectorGroupComponent implements OnInit {

  constructor(private activatedRoute:ActivatedRoute,private bsectorgroupService:BusinessSectorGroupService) { }
  id:any;
  error = '';
  loading = false;
  bsectorgroup:BusinessSectorGroup;

  ngOnInit() {

    this.id = this.activatedRoute.snapshot.queryParams.id;   
    this.bsectorgroupService.getBusinessSectorGroupView({'id':this.id}).pipe(first())
    .subscribe(res => {
      this.bsectorgroup = res['data'];
    },
    error => {
        this.error = error;
        this.loading = false;
    });

     
  }

}
