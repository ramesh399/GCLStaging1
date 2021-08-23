import { Component, OnInit,Input,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditLivingWageChecklistService } from '@app/services/audit/audit-livingwage-checklist.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first,map } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-audit-livingwage-viewchecklist',
  templateUrl: './audit-livingwage-viewchecklist.component.html',
  styleUrls: ['./audit-livingwage-viewchecklist.component.scss'],
  providers: [AuditLivingWageChecklistService]
  
})
export class AuditLivingwageViewchecklistComponent implements OnInit {
  @Input() cond_viewonly: any;
  title = 'Audit Interview Employee'; 
  form : FormGroup;
  remarkForm : FormGroup;   
  summary:any=[];
  id:number;
  audit_id:number;
  unit_id:number;
  employeeData:any;
  EmployeeData:any;
  migrantlist:any;
  genderlist:any;
  typelist:any;
  error:any;
  success:any;
  buttonDisable = false;
  dataloaded = false;
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  modalss:any;
  loading:any=[];
  answerArr:any;
  livingrequirements:any;
  livingcategorys:any;
  livingexpensesinfo:any;
  foodCategoryID:any;
  currencyCategoryID:any;
  isItApplicable=true;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: AuditLivingWageChecklistService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
   
  }
  remarksdata:any;
  ngOnInit() 
  {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
    /*
    this.remarkForm = this.fb.group({	
      remark:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]]
    });
    */
    this.service.getChecklistQuestions({unit_id:this.unit_id,audit_id:this.audit_id}).pipe(first())
    .subscribe(res => {   
      this.livingrequirements = res.requirements;
      this.livingexpensesinfo = res.expensesinfo;
      this.livingcategorys = res.categorys;
    });

    this.service.getRemarkData({audit_id:this.audit_id,unit_id:this.unit_id,type:'livingwage_list'}).pipe(first())
    .subscribe(res => {    
      this.dataloaded = true;
      if(res!==null)
      {  
        this.remarksdata = res;
        this.isApplicable = res.status;
        if(res.status==1)
        {
          this.isItApplicable=true; 
        }else{
          this.isItApplicable=false;
        }	 
        
        if(res.comments)
        {
          /*
          this.remarkForm.patchValue({
            'remark':res.comments
          });
          */
        }
      }
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
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
  }

 


  
  isApplicable:number;
  isItApp(arg)
  {
    this.isApplicable = arg;
	  if(arg==1)
	  {
		  this.isItApplicable=true;
	  }else{
		  this.isItApplicable=false;
	  }	  
  }
  
  

}
