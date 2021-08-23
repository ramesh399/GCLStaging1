import { Component, OnInit,Input,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditReportQbsScopeHolder } from '@app/models/audit/audit-qbs-scopeholder';
import { AuditQbsScopeholderService } from '@app/services/audit/audit-qbs-description.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-audit-qbs-scopeholder',
  templateUrl: './audit-qbs-scopeholder.component.html',
  styleUrls: ['./audit-qbs-scopeholder.component.scss']
})
export class AuditQbsScopeholderComponent implements OnInit {
  @Input() cond_viewonly: any;
  title = 'Audit Report QBS Description'; 
  form : FormGroup; 
  id:number;
  audit_id:number;
  unit_id:number;
  descriptioData:any;
  error:any;
  success:any;
  buttonDisable = false;
  dataloaded = false;
  loading:any=[];
  userType:number;
  userdetails:any;
  userdecoded:any;
  editStatus=0;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: AuditQbsScopeholderService, public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
    this.form = this.fb.group({	
      qbs_description:['',[Validators.required, this.errorSummary.noWhitespaceValidator]]
    });

    
    this.loadDetails();
    
    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
      }else{
        this.userdecoded=null;
      }
    });
  }

  loadDetails()
  {
    this.service.getQBSdescription({audit_id:this.audit_id,unit_id:this.unit_id}).pipe(first())
    .subscribe(res => {   

      if(res.status)
      {
        this.form.patchValue({
          qbs_description:res.data,
        });
        this.editStatus=1;
      }
      this.dataloaded = true; 
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });
  }

  get f() { return this.form.controls; }

  addDescription()
  {
    this.f.qbs_description.markAsTouched();

    if(this.form.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let qbs_description = this.form.get('qbs_description').value;

      let expobject:any={unit_id:this.unit_id,audit_id:this.audit_id,qbs_description:qbs_description}

      this.service.addData(expobject)
      .pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.success = {summary:res.message};
          this.buttonDisable = false;
          this.loading['button'] = false;
          this.loadDetails();
        }
      },
      error => {
        this.buttonDisable = false;
          this.error = {summary:error};
          this.loading['button'] = false;
      });
    }
  }

  descriptionFormreset()
  {
    this.editStatus=0;
    this.form.reset();
    
    this.form.patchValue({     
      qbs_description:''
    });
  }

  onSubmit(){ }

}
