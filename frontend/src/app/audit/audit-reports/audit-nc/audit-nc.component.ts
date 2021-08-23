import { Component, OnInit,Input } from '@angular/core';
import { first,tap,map } from 'rxjs/operators';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl, Form } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditNcnReportService } from '@app/services/audit/audit-ncnreport.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-audit-nc',
  templateUrl: './audit-nc.component.html',
  styleUrls: ['./audit-nc.component.scss']
})
export class AuditNcComponent implements OnInit {
  @Input() cond_viewonly: any;
  constructor(private activatedRoute:ActivatedRoute,private fb:FormBuilder,private router: Router,public service:AuditNcnReportService,public errorSummary: ErrorSummaryService, private modalService: NgbModal, private authservice:AuthenticationService) { }
  id:number;
  audit_id:any;
  unit_id:any;
  form : FormGroup; 
  error:any;
  success:any;
  buttonDisable = false;
  dataloaded = false;
  loading:any=[];
  editStatus=0;
  userType:number;
  userdetails:any;
  userdecoded:any;

  effectiveness_of_corrective_actions='';
  audit_team_recommendation='';
  summary_of_evidence='';
  potential_high_risk_situations='';
  entities_and_processes_visited='';
  people_interviewed='';
  type_of_documents_reviewed='';

  showTextarea_effectiveness_of_corrective_actions=0;
  showTextarea_audit_team_recommendation=0;
  showTextarea_summary_of_evidence=0;
  showTextarea_potential_high_risk_situations=0;
  showTextarea_entities_and_processes_visited=0;
  showTextarea_people_interviewed=0;
  showTextarea_type_of_documents_reviewed=0;


  ngOnInit() 
  {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
    this.form = this.fb.group({	
      effectiveness_of_corrective_actions:['',[Validators.required, this.errorSummary.noWhitespaceValidator]], 
      audit_team_recommendation:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      summary_of_evidence:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      potential_high_risk_situations:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      entities_and_processes_visited:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      people_interviewed:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      type_of_documents_reviewed:['',[Validators.required, this.errorSummary.noWhitespaceValidator]]	
    });


    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
      }else{
        this.userdecoded=null;
      }
    });

    this.loadDetails();
  }
  unitdetails:any=[];
  unitWiseFindingsContent:any=[];
  loadDetails()
  {
    this.showTextarea_effectiveness_of_corrective_actions=0;
    this.showTextarea_audit_team_recommendation=0;
    this.showTextarea_summary_of_evidence=0;
    this.showTextarea_potential_high_risk_situations=0;
    this.showTextarea_entities_and_processes_visited=0;
    this.showTextarea_people_interviewed=0;
    this.showTextarea_type_of_documents_reviewed=0;

    this.service.getNcn({audit_id:this.audit_id,unit_id:this.unit_id}).pipe(first())
    .subscribe(res => {    
      if(res.status)
      {
        this.dataloaded = true; 
        this.unitdetails = res.unitdetails;
        this.unitWiseFindingsContent = res.unitWiseFindingsContent;
        
        this.effectiveness_of_corrective_actions=res.effectiveness_of_corrective_actions?res.effectiveness_of_corrective_actions:'';
        this.audit_team_recommendation=res.audit_team_recommendation?res.audit_team_recommendation:'';
        this.summary_of_evidence=res.summary_of_evidence?res.summary_of_evidence:'';
        this.potential_high_risk_situations=res.potential_high_risk_situations?res.potential_high_risk_situations:'';
        this.entities_and_processes_visited=res.entities_and_processes_visited?res.entities_and_processes_visited:'';
        this.people_interviewed=res.people_interviewed?res.people_interviewed:'';
        this.type_of_documents_reviewed=res.type_of_documents_reviewed?res.type_of_documents_reviewed:'';

        this.form.patchValue({
          effectiveness_of_corrective_actions:this.effectiveness_of_corrective_actions, 
          audit_team_recommendation:this.audit_team_recommendation,
          summary_of_evidence:this.summary_of_evidence,
          potential_high_risk_situations:this.potential_high_risk_situations,
          entities_and_processes_visited:this.entities_and_processes_visited,
          people_interviewed:this.people_interviewed,
          type_of_documents_reviewed:this.type_of_documents_reviewed	
        });
      }
    },
    error => {
        this.error = error;
    });
  }

  get f() { return this.form.controls; }


  fnAdd(type)
  {
    if(type=='effectiveness_of_corrective_actions')
    {
      if(this.showTextarea_effectiveness_of_corrective_actions)
      {
        this.showTextarea_effectiveness_of_corrective_actions = 0;
      }
      else
      {
        this.showTextarea_effectiveness_of_corrective_actions = 1;
      }
    }
    else if(type=='audit_team_recommendation')
    {
      if(this.showTextarea_audit_team_recommendation)
      {
        this.showTextarea_audit_team_recommendation = 0;
      }
      else
      {
        this.showTextarea_audit_team_recommendation = 1;
      }
    } 
    else if(type=='summary_of_evidence')
    {
      if(this.showTextarea_summary_of_evidence)
      {
        this.showTextarea_summary_of_evidence = 0;
      }
      else
      {
        this.showTextarea_summary_of_evidence = 1;
      }
      
    }
    else if(type=='potential_high_risk_situations')
    {
      if(this.showTextarea_potential_high_risk_situations)
      {
        this.showTextarea_potential_high_risk_situations = 0;
      }
      else
      {
        this.showTextarea_potential_high_risk_situations = 1;
      }
    } 
    else if(type=='entities_and_processes_visited')
    {
      if(this.showTextarea_entities_and_processes_visited)
      {
        this.showTextarea_entities_and_processes_visited = 0;
      }
      else
      {
        this.showTextarea_entities_and_processes_visited = 1;
      }
    } 
    else if(type=='people_interviewed')
    {
      if(this.showTextarea_people_interviewed)
      {
        this.showTextarea_people_interviewed = 0;
      }
      else
      {
        this.showTextarea_people_interviewed = 1;
      }
    } 
    else if(type=='type_of_documents_reviewed')
    {
      if(this.showTextarea_type_of_documents_reviewed)
      {
        this.showTextarea_type_of_documents_reviewed = 0;
      }
      else
      {
        this.showTextarea_type_of_documents_reviewed = 1;
      }
    } 
  }

  proceedtoSubmit = 0;
  fnSave(type)
  {
    let expobject:any=[];
    let fieldval = this.form.get(type).value;
    
    if(type=='effectiveness_of_corrective_actions')
    {
      
      this.f.effectiveness_of_corrective_actions.markAsTouched();

      if(this.f.effectiveness_of_corrective_actions.errors)
      {
        return false;
      }

    }
    else if(type=='audit_team_recommendation')
    {
      
      this.f.audit_team_recommendation.markAsTouched();

      if(this.f.audit_team_recommendation.errors)
      {
        return false;
      }

    } 
    else if(type=='summary_of_evidence')
    {
      
      this.f.summary_of_evidence.markAsTouched();

      if(this.f.summary_of_evidence.errors)
      {
        return false;
      }
    }
    else if(type=='potential_high_risk_situations')
    {
      
      this.f.potential_high_risk_situations.markAsTouched();

      if(this.f.potential_high_risk_situations.errors)
      {
        return false;
      }
    } 
    else if(type=='entities_and_processes_visited')
    {
      
      this.f.entities_and_processes_visited.markAsTouched();

      if(this.f.entities_and_processes_visited.errors)
      {
        return false;
      }
    } 
    else if(type=='people_interviewed')
    {
      
      this.f.people_interviewed.markAsTouched();

      if(this.f.people_interviewed.errors)
      {
        return false;
      }
    } 
    else if(type=='type_of_documents_reviewed')
    {
      
      this.f.type_of_documents_reviewed.markAsTouched();

      if(this.f.type_of_documents_reviewed.errors)
      {
        return false;
      }
    } 

    
    this.buttonDisable = true;
    this.loading['button'] = true;

    expobject={audit_id:this.audit_id,unit_id:this.unit_id,fieldvalue:fieldval,type:type};

    this.service.addData(expobject)
    .pipe(first())
    .subscribe(res => {

    
        if(res.status){
          this.success = {summary:res.message};
          this.buttonDisable = false;
          this.loading['button'] = false;
          this.loadDetails();
          
        }else if(res.status == 0){
          this.error = {summary:res};
        }
        this.loading['button'] = false;
        this.buttonDisable = false;
    },
    error => {
      this.error = {summary:error};
      this.loading['button'] = false;
      this.buttonDisable = false;
      this.error = {summary:this.errorSummary.errorSummaryText};
    });
    
  }

  fnClose(type)
  {
    if(type=='effectiveness_of_corrective_actions')
    {
        this.showTextarea_effectiveness_of_corrective_actions = 0;
    }
    else if(type=='audit_team_recommendation')
    {
      this.showTextarea_audit_team_recommendation = 0;
    } 
    else if(type=='summary_of_evidence')
    {
      this.showTextarea_summary_of_evidence = 0;
    }
    else if(type=='potential_high_risk_situations')
    {
      this.showTextarea_potential_high_risk_situations = 0;
    } 
    else if(type=='entities_and_processes_visited')
    {
      this.showTextarea_entities_and_processes_visited = 0;
    } 
    else if(type=='people_interviewed')
    {
      this.showTextarea_people_interviewed = 0;
    } 
    else if(type=='type_of_documents_reviewed')
    {
      this.showTextarea_type_of_documents_reviewed = 0;
    } 
  }
  
  
}
