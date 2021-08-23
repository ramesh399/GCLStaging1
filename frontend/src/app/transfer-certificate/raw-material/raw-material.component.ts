import { Component, OnInit,EventEmitter,QueryList, ViewChildren, HostListener  } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';

import { RawMaterialListService } from '@app/services/transfer-certificate/raw-material/raw-material-list.service';
import { InspectionBodyListService } from '@app/services/transfer-certificate/inspection-body/inspection-body-list.service';
import {RawMaterial} from '@app/models/transfer-certificate/raw-material';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import {saveAs} from 'file-saver';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import { first, debounceTime, distinctUntilChanged, map } from 'rxjs/operators';
import {Observable,Subject} from 'rxjs';

@Component({
  selector: 'app-raw-material',
  templateUrl: './raw-material.component.html',
  styleUrls: ['./raw-material.component.scss'],
  providers: [RawMaterialListService]
})
export class RawMaterialComponent implements OnInit {

  title = '';
  RawMaterial$: Observable<RawMaterial[]>;
  total$: Observable<number>;
  //source_file_status$: Observable<number>;
  //view_file_status$: Observable<number>;

  auditplanStatus$: Observable<any>;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;


  form : FormGroup;
  logForm: FormGroup;
  
  buttonDisable = false;
  is_certified = false;
  certified_yes = false;
  certified_no = false;
  certified_reclaim = false;
  editStatus=0;
  error:any;
  id:number;
  std_id:number;
  typelist:any=[];
  statuslist:any=[];
  labelgradeList:any=[];
  certificationlist:any=[];
  
  model: any = {id:null,action:null,type:'',description:'',date:''};
  success:any;
  modalss:any;

  formData:FormData = new FormData();
  userType:number;
  userdetails:any;
  userdecoded:any;
  productErrors:any;
  productEntries:any=[];
  type:any;
  reviewerList:any;
  accessList:any;
  standardList:any; 
  filteroptionList:any;
  standard_idErrors:any = '';
  standardUpdate = new Subject<any>();
  tc_numberErrors:any='';
  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, public userService:UserService,public service: RawMaterialListService,private inspectionservice: InspectionBodyListService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { 
  
    this.RawMaterial$ = service.rawmaterial$;
    this.total$ = service.total$;		
    //this.source_file_status$ = service.source_file_status$;   
    //this.view_file_status$ = service.view_file_status$;   
	
	/*
    router.events
      .filter(e => e instanceof NavigationEnd)
      .forEach(e => {
        this.title = activatedRoute.root.firstChild.snapshot.data['usertype'];
    });
	*/
	
	window.scroll({ 
      top: 0, 
      left: 0, 
      behavior: 'smooth' 
    });
  }
  canAddData = false;
  canEditData = false;
  canDeleteData = false;
  canViewData = false;
  ngOnInit() {
  	
    this.standardUpdate.pipe(
      debounceTime(900),
      distinctUntilChanged())
    .subscribe(value => {
      this.loading['labelgrade'] = true;
      this.standard_idErrors = '';
      this.service.getStandardlabelgradeList(value).subscribe(res => {
        this.labelgradeList = res['standardlabelgrade'];
        this.loading['labelgrade'] = false;
      }); 
      this.service.checkStandardCobination({standard_id:value.id}).subscribe(res => {
        if(res.status == 0){        
          this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
        }
      }); 
      //this.getlabel(value);
  
    });



	  this.title = 'Raw Material';	
	  //this.title = this.activatedRoute.snapshot.data['pageType'];
			
    this.form = this.fb.group({
      supplier_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      trade_name:['',[Validators.required,Validators.maxLength(255)]],
	    product_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],	  
      lot_number:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],	
      is_certified:['',[Validators.required]],      
      net_weight:['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]],
      gross_weight:['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]],
      certified_weight:['',[Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]],
      raw_material_product_id:[''],
      // used_weight:['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]],
      // balance_weight:['',[Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]],
      tc_number:[],
      tc_attachment:[],
      form_sc_number:[],
      form_sc_attachment:[],
      form_tc_number:[],
      form_tc_attachment:[],
      trade_tc_number:[],
      trade_tc_attachment:[],
	    certification_body_id:[''],
      standard_id:[],
      label_grade_id:[],
      invoice_number:[],
      invoice_attachment:[],
      declaration_attachment:[]
    });	   
    
    this.service.getStandardList().subscribe(res => {
      this.standardList = res['standards'];
    });	

    this.service.getFilterOptions().subscribe(res => {
      this.filteroptionList = res['filteroptions'];
    });	

    this.inspectionservice.getData().pipe(first())
    .subscribe(res => {
      this.certificationlist  = res.certificationlist;
    },
    error => {
        this.error = error;
    });   

    this.authservice.currentUser.subscribe(x => 
    {
      if(x)
      {
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        
        if(this.userdetails.resource_access == 1){
          this.canEditData = true;
          this.canDeleteData = true;
        }else{
          
          if(this.userdetails.rules.includes('edit_raw_material')  ){
            this.canEditData = true;
          }
          if(this.userdetails.rules.includes('delete_raw_material') ){
            this.canDeleteData = true;
          }
          if(this.userType == 2){
            this.canAddData = true;
          }
          
          //this.canAddData = true;

        }

          
          
        
      }
      else
      {
        this.userdecoded=null;
      }
	  });
	  
    this.form.patchValue({
		is_certified:'',
		certification_body_id:''
	}); 

  }

  get f() { return this.form.controls; } 

  onSort({column, direction}: SortEvent) 
  {
    this.headers.forEach(header => {
      if (header.sortable !== column) {
        header.direction = '';
      }
    });

    this.service.sortColumn = column;
    this.service.sortDirection = direction;
  }

  certifiedFn(value)
  {
    if(value)
    {
		this.is_certified = true;
		if(value==3){
			this.f.tc_number.setValidators([]);	
			this.f.certification_body_id.setValidators([]);			
			this.f.standard_id.setValidators([]);
			// this.f.label_grade_id.setValidators([]);
			// this.f.certified_weight.setValidators([]);
			
			this.f.tc_number.updateValueAndValidity();	
			this.f.certification_body_id.updateValueAndValidity();
			this.f.standard_id.updateValueAndValidity();
			// this.f.label_grade_id.updateValueAndValidity();
			// this.f.certified_weight.updateValueAndValidity();
			
			this.f.tc_number.markAsUntouched();
			this.f.certification_body_id.markAsUntouched();
			this.f.standard_id.markAsUntouched();
			// this.f.label_grade_id.markAsUntouched();
			// this.f.certified_weight.markAsUntouched();
			
			this.f.invoice_number.setValidators([Validators.required]);
			this.f.invoice_number.updateValueAndValidity();	
			
			this.tc_attachmentFileErr='';
			 
			this.certified_reclaim=true;
			this.certified_no = false;
			this.certified_yes = false;	
			
    }
    else if(value==1)
		{
		  	this.f.invoice_number.setValidators([]);
			this.f.invoice_number.updateValueAndValidity();
			this.f.invoice_number.markAsUntouched();
						
			this.f.tc_number.setValidators([Validators.required]);
			/*	  
			this.f.form_sc_number.setValidators([Validators.required]);
			this.f.form_tc_number.setValidators([Validators.required]);
			this.f.trade_tc_number.setValidators([Validators.required]);
			*/
			this.f.certification_body_id.setValidators([Validators.required]);
			this.f.standard_id.setValidators([Validators.required]);
      this.f.label_grade_id.setValidators([Validators.required]);
      
      if(this.editStatus == 1){
        this.f.certified_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(0)]);
      }else{
        this.f.certified_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]);
      }
      
      
			this.f.tc_number.updateValueAndValidity();
			
			/*
			this.f.form_sc_number.updateValueAndValidity();
			this.f.form_tc_number.updateValueAndValidity();
			this.f.trade_tc_number.updateValueAndValidity();
			*/
			
			this.f.certification_body_id.updateValueAndValidity();
			this.f.standard_id.updateValueAndValidity();
			this.f.label_grade_id.updateValueAndValidity();
			this.f.certified_weight.updateValueAndValidity();

			/*
			this.f.form_sc_number.markAsTouched();
			this.f.form_tc_number.markAsTouched();
			this.f.trade_tc_number.markAsTouched();
			*/
			
			/*			
			this.f.invoice_number.markAsTouched();
			this.f.tc_number.markAsTouched();
			this.f.certification_body_id.markAsTouched();
			this.f.standard_id.markAsTouched();
			this.f.label_grade_id.markAsTouched();
			this.f.certified_weight.markAsTouched();
			*/
			
			this.certified_yes = true;
      this.certified_no = false;
      this.certified_reclaim=false;
		}
		else
		{
			this.f.tc_number.setValidators([]);	
			this.f.certification_body_id.setValidators([]);			
			this.f.standard_id.setValidators([]);
			this.f.label_grade_id.setValidators([]);
			this.f.certified_weight.setValidators([]);
			
			this.f.tc_number.updateValueAndValidity();	
			this.f.certification_body_id.updateValueAndValidity();
			this.f.standard_id.updateValueAndValidity();
			this.f.label_grade_id.updateValueAndValidity();
			this.f.certified_weight.updateValueAndValidity();
			
			this.f.tc_number.markAsUntouched();
			this.f.certification_body_id.markAsUntouched();
			this.f.standard_id.markAsUntouched();
			this.f.label_grade_id.markAsUntouched();
			this.f.certified_weight.markAsUntouched();
			
			this.f.invoice_number.setValidators([Validators.required]);
			this.f.invoice_number.updateValueAndValidity();	
			
			 this.tc_attachmentFileErr='';
		
      this.certified_no = true;
      this.certified_reclaim=false;
			this.certified_yes = false;
			
			//console.log('Test');
		}
    }
    else
    {
      this.is_certified = false;
    } 
  }

  getlabel(id,type)
  {
    this.form.patchValue({
      label_grade_id:''
    });

    if(type)
    {
      let productvals:any=[];
      this.productEntries.forEach((val)=>{
        productvals.push({raw_material_product_id:val.raw_material_product_id,trade_name:val.trade_name,product_name:val.product_name,lot_number:val.lot_number,label_grade_id:[],label_grade_name:[],certified_weight: val.certified_weight,gross_weight:val.gross_weight,net_weight:val.net_weight,actual_net_weight:val.net_weight,used_weight:val.used_weight,balance_weight:val.balance_weight})
      });
      this.productEntries=productvals;
    }
    
    if(id)
    {
      this.standardUpdate.next({id});
    }
  }

  invoice_attachment:any;
  invoice_attachmentFileErr = '';  
  invoiceChange(element) 
  {
    let files = element.target.files;
    this.invoice_attachmentFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.formData.append("invoice_attachment", files[0], files[0].name);
      this.invoice_attachment = files[0].name;
    }
    else
    {
      this.invoice_attachmentFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }
   
  removeinvoice_attachment()
  {
    this.invoice_attachment = '';
    this.formData.delete("invoice_attachment");
  }
  
  declaration_attachment:any;  
  declaration_attachmentFileErr = '';
  declarationChange(element) 
  {
    let files = element.target.files;
    this.declaration_attachmentFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.formData.append("declaration_attachment", files[0], files[0].name);
      this.declaration_attachment = files[0].name;
    }
    else
    {
      this.declaration_attachmentFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }
   
  removedeclaration_attachment()
  {
    this.declaration_attachment = '';
    this.formData.delete("declaration_attachment");
  }

  //TC Attachment
  tc_attachment:any;
  tc_attachmentFileErr = '';
  tcChange(element) 
  {
    let files = element.target.files;
    this.tc_attachmentFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.formData.append("tc_attachment", files[0], files[0].name);
      this.tc_attachment = files[0].name;
      
    }else{
      this.tc_attachmentFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }
  
  removetc_attachment()
  {
    this.tc_attachment = '';
    this.formData.delete("tc_attachment");
  }


   //Form SC Attachment
   form_sc_attachment:any;
   form_sc_attachmentFileErr = '';
   form_scChange(element) 
   {
     let files = element.target.files;
     this.form_sc_attachmentFileErr ='';
     let fileextension = files[0].name.split('.').pop();
     if(this.errorSummary.checkValidDocs(fileextension))
     {
       this.formData.append("form_sc_attachment", files[0], files[0].name);
       this.form_sc_attachment = files[0].name;
       
     }else{
       this.form_sc_attachmentFileErr ='Please upload valid file';
     }
     element.target.value = '';
   }
   
   removeform_sc_attachment()
   {
     this.form_sc_attachment = '';
     this.formData.delete("form_sc_attachment");
   }


   //Form TC Attachment
   form_tc_attachment:any;
   form_tc_attachmentFileErr:any = '';
   form_tcChange(element) 
   {
     let files = element.target.files;
     this.form_tc_attachmentFileErr ='';
     let fileextension = files[0].name.split('.').pop();
     if(this.errorSummary.checkValidDocs(fileextension))
     {
       this.formData.append("form_tc_attachment", files[0], files[0].name);
       this.form_tc_attachment = files[0].name;
       
     }else{
       this.form_tc_attachmentFileErr ='Please upload valid file';
     }
     element.target.value = '';
   }
   
   removeform_tc_attachment()
   {
     this.form_tc_attachment = '';
      this.formData.delete("form_tc_attachment");
   }


   //Trade TC Attachment
   trade_tc_attachment:any;
   trade_tc_attachmentFileErr = '';
   trade_tcChange(element) 
   {
     let files = element.target.files;
     this.trade_tc_attachmentFileErr ='';
     let fileextension = files[0].name.split('.').pop();
     if(this.errorSummary.checkValidDocs(fileextension))
     {
       this.formData.append("trade_tc_attachment", files[0], files[0].name);
       this.trade_tc_attachment = files[0].name;
     }
     else
     {
       this.trade_tc_attachmentFileErr ='Please upload valid file';
     }
     element.target.value = '';
   }
   
   removetrade_tc_attachment()
   {
     this.trade_tc_attachment = '';
     this.formData.delete("trade_tc_attachment");
   }
 
  
  getSelectedStandardValue(val)
  {
    return this.standardList.find(x=> x.id==val).name; 
  }


  getSelectedLabelgradeValue(val)
  {
    let labelgradesel = this.labelgradeList.find(x=> x.id==val);
    return labelgradesel?labelgradesel.name:''; 
  }
  
  downloadFile(fileid='',filetype='',filename='')
  {
    this.service.downloadFile({id:fileid,filetype:filetype})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }

  downloadRawmaterialFile(fileid='',filetype='',filename='')
  {
    this.service.downloadFile({id:fileid,filetype:filetype})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }
  
  
  gross_weightErr = '';
  net_weightErr = '';
  certified_weightErr = '';
  
  gisListEntries = [];
  gisIndex:number=null;
  loading:any=[];
  addData()
  {
	this.f.supplier_name.markAsTouched();
    this.f.trade_name.markAsTouched();
	this.f.product_name.markAsTouched();	
    this.f.lot_number.markAsTouched();
    this.f.is_certified.markAsTouched();   
    this.f.gross_weight.markAsTouched();
    this.f.net_weight.markAsTouched();

    this.tc_attachmentFileErr = '';
    this.form_sc_attachmentFileErr = '';
    this.form_tc_attachmentFileErr = '';
    this.trade_tc_attachmentFileErr = '';
    this.gross_weightErr = '';
    this.net_weightErr = '';
    this.certified_weightErr = '';
    
    this.invoice_attachmentFileErr = '';
    this.declaration_attachmentFileErr = '';
    let productErr:any = [];
    if(this.form.get('is_certified').value==3)
    {
      
      if(this.invoice_attachment =='' || this.invoice_attachment===undefined)
      {
        this.invoice_attachmentFileErr = 'Please upload Invoice Document';
      }
      
      if(this.declaration_attachment =='' || this.declaration_attachment===undefined)
      {
        this.declaration_attachmentFileErr = 'Please upload Declaration Document';
      }
      
      this.f.invoice_number.markAsTouched();
      /*
      let gross_weight = parseFloat(this.form.get('gross_weight').value);
      let net_weight = parseFloat(this.form.get('net_weight').value);			
          
      if(gross_weight>0 && net_weight>0 && net_weight>gross_weight)	
      {
        this.gross_weightErr = 'Gross Weight should be greater than or equal to Net Weight';
        this.net_weightErr = 'Net Weight should be less than or equal to Gross Weight';	
      }
      */
    }
    else if(this.form.get('is_certified').value==1)
    {		
      this.f.tc_number.markAsTouched();
      this.f.certification_body_id.markAsTouched();
      this.f.standard_id.markAsTouched();
      
      if(this.tc_attachment =='' || this.tc_attachment===undefined)
      {
        this.tc_attachmentFileErr = 'Please upload TC Attachment';
      }
      
      this.productEntries.forEach((val)=>{
        let label_grade_id = val.label_grade_id;
        let certified_weight = val.certified_weight;
        
        let errdata = [];
        if(!label_grade_id || label_grade_id.length<=0){
          errdata.push(`Please Select Standard Label Grade`);
        }
        if(this.editStatus == 1){
          if(certified_weight===undefined || certified_weight ==='' || certified_weight < 0){
            errdata.push(`Please enter the Certified Weight`);
          }
        }else{
          if(certified_weight===undefined || certified_weight ==='' || certified_weight <= 0){
            errdata.push(`Please enter the Certified Weight`);
          }
        }
        
        if(errdata.length>0){
          let errdatastr = errdata.join(', ');
          productErr.push(`<li>${val.trade_name} - ${val.product_name}: ${errdatastr}</li>`);
        }
      })
    }
    else
    {
      this.f.invoice_number.markAsTouched();
       
    }		
    
    this.removeValidationProductEntry();
    if(productErr.length>0){
      let errstr = `Please Fix Following Errors:<ul>`+productErr.join('')+`</ul>`;
      this.error = {summary:errstr};
      return false;
    }

    this.productErrors='';
    if(this.productEntries.length<=0){
      this.productErrors = {summary:"Please select the product"};
      return false;
    }  
    //return false;

	  if(this.productErrors =='' && productErr.length<=0 && this.form.valid && this.gross_weightErr == '' && this.certified_weightErr == '' && this.net_weightErr == '' && this.tc_attachmentFileErr =='' && this.form_sc_attachmentFileErr =='' && this.form_tc_attachmentFileErr =='' && this.trade_tc_attachmentFileErr =='' && this.standard_idErrors == '' && this.invoice_attachmentFileErr =='' && this.declaration_attachmentFileErr =='')
    {
      

      this.loading['button'] = true;
      this.buttonDisable = true;  
      
      
     
      let supplier_name = this.form.get('supplier_name').value;	  
      let lot_number = this.form.get('lot_number').value;
      let is_certified = this.form.get('is_certified').value;
      let certification_body_id = this.form.get('certification_body_id').value;

      // if(is_certified==1)
      // {
        let tc_number = this.form.get('tc_number').value;
        let form_sc_number = this.form.get('form_sc_number').value;
        let form_tc_number = this.form.get('form_tc_number').value;
        let trade_tc_number = this.form.get('trade_tc_number').value;
        let standard_id = this.form.get('standard_id').value;
      // }
      // else
      // {
        let invoice_number = this.form.get('invoice_number').value;
      // }

      

      let expobject:any={};

      expobject = {supplier_name:supplier_name,lot_number:lot_number,is_certified:is_certified,invoice_number:invoice_number,tc_number:tc_number,form_sc_number:form_sc_number,form_tc_number:form_tc_number,trade_tc_number:trade_tc_number,standard_id:standard_id,certification_body_id:certification_body_id};
       
      if(is_certified!=1 && is_certified!=3)
      {
       this.formData = new FormData(); 
      }

	    if(this.curData){
        expobject.id = this.curData.id;
        /*
		    expobject.tc_attachment = this.curData.tc_attachment;
        expobject.form_sc_attachment = this.curData.form_sc_attachment;
        expobject.form_tc_attachment = this.curData.form_tc_attachment;
        expobject.trade_tc_attachment = this.curData.trade_tc_attachment;
        expobject.invoice_attachment = this.curData.invoice_attachment;
        expobject.declaration_attachment = this.curData.declaration_attachment;
        */
        expobject.tc_attachment = this.tc_attachment;
        expobject.form_sc_attachment = this.form_sc_attachment;
        expobject.form_tc_attachment = this.form_tc_attachment;
        expobject.trade_tc_attachment = this.trade_tc_attachment;
        expobject.invoice_attachment = this.invoice_attachment;
        expobject.declaration_attachment = this.declaration_attachment;
      
        expobject.updated_at = this.curData.updated_at;
        
      }
      
      let productdatas = [];
      this.productEntries.forEach((val)=>{

        let label_grade_id:any = [];
        let certified_weight:any = 0;
        if(is_certified == 1){
          label_grade_id = val.label_grade_id;
          certified_weight = val.certified_weight;
        }
        productdatas.push({raw_material_product_id:val.raw_material_product_id,trade_name:val.trade_name,product_name:val.product_name,lot_number:val.lot_number,label_grade_id:label_grade_id,certified_weight:certified_weight,gross_weight:val.gross_weight,net_weight:val.net_weight,actual_net_weight:val.net_weight,used_weight:val.used_weight,balance_weight:val.balance_weight})
      });

      expobject.products = [];
      expobject.products = productdatas;
	    this.formData.append('formvalues',JSON.stringify(expobject));

	    this.service.addData(this.formData)
	    .pipe(first())
	    .subscribe(res => {
    			if(res.status){
            this.productEntries = [];
    				this.formData = new FormData(); 
    				this.service.customSearch();
					  this.success = {summary:res.message};
					
            setTimeout(() => {					
              this.success = {summary:''};
              this.formReset();
              this.buttonDisable = false;	          					
              this.is_certified = false;	
            }, this.errorSummary.redirectTime);								    			
    				
            this.labelgradeList = [];         
    	    }else if(res.status == 0){				
    				this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
    	    }else{			      
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
        
      
    }
  }

  curData:any;
  editData(index:number,id) 
  {
   
    this.formData = new FormData(); 
    this.editStatus = 1;

    this.tc_attachmentFileErr = '';
    this.form_sc_attachmentFileErr = '';
    this.form_tc_attachmentFileErr = '';
    this.trade_tc_attachmentFileErr = '';
    this.invoice_attachmentFileErr = '';
    this.declaration_attachmentFileErr = '';
    this.success = {summary:''};

    if(this.editStatus == 1){
      this.f.gross_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(0)]);
      this.f.net_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(0)]);
      this.f.certified_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(0)]);
    }else{
      this.f.gross_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]);
      this.f.net_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]);
      this.f.certified_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]);
    }
    
    this.f.gross_weight.updateValueAndValidity();
    this.f.net_weight.updateValueAndValidity();
    this.f.certified_weight.updateValueAndValidity();
    
    this.service.getDetails({id:id,type:'edit'})
    .subscribe(res => {
      this.downloadData = res.data;
      this.curData = this.downloadData;
      this.productEntries = this.downloadData.products;
      this.tc_attachment = this.downloadData.tc_attachment;
      this.form_sc_attachment = this.downloadData.form_sc_attachment;
      this.form_tc_attachment = this.downloadData.form_tc_attachment;
      this.trade_tc_attachment = this.downloadData.trade_tc_attachment;
      this.invoice_attachment = this.downloadData.invoice_attachment;
      this.declaration_attachment = this.downloadData.declaration_attachment;

      this.certifiedFn(this.downloadData.is_certified);
      this.getlabel(this.downloadData.standard_id,0);
      //trade_name:this.downloadData.trade_name,
      //product_name:this.downloadData.product_name,
	  //lot_number:this.downloadData.lot_number,
      // label_grade_id:this.downloadData.label_grade_id,
      //certified_weight:this.downloadData.certified_weight,
      //net_weight:this.downloadData.net_weight
      //gross_weight:this.downloadData.gross_weight,
      this.form.patchValue({
        supplier_name:this.downloadData.supplier_name,        
        is_certified:this.downloadData.is_certified,
        tc_number:this.downloadData.tc_number,
        tc_attachment:this.downloadData.tc_attachment,
        form_sc_number:this.downloadData.form_sc_number,
        form_sc_attachment : this.downloadData.form_sc_attachment,
        form_tc_number:this.downloadData.form_tc_number,
        form_tc_attachment : this.downloadData.form_tc_attachment,
        trade_tc_number:this.downloadData.trade_tc_number,
        trade_tc_attachment : this.downloadData.trade_tc_attachment,
        standard_id:this.downloadData.standard_id,
        invoice_attachment:this.downloadData.nvoice_attachment,
        declaration_attachment:this.downloadData.declaration_attachment,
        invoice_number:this.downloadData.invoice_number,
        certification_body_id:this.downloadData.certification_body_id?this.downloadData.certification_body_id:'',
        
      });
      this.certifiedFn(this.downloadData.is_certified);
    },
    error => {
        this.error = {summary:error};
    });
    this.resetproductform();
    this.scrollToBottom();	
  }
  

  formReset()
  {
    this.editStatus=0;
  	this.formData = new FormData(); 
    this.curData = '';
    this.tc_attachment = '';
    this.tc_attachmentFileErr = '';
    this.form_sc_attachment = '';
    this.form_sc_attachmentFileErr = '';
    this.form_tc_attachment = '';
    this.form_tc_attachmentFileErr = '';
    this.trade_tc_attachment = '';
    this.trade_tc_attachmentFileErr = '';
    this.declaration_attachment = '';
    this.declaration_attachmentFileErr = '';
    this.invoice_attachment = '';
    this.invoice_attachmentFileErr = '';
	  
	this.is_certified = false;	
	this.buttonDisable = false;
	  
    this.form.reset();
	
	this.certified_no = false;
	this.certified_yes = false;
	this.certified_reclaim=false;
	
	this.form.patchValue({
		is_certified:'',
		certification_body_id:''
	});
	
	this.resetproductform();
	
	this.productEntries = [];	
  }

  downloadData:any;
  showDetails(content,id)
  {
    this.downloadData = [];
    this.loading['data'] = true;
    this.service.getDetails({id:id,type:'view'})
    .subscribe(res => {
      this.downloadData = res.data;
      this.loading['data'] = false;
    },
    error => {
        this.error = {summary:error};
    });
    
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }
  
  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  rawmaterialdelid:number = 0;
  removeData(content,index:number,data) {
      this.rawmaterialdelid = data.id;
      this.popupsuccess = '';
      this.popuperror = '';

      this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

      this.modalss.result.then((result) => {

        this.popupsuccess = '';
        this.popuperror = '';
      }, (reason) => {
      })
      
    
  }

  popupsuccess:any = '';
  popuperror:any = '';
  popupinfomessage:any = '';
  removeDataAction(){
    this.formReset();
    this.popupinfomessage = 'Please wait. Your request is processing';
    this.service.deleteData({id:this.rawmaterialdelid})
    .pipe(first())
    .subscribe(res => {
        this.popupinfomessage = '';
        if(res.status){
          this.service.customSearch();
          this.popupsuccess = res.message;
          setTimeout(()=>this.modalss.close('deactivate'),this.errorSummary.redirectTime);
          this.buttonDisable = true;
        }else if(res.status == 0){
          this.popuperror = res.message;
          setTimeout(()=>this.modalss.close('deactivate'),this.errorSummary.errormessageTimeoutTime);
        }
        this.loading['button'] = false;
        this.buttonDisable = false;
    },
    error => {
        this.popupinfomessage = '';
        this.popuperror = error;
        this.loading['button'] = false;
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
  
  validateWeight()
  {
	this.gross_weightErr = '';
	this.net_weightErr = '';
	this.certified_weightErr = '';
	if(this.form.get('is_certified').value==1)
    {		
		let gross_weight = parseFloat(this.form.get('gross_weight').value);
		let certified_weight = parseFloat(this.form.get('certified_weight').value);
		let net_weight = parseFloat(this.form.get('net_weight').value);
		
		if(gross_weight>=0 && net_weight>=0 && net_weight>gross_weight)	
		{
			this.gross_weightErr = 'Gross Weight should be greater than or equal to Net Weight';
			this.net_weightErr = 'Net Weight should be less than or equal to Gross Weight';
		}		
		
		if(certified_weight>=0 && net_weight>=0 && certified_weight>net_weight)	
		{
			this.net_weightErr = 'Net Weight should be greater than or equal to Certified Weight';	
			this.certified_weightErr = 'Certified Weight should be less than or equal to Net Weight';
		}	
		
    }else{
		let gross_weight = parseFloat(this.form.get('gross_weight').value);
		let net_weight = parseFloat(this.form.get('net_weight').value);	
				
		if(gross_weight>=0 && net_weight>=0 && net_weight>gross_weight)	
		{
			this.gross_weightErr = 'Gross Weight should be greater than or equal to Net Weight';
			this.net_weightErr = 'Net Weight should be less than or equal to Gross Weight';	
		}	
				
	} 
  }

  productIndex=null;
  addProduct()
  {
    this.f.trade_name.setValidators([Validators.required,Validators.maxLength(255)]);
    this.f.product_name.setValidators([Validators.required,Validators.maxLength(255)]);
    this.f.lot_number.setValidators([Validators.required,Validators.maxLength(255)]);
    //let raw_material_product_id_chk = this.form.get('raw_material_product_id').value;
    // && raw_material_product_id_chk
    if(this.editStatus == 1){
      this.f.gross_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(0)]);
      this.f.net_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(0)]);
    }else{
      this.f.gross_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]);
      this.f.net_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]);
    }
    
    

    this.f.trade_name.updateValueAndValidity();
    this.f.product_name.updateValueAndValidity();
	  this.f.lot_number.updateValueAndValidity();	
    this.f.gross_weight.updateValueAndValidity();
    this.f.net_weight.updateValueAndValidity();

    if(this.form.get('is_certified').value==1)
    {
      this.f.label_grade_id.setValidators([Validators.required]);
      if(this.editStatus == 1){
        this.f.certified_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(0)]);
      }else{
        this.f.certified_weight.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(1)]);
      }
      
      
      this.f.label_grade_id.updateValueAndValidity();
      this.f.certified_weight.updateValueAndValidity();
    }

    this.touchProductform();

    let certified_weight = this.form.get('certified_weight').value;
    let gross_weight = this.form.get('gross_weight').value;
    let net_weight = this.form.get('net_weight').value;
    let raw_material_product_id = this.form.get('raw_material_product_id').value;
    
    let trade_name = this.form.get('trade_name').value;
    let product_name = this.form.get('product_name').value;
	  let lot_number = this.form.get('lot_number').value;	
    let label_grade_id = this.form.get('label_grade_id').value;
    certified_weight = certified_weight!=''?parseFloat(certified_weight):'';
    gross_weight = gross_weight!=''?parseFloat(gross_weight):'';
    net_weight = net_weight!=''?parseFloat(net_weight):'';
    this.net_weightErr = '';
    this.gross_weightErr = '';
    this.certified_weightErr = '';

    if(trade_name == '' || product_name == '' || lot_number === '' || gross_weight === '' || net_weight === '' || this.f.gross_weight.errors || this.f.net_weight.errors)
    {
      
      return false;
    }
    let weightgreaterthanzero = [];
    if(this.form.get('is_certified').value==1){
      
      if(net_weight === 0 || gross_weight === 0 || certified_weight === 0){
        if(net_weight===0){
          weightgreaterthanzero.push('Net Weight');
        }
        if(gross_weight===0){
          weightgreaterthanzero.push('Gross Weight');
        }
        if(certified_weight===0){
          weightgreaterthanzero.push('Certified Weight');
        }
        let withoutzerostr = weightgreaterthanzero.join(', ');
        if(net_weight > 0){
          
          this.net_weightErr = `Net Weight should be zero as ${withoutzerostr} was zero`;
        }
        if(gross_weight > 0){
          this.gross_weightErr = `Gross Weight should be zero as ${withoutzerostr} was zero`;
        }
        if(certified_weight > 0){
          this.certified_weightErr = `Net Weight should be zero as ${withoutzerostr} was zero`;
        }
      }      
    }else if(this.form.get('is_certified').value==2 || this.form.get('is_certified').value==3){
      if(net_weight === 0 || gross_weight === 0){
        

        if(net_weight === 0){
          weightgreaterthanzero.push('Net Weight');
        }
        if(gross_weight=== 0){
          weightgreaterthanzero.push('Gross Weight');
        }
        let withoutzerostr = weightgreaterthanzero.join(', ');
        
        if(net_weight > 0){
          this.net_weightErr = `Net Weight should be zero as ${withoutzerostr} was zero`;
        }
        if(gross_weight > 0){
          this.gross_weightErr = `Gross Weight should be zero as ${withoutzerostr} was zero`;
        }
      }
    }
    if(this.net_weightErr !='' || this.gross_weightErr !='' || this.certified_weightErr !=''){
      return false;
    }
     
    if(this.form.get('is_certified').value==3)
    {
      if(gross_weight>=0 && net_weight>=0 && net_weight>gross_weight)	
      {
        this.gross_weightErr = 'Gross Weight should be greater than or equal to Net Weight';
        this.net_weightErr = 'Net Weight should be less than or equal to Gross Weight';	
      }
    }
    else if(this.form.get('is_certified').value==1)
    {
      if(trade_name == '' || product_name == '' || lot_number === '' || label_grade_id == '' || certified_weight === '' || gross_weight === '' || net_weight === '' || this.f.gross_weight.errors || this.f.net_weight.errors || this.f.certified_weight.errors)
      {
        
         return false;
      }

      if(gross_weight>=0 && net_weight>=0 && net_weight>gross_weight)	
      {
        this.gross_weightErr = 'Gross Weight should be greater than or equal to Net Weight';
        this.net_weightErr = 'Net Weight should be less than or equal to Gross Weight';
      }		
        
      if(certified_weight>=0 && net_weight>=0 && certified_weight>net_weight)	
      {
        this.net_weightErr = 'Net Weight should be greater than or equal to Certified Weight';	
        this.certified_weightErr = 'Certified Weight should be less than or equal to Net Weight';	
      }
    }
    else
    {
      
      if(gross_weight>=0 && net_weight>=0 && net_weight>gross_weight)	
      {
        this.gross_weightErr = 'Gross Weight should be greater than or equal to Net Weight';
        this.net_weightErr = 'Net Weight should be less than or equal to Gross Weight';	
      }
    }
    if(net_weight < parseFloat(this.currentEditproduct.used_weight)){
      
      if(this.editStatus == 1 && net_weight===0){

      }else{
        this.net_weightErr = 'Net Weight should be greater than or equal to Used Weight';	
      }
    }
    if(this.net_weightErr !='' || this.gross_weightErr !='' || this.certified_weightErr !=''){
     
      return false;
    }
    let labelnames;
    if(this.form.get('is_certified').value==1)
    {
      let sellabel;
      let label_names:any=[];
      label_grade_id.forEach((val)=>{
        sellabel = this.labelgradeList.find(s => s.id ==  val);
        label_names.push(sellabel.name);
      });
      labelnames=label_names.join(',');
    }
    
	  this.productErrors='';
    if(certified_weight =='' || certified_weight===undefined){
      certified_weight = 0;
    }
    let expobject:any=[];
    expobject["trade_name"] = trade_name;
    expobject["product_name"] = product_name;
	expobject["lot_number"] = lot_number;	
    expobject["label_grade_id"] = label_grade_id;
    expobject["label_grade_name"] = labelnames;
    expobject["certified_weight"] = certified_weight.toFixed(2);
    expobject["gross_weight"] = gross_weight.toFixed(2);
    expobject["net_weight"] = net_weight.toFixed(2);
    expobject["actual_net_weight"] = net_weight.toFixed(2);
    expobject["raw_material_product_id"] = raw_material_product_id;
    
    if(this.productIndex!==null)
    {
      expobject["balance_weight"] = Number(this.currentEditproduct.balance_weight).toFixed(2);
      expobject["used_weight"] = Number(this.currentEditproduct.used_weight).toFixed(2);
      expobject["is_product_used"] = this.currentEditproduct.is_product_used;
      
      this.productEntries[this.productIndex] = expobject;
    }
    else
    {
      expobject["balance_weight"] = Number(net_weight).toFixed(2);
      expobject["used_weight"] = 0;
      expobject["is_product_used"] = 0;

      this.productEntries.push(expobject);
    }
    this.currentEditproduct = [];
    this.resetProductEntry();

  }


  producteditStatus=false;
  currentEditproduct:any=[];
  editProduct(index:number)
  {
    this.producteditStatus=true;  
    this.productIndex=index;

    let qual = Object.assign({}, this.productEntries[index]);
    
    this.currentEditproduct = Object.assign({}, qual);//this.productEntries[index];
    
    this.form.patchValue({
      trade_name: qual.trade_name,
      product_name: qual.product_name,
	  lot_number: qual.lot_number,	  
      label_grade_id: qual.label_grade_id, 
      net_weight:qual.actual_net_weight,
      gross_weight:qual.gross_weight,
      certified_weight:qual.certified_weight,
      raw_material_product_id: qual.raw_material_product_id
    });
  }
 

  removeProduct(index)
  {
    this.producteditStatus=false;  
    if(index != -1)
      this.productEntries.splice(index,1);
    this.productIndex=null;
  }
   
  resetProductEntry()
  {
    this.f.trade_name.setValidators([]);
    this.f.product_name.setValidators([]);
	this.f.lot_number.setValidators([]);
    this.f.label_grade_id.setValidators([]);
    this.f.net_weight.setValidators([]);
    this.f.gross_weight.setValidators([]);
    this.f.certified_weight.setValidators([]);

    this.f.trade_name.updateValueAndValidity();
    this.f.product_name.updateValueAndValidity();
	this.f.lot_number.updateValueAndValidity();
    this.f.label_grade_id.updateValueAndValidity();
    this.f.net_weight.updateValueAndValidity();
    this.f.gross_weight.updateValueAndValidity();
    this.f.certified_weight.updateValueAndValidity();

    this.resetproductform();
    
    this.productIndex=null;
    this.producteditStatus=false;	
  }

  removeValidationProductEntry()
  {
    this.f.trade_name.setValidators([]);
    this.f.product_name.setValidators([]);
	this.f.lot_number.setValidators([]);
    this.f.label_grade_id.setValidators([]);
    this.f.net_weight.setValidators([]);
    this.f.gross_weight.setValidators([]);
    this.f.certified_weight.setValidators([]);

    this.f.trade_name.updateValueAndValidity();
    this.f.product_name.updateValueAndValidity();
	this.f.lot_number.updateValueAndValidity();
    this.f.label_grade_id.updateValueAndValidity();
    this.f.net_weight.updateValueAndValidity();
    this.f.gross_weight.updateValueAndValidity();
    this.f.certified_weight.updateValueAndValidity();
  }

  touchProductform(){
    this.f.trade_name.markAsTouched();
    this.f.product_name.markAsTouched();
	this.f.lot_number.markAsTouched();
    this.f.label_grade_id.markAsTouched();
    this.f.certified_weight.markAsTouched();
    this.f.gross_weight.markAsTouched();
    this.f.net_weight.markAsTouched();
  }

  resetproductform()
  {
	this.producteditStatus=false;
    this.form.patchValue({
	  raw_material_product_id: '',	
      trade_name: '',
      product_name: '',
	  lot_number: '',
      label_grade_id:'',
      certified_weight:'',
      gross_weight:'',
      net_weight:'',
    });
    this.removeValidationProductEntry();
    this.error = '';
    this.productIndex=null;
    this.productErrors='';
    this.net_weightErr = '';		
    this.certified_weightErr = '';
    this.gross_weightErr = '';
    this.currentEditproduct = [];
  }

  changeBalanceWeight(val)
  {
    let net_weight_fld=this.form.get('net_weight').value!='' && this.form.get('net_weight').value != null ? this.form.get('net_weight').value.toString():''; 
	  if(this.currentEditproduct.used_weight>parseFloat(net_weight_fld))	
    {
      
      if(this.editStatus == 1 && net_weight_fld==0){

      }else{
        this.net_weightErr = 'Net Weight should be greater than or equal to Used Weight';	
        return false;
      }      
    }
    this.net_weightErr = '';
    let used_weight:any = this.currentEditproduct.used_weight;
	  this.currentEditproduct.balance_weight = Number(val - used_weight).toFixed(2);
  }
  
  downloadMaterialFile(filedata,ftype){
    const {  raw_material_file_type,raw_material_history_file_id,raw_material_file_old,raw_material_file_new } = filedata;
    let filename:string;
    if(ftype =='old'){
      filename = raw_material_file_old;
    }else{
      filename = raw_material_file_new;
    }
    this.service.downloadMaterialFile({material_file_type:raw_material_file_type, file_type:ftype,id:raw_material_history_file_id})
    .subscribe(res => {
      
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
      this.modalss.close('');
    });
  }
}

