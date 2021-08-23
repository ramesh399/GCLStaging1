import {DecimalPipe} from '@angular/common';
import {Directive, Component, QueryList, ViewChildren } from '@angular/core';
import {Observable} from 'rxjs';
import { Router } from '@angular/router';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl, Form } from '@angular/forms';
import { Invoice } from '@app/models/invoice/invoice';
import {UnannouncedAuditListService} from '@app/services/audit/unannounced-audit-list.service';
import {ListAuditPlanService} from '@app/services/audit/list-audit-plan.service';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
//import { getFileNameFromResponseContentDisposition, saveFile } from '@app/helpers/file-download-helper';
import { first } from 'rxjs/operators';
import {saveAs} from 'file-saver';
import { AuthenticationService } from '@app/services/authentication.service';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { User } from '@app/models/master/user';
import { UserService } from '@app/services/master/user/user.service';
import { BrandService } from '@app/services/master/brand/brand.service';


@Component({
  selector: 'app-list-audit-plan',
  templateUrl: './list-audit-plan.component.html',
  styleUrls: ['./list-audit-plan.component.scss'],
  providers: [ListAuditPlanService]
})
export class ListAuditPlanComponent {

  id:number;
  loading = false;
  success:any;
  error:any;
  listauditplan$: Observable<Invoice[]>;
  total$: Observable<number>;
  statuslist:any=[];
  auditstdlist:any=[];
  riskList:any=[];
  unitList:any=[];
  form : FormGroup;
  app_id:number;
  AuditData:any;
  msgsuccess:any;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  userType:number;
  userdetails:any;
  userdecoded:any;
  auditStatus:any;
  standardList:Standard[];
  franchiseList:User[];
  brandList: any=[];

  
  constructor(private modalService: NgbModal, private fb:FormBuilder, public service: ListAuditPlanService, public uauditservice: UnannouncedAuditListService, public errorSummary: ErrorSummaryService, private router: Router, private authservice:AuthenticationService,private standardservice: StandardService,private userservice: UserService,private brandservice: BrandService) {
    this.listauditplan$ = service.listauditplan$;
    this.total$ = service.total$;   
    this.router.routeReuseStrategy.shouldReuseRoute = () => false;

    this.service.auditStatus().subscribe(data=>{
      this.auditStatus = data.enumStatus;
      this.statuslist  = data.status;
    });

    this.service.getReviewOptions().subscribe(res => {
		  this.riskList = res['risklist'];     
    });
	
	  this.standardservice.getStandard().subscribe(res => {
		  this.standardList = res['standards'];     
    });

    this.brandservice.getData().subscribe(res => {
      this.brandList = res.data;
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

    this.form = this.fb.group({
      app_id:[''],
      standard_id:['',[Validators.required]],
      unit_id:['',[Validators.required]],     
    });
	
	this.userservice.getAllUser({type:3}).pipe(first())
	.subscribe(res => {
	  this.franchiseList = res.users;
	},
	error => {
		this.error = {summary:error};
	});

  }
  
  get f() { return this.form.controls; }

  downloadInvoiceFile(invoicecode,invoice_id,offer_id){
    this.service.downloadInvoiceFile({invoice_id,offer_id})
    .subscribe(res => {
        saveAs(new Blob([res],{type:'application/pdf'}),'invoice_'+invoicecode+'.pdf');
    });
  }
  

  onSort({column, direction}: SortEvent) {
    // resetting other headers
    //console.log('sdfsdfdsf');
    this.headers.forEach(header => {
      if (header.sortable !== column) {
        header.direction = '';
      }
    });

    this.service.sortColumn = column;
    this.service.sortDirection = direction;
  }
  
  getSelectedValue(val)
  {
    return this.standardList.find(x=> x.id==val).code;    
  }

  getSelectedBrandValue(val)
  {
    return this.brandList.find(x=> x.id==val).brand_name;    
  }

  getSelectedStdValue(val)
  {
    return this.auditstdlist.find(x=> x.id==val).name;    
  }

  getSelectedUnitValue(val)
  {
    return this.unitList.find(x=> x.id==val).name;    
  }

  modalss:any;
  openmodal(content,data) 
  {
    this.resetform();
    this.AuditData = data;
    this.id = this.AuditData.id;
    this.app_id = this.AuditData.app_id;
    this.auditstdlist = this.AuditData.audit_standard;
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  getUnits(value)
  {
    this.unitList = [];
    if(value)
    {
      this.uauditservice.getUnitsbystds({app_id:this.app_id,standard_id:value}).subscribe(res => {
        if(res.status)
        {
          this.unitList = res.units;
        }
      });
    }
    else{    
      this.unitList = [];
      this.form.patchValue({unit_id:''});    
    }
  }

  SubmitForm()
  {
    this.f.standard_id.markAsTouched();
    this.f.unit_id.markAsTouched();

    if(this.form.valid)
    {
      this.loading = true;
      let standard_id = this.form.get('standard_id').value;
      let unit_id = this.form.get('unit_id').value;

      this.uauditservice.SaveAudit({id:this.id,app_id:this.app_id,standard_id:standard_id,unit_id:unit_id}).subscribe(res => {
        if(res.status)
        {
          this.msgsuccess = res.message;
          this.resetform();
          setTimeout(() => {
            this.msgsuccess = '';
            this.modalss.close('');
          },this.errorSummary.redirectTime);
        }
        else
        {
          this.error = {summary:res};
        }
        this.loading = false;
      });
    }
    else
    {
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.form);
    }
  }


  resetform()
  {
    this.form.reset();
    this.loading = false;

    this.form.patchValue({
      standard_id:'',
      unit_id:''
    });
  }
  
  getSelectedFranchiseValue(val)
  {
    return this.franchiseList.find(x=> x.id==val).osp_details;    
  }

}