import { Component, OnInit } from '@angular/core';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { AuditExecutionChecklistService } from '@app/services/master/checklist/audit-execution-checklist.service';
import { BusinessSectorService } from '@app/services/master/business-sector/business-sector.service';
import { SeverityTimeline } from '@app/services/master/audit-severity-timeline/timeline.service';
import { SubTopicService } from '@app/services/master/sub-topic/sub-topic.service';
import { StandardService } from '@app/services/master/standard/standard.service';
import { ProcessService } from '@app/services/master/process/process.service';

import { BusinessSector } from '@app/models/master/business-sector';
import { SubTopic } from '@app/models/master/sub-topic';
import { Process } from '@app/models/master/process';
import { AuditSeverityTimeline } from '@app/models/master/audit-severity-timeline';
import { Standard } from '@app/services/standard';


@Component({
  selector: 'app-add-audit-execution-checklist',
  templateUrl: './add-audit-execution-checklist.component.html',
  styleUrls: ['./add-audit-execution-checklist.component.scss']
})
export class AddAuditExecutionChecklistComponent implements OnInit {

  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  stdindex:any;
  submittedError = false;
  success:any;
  title = 'Add Audit Execution Checklist';
  btnLabel = 'Save';

  formData:FormData = new FormData();

  standardErrors ='';
  processErrors='';

  StandardclauseList:Array<any> = [];
  timelineList:AuditSeverityTimeline[];
  bsectorList:BusinessSector[];
  subTopicList:SubTopic[];
  standardList:Standard[];
  processList:Process[];
  processEntries:Process[] =[];
  severityErrors:any;
  postiveCommentErrors:any;
  negativeCommentErrors:any;
  StandardErrors:any;
  nameErrors:any;
  standard_error:any;
  findingsErrors:any;
  constructor(private router: Router,private fb:FormBuilder,private errorSummary: ErrorSummaryService,private standardService: StandardService, private SubTopicService: SubTopicService, private SeverityTimeline: SeverityTimeline, private BusinessSectorService: BusinessSectorService, private processService: ProcessService, private AuditExecutionChecklistService: AuditExecutionChecklistService) { }

  ngOnInit() 
  {

    this.standardService.getStandardList().subscribe(res => {
      this.standardList = res['standards'];
    });
    this.processService.getProcessList().pipe(first()).subscribe(res => {
       this.processList = res['processes'];      
    });
    this.SeverityTimeline.getSeverityTimeline().subscribe(res => {
      this.timelineList = res['timeline'];
    });
	
    this.SubTopicService.getSubTopicList().subscribe(res => {
      this.subTopicList = res['subtopics'];
    });

    this.form = this.fb.group({
        standard:[''],
        clauseNo:['',[this.errorSummary.noWhitespaceValidator]],
        clause:['',[this.errorSummary.noWhitespaceValidator]], 
        process:['',[Validators.required]], 
        sub_topic_id:['',[Validators.required]],
        business_sector:['',[Validators.required]],  
        name:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
        interpretation:['',[this.errorSummary.noWhitespaceValidator]],
        expected_evidence:['',[this.errorSummary.noWhitespaceValidator]],
        severity:['',[Validators.required]],
        findings:['',[Validators.required]],
        postiveComment:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        negativeComment:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        file_upload_required:['']	      
      });
  }

  get f() { return this.form.controls; }

  touchStandardClause(){
    this.f.standard.markAsTouched();
    this.f.clauseNo.markAsTouched();
    this.f.clause.markAsTouched();
  }

  addClause()
  {
    this.f.standard.setValidators([Validators.required]);
    //this.f.clauseNo.setValidators([Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]);
    //this.f.clause.setValidators([Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]);
	  this.f.clauseNo.setValidators([Validators.required,Validators.maxLength(255)]);
    //this.f.clause.setValidators([Validators.required,Validators.maxLength(255)]);
	//this.f.clauseNo.setValidators([Validators.required]);
    this.f.clause.setValidators([Validators.required]);

    this.f.standard.updateValueAndValidity();
    this.f.clauseNo.updateValueAndValidity();
    this.f.clause.updateValueAndValidity();

    this.touchStandardClause();

    let standard = this.form.get('standard').value;
    let clauseNo = this.form.get('clauseNo').value;
    let clause = this.form.get('clause').value;

    let selstandard = this.standardList.find(s => s.id ==  standard);

    if(standard == '' || clauseNo == '' || clause == '' ){
      return false;
    }

    let entry= this.StandardclauseList.findIndex(s => s.standard_id ==  standard);
    let expobject:any=[];
    expobject["standard_id"] = selstandard.id;
    expobject["standard_name"] = selstandard.name;//this.registrationForm.get('expname').value;
    expobject["clauseNo"] = clauseNo;
    expobject["clause"] = clause;
    if(entry === -1){
      this.StandardclauseList.push(expobject);
    }else{
      this.StandardclauseList[entry] = expobject;
    }

    this.form.patchValue({
      standard: '',
      clauseNo:'',
      clause:''
    });

    this.getBsectorList();

    this.f.standard.setValidators([]);
    this.f.clauseNo.setValidators([]);
    this.f.clause.setValidators([]);

    this.f.standard.updateValueAndValidity();
    this.f.clauseNo.updateValueAndValidity();
    this.f.clause.updateValueAndValidity();
	this.editStatus=false;

  }
 
  editStatus=false;
  editStandardClause(Id:number)
  {
	this.editStatus=true;
    //this.stdindex = Id;
    //let std = this.StandardclauseList[Id];
    let std = this.StandardclauseList.find(s => s.standard_id ==  Id);
    
    this.form.patchValue({
      standard: std.standard_id,
      clauseNo: std.clauseNo,
      clause:std.clause
    });
  }

  removeStandardClause(Id:number) {
    let index= this.StandardclauseList.findIndex(s => s.standard_id ==  Id);
    this.bsectorList = [];
    this.form.patchValue({business_sector:''});	
    if(index != -1)
    {
      this.StandardclauseList.splice(index,1);
      this.getBsectorList();
    }
      
    
  }

  getSelectedValue(type,val)
  {
    if(type=='process'){
      return this.processList.find(x=> x.id==val).name;
    }else if(type=='business_sector'){
      return this.bsectorList.find(x=> x.id==val).name;
    }else if(type=='severity'){
      return this.timelineList.find(x=> x.id==val).name;
    }
  }

  resetclauseform()
  {
	this.editStatus=false;
    this.form.patchValue({
      standard: '',
      clauseNo: '',
      clause:''
    });
  }

  getBsectorList(){
    this.bsectorList = [];
    this.form.patchValue({business_sector:''});		
   // this.processList = [];
    let standardvals=[];
    
      this.StandardclauseList.forEach(val=>{
        standardvals.push(val.standard_id);   
      })
      
    if(standardvals.length>0)
    {
      this.BusinessSectorService.getBusinessSectorsbystds({standardvals}).subscribe(res => {
        this.bsectorList = res['bsectors'];
        this.form.patchValue({business_sector:''});
        
      });	
    }else{		
      this.bsectorList = [];
      this.form.patchValue({business_sector:''});		
      
    }
  }

  getProcess()
  {

    this.processEntries = [];
    this.processList = [];

    let standardvals=[];
    
    this.StandardclauseList.forEach(val=>{
      standardvals.push(val.standard_id);   
    })
    
    let bsectorvals=this.form.controls.business_sector.value;
    if(standardvals.length>0  && bsectorvals.length>0)
    {
      this.BusinessSectorService.getBusinessSectorGroupsbystds({standardvals,bsectorvals}).subscribe(res => {
        this.processList = res['processes'];
        this.form.patchValue({process:''});
      });	
    }else{		
      this.processList = [];
      this.form.patchValue({process:''});		
    }
  }

  
  onSubmit()
  {
    if (this.form.valid) 
    {
      this.loading = true;

      let standardvals=[];
      this.StandardclauseList.forEach(val=>{
        standardvals.push({standard_id:val.standard_id,clause_no:val.clauseNo,clause:val.clause}); 
      })

      

      let formvalue = this.form.value; 
      
      let fileUploadRequired=0;
      if(formvalue.file_upload_required)
      {
        fileUploadRequired=1;
      }
      formvalue.file_upload_required=fileUploadRequired;
     
      formvalue.standard_clause = standardvals;
	  
      this.AuditExecutionChecklistService.addData(formvalue)
      .pipe(
        first()        
      ).subscribe(res => {
          if(res.status)
          {
            this.success = {summary:res.message};
			      this.buttonDisable = true;
                      
			      setTimeout(() => {
                this.router.navigateByUrl('/master/audit-execution-checklist/list');
            }, this.errorSummary.redirectTime);
          }
          else if(res.status == 0)
          {
            this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};				      
          }
          else
          {			      
            this.error = {summary:res};
          }
          this.loading = false;         
      },
      error => {
          this.error = {summary:error};
          this.loading = false;
      });      
    }
    else
    {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form); 
    }
  }

}
