import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { AddTcRoyaltyListService } from '@app/services/master/franchise/add-tc-royalty.service';
import { StandardService } from '@app/services/standard.service';
import { User } from '@app/models/master/user';
import {Observable} from 'rxjs';
import { first } from 'rxjs/operators';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import {AddRoyalty} from '@app/models/master/add-Royalty';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';


@Component({
  selector: 'app-tc-royalty-fee',
  templateUrl: './tc-royalty-fee.component.html',
  styleUrls: ['./tc-royalty-fee.component.scss'],
  providers: [AddTcRoyaltyListService]
})
export class TcRoyaltyFeeComponent implements OnInit {

  id:any;
  franchise_id:any;
  error:any;
  success:any;
  userdata:User;
  standardList:any; 
  form : FormGroup;
  modalss:any;
  standard_idErrors:any = '';
  buttonDisable = false;
  addRoyalty$: Observable<AddRoyalty[]>;
  total$: Observable<number>; 
  paginationList = PaginationList;
  commontxt = commontxt;
  standardRights:any={};
  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute,private userService:UserService,public service:AddTcRoyaltyListService,public standardservice:StandardService,private fb:FormBuilder,public errorSummary: ErrorSummaryService) 
  {
    this.addRoyalty$ = service.addRoyalty$;
    this.total$ = service.total$;		   
	
	  window.scroll({ 
      top: 0, 
      left: 0, 
      behavior: 'smooth' 
    });
  }

  ngOnInit() 
  {
    this.id = this.activatedRoute.snapshot.queryParams.id;   
    this.franchise_id = this.activatedRoute.snapshot.queryParams.franchise_id;   
    
    this.standardservice.getStandard().subscribe(res => {
      this.standardList = res['standards'];
    });
    this.userService.getTcFeeRights().subscribe(res => {
      this.standardRights = res;
    });

    this.form = this.fb.group({	
      standard_id:['',[Validators.required]],			
      single_domestic_invoice_fee_for_oss_to_customer:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]],      
	  single_export_invoice_fee_for_oss_to_customer:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]],      
      multiple_domestic_invoice_fee_for_oss_to_customer:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]],      
	  multiple_export_invoice_fee_for_oss_to_customer:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]],      	  
	  single_invoice_fee_for_hq_to_oss:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]],
      multiple_invoice_fee_for_hq_to_oss:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]]     
    });	   
  }

  get f() { return this.form.controls; }

  gisListEntries = [];
  gisIndex:number=null;
  loading:any=[];
  addData()
  {
    this.standard_idErrors = '';
    this.f.standard_id.markAsTouched();
    this.f.single_domestic_invoice_fee_for_oss_to_customer.markAsTouched();
	this.f.single_export_invoice_fee_for_oss_to_customer.markAsTouched();	
	this.f.multiple_domestic_invoice_fee_for_oss_to_customer.markAsTouched();
	this.f.multiple_export_invoice_fee_for_oss_to_customer.markAsTouched();
	
    this.f.single_invoice_fee_for_hq_to_oss.markAsTouched();    
    this.f.multiple_invoice_fee_for_hq_to_oss.markAsTouched();
           
    if(this.form.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true;     
      
      let standard_id = this.form.get('standard_id').value;  
      let single_domestic_invoice_fee_for_oss_to_customer = this.form.get('single_domestic_invoice_fee_for_oss_to_customer').value;
	  let single_export_invoice_fee_for_oss_to_customer = this.form.get('single_export_invoice_fee_for_oss_to_customer').value;
	  let multiple_domestic_invoice_fee_for_oss_to_customer = this.form.get('multiple_domestic_invoice_fee_for_oss_to_customer').value;
	  let multiple_export_invoice_fee_for_oss_to_customer = this.form.get('multiple_export_invoice_fee_for_oss_to_customer').value;
	  
      let single_invoice_fee_for_hq_to_oss = this.form.get('single_invoice_fee_for_hq_to_oss').value;      
      let multiple_invoice_fee_for_hq_to_oss = this.form.get('multiple_invoice_fee_for_hq_to_oss').value;  
          
      let expobject:any={};

      expobject = {franchise_id:this.franchise_id,standard_id:standard_id,single_domestic_invoice_fee_for_oss_to_customer:single_domestic_invoice_fee_for_oss_to_customer,single_export_invoice_fee_for_oss_to_customer:single_export_invoice_fee_for_oss_to_customer,	  multiple_domestic_invoice_fee_for_oss_to_customer:multiple_domestic_invoice_fee_for_oss_to_customer,multiple_export_invoice_fee_for_oss_to_customer:multiple_export_invoice_fee_for_oss_to_customer,single_invoice_fee_for_hq_to_oss:single_invoice_fee_for_hq_to_oss,multiple_invoice_fee_for_hq_to_oss:multiple_invoice_fee_for_hq_to_oss};           
      
      if(this.curData){
        expobject.id = this.curData.id;
      }      

      this.service.addData(expobject)
      .pipe(first())
      .subscribe(res => {
      
          if(res.status){
            this.service.customSearch();
            this.formReset();
            this.success = {summary:res.message};
            this.buttonDisable = false;
          }else if(res.status == 0){
            this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
          }
          this.loading['button'] = false;
          this.buttonDisable = false;
      },
      error => {
          this.error = {summary:error};
          this.loading['button'] = false;
      });
    
    }
  }


  editStatus=0;
  curData:any;
  editData(index:number,data) 
  {
    this.curData = data;
    this.editStatus = 1;
    
    this.form.patchValue({		
      standard_id:data.standard_id,
      single_domestic_invoice_fee_for_oss_to_customer:data.single_domestic_invoice_fee_for_oss_to_customer,
	  single_export_invoice_fee_for_oss_to_customer:data.single_export_invoice_fee_for_oss_to_customer,
	  multiple_domestic_invoice_fee_for_oss_to_customer:data.multiple_domestic_invoice_fee_for_oss_to_customer,
	  multiple_export_invoice_fee_for_oss_to_customer:data.multiple_export_invoice_fee_for_oss_to_customer,
      single_invoice_fee_for_hq_to_oss:data.single_invoice_fee_for_hq_to_oss,      
      multiple_invoice_fee_for_hq_to_oss:data.multiple_invoice_fee_for_hq_to_oss
    });
  
    this.scrollToBottom();	
  }

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  
  removeData(content,index:number,data) 
  {
      this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

      this.modalss.result.then((result) => {
        this.loading['button'] = true;
        this.buttonDisable = true;

          this.formReset();
          
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
              this.buttonDisable = false;
              this.loading['button'] = false;
          });
      }, (reason) => {
      })
  }

  formReset()
  {
    this.curData = '';   
    this.editStatus=0;
    this.form.reset();
  }

  getSelectedValue(val)
  {
    return this.standardList.find(x=> x.id==val).name; 
  }

  scrollToBottom()
  {
    window.scroll({ 
      top: window.innerHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }

}
