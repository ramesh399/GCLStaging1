import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { AddStandardRoyaltyListService } from '@app/services/master/franchise/add-standard-royalty.service';
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
  selector: 'app-standard-royalty-fee',
  templateUrl: './standard-royalty-fee.component.html',
  styleUrls: ['./standard-royalty-fee.component.scss'],
  providers: [AddStandardRoyaltyListService]
})
export class StandardRoyaltyFeeComponent implements OnInit {

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
  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute,private userService:UserService,public service:AddStandardRoyaltyListService,public standardservice:StandardService,private fb:FormBuilder,public errorSummary: ErrorSummaryService) 
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
    this.userService.getStandardRights().subscribe(res => {
      this.standardRights = res;
    });

    this.form = this.fb.group({	
		standard_id:['',[Validators.required]],			
		scope_holder_fee:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]],
		facility_fee:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]],
		sub_contractor_fee:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]],
		non_certified_subcon_fee:['',[Validators.required,Validators.maxLength(10),Validators.pattern("^[0-9]+(.[0-9]{0,2})?$")]]
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
    this.f.scope_holder_fee.markAsTouched();
    this.f.facility_fee.markAsTouched();
    this.f.sub_contractor_fee.markAsTouched();
	this.f.non_certified_subcon_fee.markAsTouched();
 	        
    if(this.form.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true;     
      
      let standard_id = this.form.get('standard_id').value;  
      let scope_holder_fee = this.form.get('scope_holder_fee').value;  
      let facility_fee = this.form.get('facility_fee').value;  
      let sub_contractor_fee = this.form.get('sub_contractor_fee').value;
	  let non_certified_subcon_fee = this.form.get('non_certified_subcon_fee').value;      
      
      let expobject:any={};
      expobject = {franchise_id:this.franchise_id,standard_id:standard_id,scope_holder_fee:scope_holder_fee,facility_fee:facility_fee,sub_contractor_fee:sub_contractor_fee,non_certified_subcon_fee:non_certified_subcon_fee};
           
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
      scope_holder_fee:data.scope_holder_fee,
      facility_fee:data.facility_fee,
      sub_contractor_fee:data.sub_contractor_fee,
	  non_certified_subcon_fee:data.non_certified_subcon_fee,
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
              this.loading['button'] = false;
              this.buttonDisable = false;
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
