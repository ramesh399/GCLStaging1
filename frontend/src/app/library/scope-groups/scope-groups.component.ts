import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { BusinessSectorGroupService } from '@app/services/master/business-sector-group/business-sector-group.service';
import { BusinessSectorService } from '@app/services/master/business-sector/business-sector.service';
import { StandardService } from '@app/services/standard.service';
import { Standard } from '@app/services/standard';
import { BusinessSector } from '@app/models/master/business-sector';
import { BusinessSectorGroup } from '@app/models/master/business-sector-group';
import { tap,first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ScopesGroupsService } from '@app/services/library/scopes-groups.service';
import { ScopesGroups } from '@app/models/library/scopesgroups';

@Component({
  selector: 'app-scope-groups',
  templateUrl: './scope-groups.component.html',
  styleUrls: ['./scope-groups.component.scss'],
  providers: [ScopesGroupsService]
})
export class ScopeGroupsComponent implements OnInit {

  title = 'Scopes & Groups';
  standardList:Standard[];
  form : FormGroup;
  loading:any=[];
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  nameErrors = '';
  standard_idErrors = '';
  bsectorList:BusinessSector[];
  bsectorgroupList:BusinessSectorGroup[];
  accrediationList:any[];
  scopeList:any[];
  riskList:any[];
  bgsectorgroupList:any[];
  statuslist:any[];
  modalss:any;
  formData:FormData = new FormData();
  scopeData:any;
  commontxt = commontxt;
  scopesgroups$: Observable<ScopesGroups[]>;
  total$: Observable<number>;
  paginationList = PaginationList;
  userType:number;
  userdetails:any;
  userdecoded:any;
  
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;
  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private standardservice: StandardService, private BusinessSectorService: BusinessSectorService, private router: Router,private fb:FormBuilder, private errorSummary: ErrorSummaryService, private authservice:AuthenticationService, public service: ScopesGroupsService) {
    this.scopesgroups$ = service.scopesgroups$;
    this.total$ = service.total$;
  }

  ngOnInit() {
    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
      }else{
        this.userdecoded=null;
      }
    });
    this.form = this.fb.group({	
      standard_id:['',[Validators.required]],
      business_sector_id:['',[Validators.required]],
      business_sector_group_id:['',[Validators.required]],
      description:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
      scope:['',[Validators.required]],      
      risk:['',[Validators.required]],
      status:['',[Validators.required]],
      accreditation:['',[Validators.required]],
      processes:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
      rcontrols:['',[Validators.required,this.errorSummary.noWhitespaceValidator]]
    });
    /*
    this.standardservice.getStandard().subscribe(res => {
      this.standardList = res['standards'];
    });
   
    this.BusinessSectorService.getBusinessSectorList().subscribe(res => {
      this.bsectorList = res['bsectors'];
    });
    */
    this.service.getStatusList().pipe(first())
    .subscribe(res => {
      this.statuslist  = res.statuslist;
      this.scopeList  = res.scopelist;
	  this.riskList  = res.risklist;	  
      this.standardList  = res.standards;
      this.accrediationList  = res.accrediationlist;
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });

  }

  
  getSelectedValue(val,id)
  {

  }

  get f() { return this.form.controls; } 
  


  listEntries = [];
  
  addData()
  {
    this.f.business_sector_id.markAsTouched();
    this.f.standard_id.markAsTouched();
    this.f.business_sector_group_id.markAsTouched();
    this.f.scope.markAsTouched();
    this.f.risk.markAsTouched();
    this.f.description.markAsTouched();
    this.f.accreditation.markAsTouched();
    this.f.status.markAsTouched();
    this.f.processes.markAsTouched();
    this.f.rcontrols.markAsTouched();
    
    

    if(this.form.valid )
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let business_sector_id = this.form.get('business_sector_id').value;
      let business_sector_group_id = this.form.get('business_sector_group_id').value;
      let standard_id = this.form.get('standard_id').value;
      let scope = this.form.get('scope').value;
      let risk = this.form.get('risk').value;
      let description = this.form.get('description').value;
      let status = this.form.get('status').value;
      let accreditation = this.form.get('accreditation').value;
      let processes = this.form.get('processes').value;
      let rcontrols = this.form.get('rcontrols').value;
      
      let expobject:any={business_sector_id:business_sector_id,business_sector_group_id:business_sector_group_id,standard_id:standard_id,scope:scope, risk:risk, description:description,status:status,accreditation:accreditation,rcontrols:rcontrols,processes:processes};
      
      if(1)
      {
        if(this.scopeData){
          expobject.id = this.scopeData.id;
        }
        
        //this.formData.append('formvalues',JSON.stringify(expobject));
        this.service.addData(expobject)
        .pipe(first())
        .subscribe(res => {
          
            if(res.status){
              
              this.service.customSearch();
              this.formreset();
              this.success = {summary:res.message};
              this.buttonDisable = false;
              
             
            }else if(res.status == 0){
              //this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.enquiryForm)};
              this.error = {summary:res};
            }
            this.loading['button'] = false;
            this.buttonDisable = false;
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
            this.buttonDisable = false;
        });
        
      } else {
        
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.form); 
        
      }   
    }
  }

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  viewData:any
  view(content,data)
  {
    this.viewData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  editStatus=0;
  edit(data) 
  { 
    this.editStatus=1;
    this.scopeData = data;
    
    this.form.patchValue({
      standard_id:data.standard_id,
      business_sector_id:data.business_sector_id,     
      business_sector_group_id:data.business_sector_group_id,
      scope:data.scope,
      risk:data.risk,
      description:data.description,
      accreditation:data.accreditation,
      status:data.status,
      processes:data.processes,
      rcontrols:data.rcontrols,
    });
    this.getBgsectorList(data.standard_id,true);
    this.getBgsectorgroupList(data.business_sector_id,true);
    this.scrollToBottom();  
  }

  remove(content,data) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.formreset();
        this.service.deleteData({id:data.id})
        .pipe(first())
        .subscribe(res => {

            if(res.status){
              this.service.customSearch();
              this.success = {summary:res.message};
              this.buttonDisable = true;
            }else if(res.status == 0){
              this.error = {summary:res};
            }
            this.loading['button'] = false;
            this.buttonDisable = false;
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        });
    }, (reason) => {
    })
    
  
  }


  getBgsectorgroupList(value,empty=false){
    this.bgsectorgroupList = [];
    let standardvals=this.form.controls.standard_id.value;
    //let processvals=this.form.controls.process.value;
    let bsectorvals=value;
    if(standardvals  && bsectorvals )
    {
      this.loading['group'] = true; 
      this.BusinessSectorService.getBusinessSectorGroupsbystds({standardvals,bsectorvals}).subscribe(res => {
        this.loading['group'] = false; 
        this.bgsectorgroupList = res['bsectorgroups'];
        if(!empty){
          this.form.patchValue({business_sector_group_id:''});
        }
      }); 
    }else{    
      this.bgsectorgroupList = [];
      this.form.patchValue({business_sector_group_id:''});    
    }
  }

  getBgsectorList(value,empty=false){
    //let standardvals=this.form.controls.standard_id.value;
    //let processvals=this.standardForm.controls.process.value;
    this.bgsectorgroupList = [];
    this.bsectorList = [];
    
    if(value)
    {
    this.loading['sector'] = true; 
      this.BusinessSectorService.getBusinessSectorsbystds({standardvals:value}).subscribe(res => {
        this.loading['sector'] = false; 
        this.bsectorList = res['bsectors'];
        if(!empty){
          this.form.patchValue({business_sector_id:'',business_sector_group_id:''});
        }
      }); 
    }else{    
      this.bsectorList = [];
      this.form.patchValue({business_sector_id:'',business_sector_group_id:''});    
    }
  }


  formreset()
  {
	this.editStatus=0;  
    this.scopeData = '';
    this.form.reset();
	
	this.form.patchValue({
      standard_id:'',
      business_sector_id:'',     
      business_sector_group_id:'',
      scope:'',
      risk:'',      
      accreditation:'',
      status:''
    });
  }

  
  
  scrollToBottom()
  {
  window.scroll({ 
      top: window.innerHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }

  onSubmit(){}

}
