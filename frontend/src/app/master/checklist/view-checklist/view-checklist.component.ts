import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ChecklistService } from '@app/services/master/checklist/checklist.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-view-checklist',
  templateUrl: './view-checklist.component.html',
  styleUrls: ['./view-checklist.component.scss']
})
export class ViewChecklistComponent implements OnInit {

  title = '';
  btnLabel = 'Update';
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  category:number;
  applicationdata:any=[];
  success:any;
  nameErrors='';

  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private checklistService: ChecklistService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.category = this.activatedRoute.snapshot.queryParams.category;
    
    if(this.category == 2){
      this.title = 'View Application Unit Review Checklist';
    }else{
      this.title = 'View Application Review Checklist';
    }

    this.checklistService.getChecklist(this.id).pipe(first())
    .subscribe(res => {
      this.applicationdata = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });
  }

}
