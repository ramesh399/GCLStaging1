import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { BusinessSectorGroupService } from '@app/services/master/business-sector-group/business-sector-group.service';
import { StandardService } from '@app/services/standard.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { BusinessSectorService } from '@app/services/master/business-sector/business-sector.service';
import { ProcessService } from '@app/services/master/process/process.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { Standard } from '@app/services/standard';
import { BusinessSector } from '@app/models/master/business-sector';
import { Process } from '@app/models/master/process';
import { first,takeUntil } from 'rxjs/operators';
import { Subject,ReplaySubject } from 'rxjs';

@Component({
  selector: 'app-edit-business-sector-group',
  templateUrl: '../add-business-sector-group/add-business-sector-group.component.html',
  styleUrls: ['./edit-business-sector-group.component.scss']
})
export class EditBusinessSectorGroupComponent implements OnInit {

  title = 'Edit Business Sector Group';
  btnLabel = 'Update';
  standardList:Standard[];
  bsectorList:BusinessSector[];
  processList:Process[];
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  success:any;
  bsectorgroupval:any;
  submittedError = false;
  nameErrors = '';
  group_codeErrors:any = '';
  constructor(private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private standardservice: StandardService,private processService: ProcessService,private BusinessSectorService: BusinessSectorService,private BusinessSectorGroupService:BusinessSectorGroupService,private errorSummary: ErrorSummaryService) { }

  getSelectedValue(type,val)
  {
    if(type=='process'){
      return this.processList.find(x=> x.id==val).name;
    }
  }
  private _onDestroy = new Subject<void>();

  ngOnInit() {
  this.id = this.activatedRoute.snapshot.queryParams.id;

  this.standardservice.getStandard().subscribe(res => {
    this.standardList = res['standards'];
  });

  this.BusinessSectorGroupService.getBusinessSectorGroup(this.id).pipe(first())
    .subscribe(res => {
      let bsectorgroupvals = res.data;
      let process=[];
    
      this.form.patchValue({process:process});
      this.form.patchValue(bsectorgroupvals);
    },
    error => {
        this.error = error;
        this.loading = false;
    });
  
  this.BusinessSectorService.getBusinessSectorList().subscribe(res => {
    this.bsectorList = res['bsectors'];
  });

  this.processService.getProcessList().subscribe(res => {
    this.processList = res['processes'];
    this.filteredprocessMulti.next(this.processList.slice());
  });
    

	this.form = this.fb.group({
      id:[''],
      standard_id:['',[Validators.required]],
      business_sector_id:['',[Validators.required]],
      process:[''], 
      processFilterCtrl:[''],
      group_code:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z0-9 \'\-+%/&,().-]+$")]],      
      group_details:['',[this.errorSummary.noWhitespaceValidator]] 	  
    });

    this.f.processFilterCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterProcess();
      });
  }

  public filteredprocessMulti: ReplaySubject<Process[]> = new ReplaySubject<Process[]>(1);
  private filterProcess() {
    if (!this.processList) {
      return;
    }
    // get the search keyword
    let search = this.f.processFilterCtrl.value;
    if (!search) {
      this.filteredprocessMulti.next(this.processList.slice());
      return;
    } else {
      search = search.toLowerCase();
    }
    // filter the banks
    this.filteredprocessMulti.next(
      this.processList.filter(p => p.name.toLowerCase().indexOf(search) > -1)
    );
  }

  get f() { return this.form.controls; } 
  
  onSubmit(){
    //console.log(this.form.valid);
    //console.log(this.form.value);
    if (this.form.valid) {
      
      this.loading = true;
      
      this.BusinessSectorGroupService.updateData(this.form.value)
      .pipe(
        first()        
      )
      .subscribe(res => {
        //console.log(res);
          if(res.status){
			this.success = {summary:res.message};
			this.buttonDisable = true;
            setTimeout(()=>this.router.navigate(['/master/business-sector-group/list']),this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};				      
          }else{			      
            this.error = {summary:res};
          }
          this.loading = false;
         
      },
      error => {
          this.error = {summary:error};
          this.loading = false;
      });
      //console.log('sdfsdfdf');
    } else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form); 
      
    }
  }

}
