import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { InspectionPlanService } from '@app/services/audit/inspection-plan.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-view-inspection-plan',
  templateUrl: './view-inspection-plan.component.html',
  styleUrls: ['./view-inspection-plan.component.scss']
})
export class ViewInspectionPlanComponent implements OnInit {

  loading = false;
  error:any;
  id:number;
  success:any;
  inspectionplanEntries:any=[];

  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private InspectionPlanService: InspectionPlanService,private errorSummary: ErrorSummaryService) { }
  
  ngOnInit() {
    this.id=1;  
    
    this.InspectionPlanService.getInspectionPlan(this.id).pipe(first())
      .subscribe(res => {
        this.inspectionplanEntries = res.inspectionplans;      
      },
      error => {
          this.error = error;
          this.loading = false;
      });
    }

}
