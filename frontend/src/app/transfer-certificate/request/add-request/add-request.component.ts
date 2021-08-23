import { Component, OnInit, ViewChild, ViewEncapsulation, Output, EventEmitter } from '@angular/core';
import {Directive, QueryList, ViewChildren} from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl, Form } from '@angular/forms';
import { AuthenticationService } from '@app/services/authentication.service';
import { BuyerListService } from '@app/services/transfer-certificate/buyer/buyer-list.service';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';

import { InspectionBodyListService } from '@app/services/transfer-certificate/inspection-body/inspection-body-list.service';
import { RequestListService } from '@app/services/transfer-certificate/request/request-list.service';
import { RawMaterialListService } from '@app/services/transfer-certificate/raw-material/raw-material-list.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Router } from '@angular/router';
import { Request } from '@app/models/transfer-certificate/request';
import {saveAs} from 'file-saver';
import { NgbdSortableHeader, SortEvent,PaginationList,commontxt } from '@app/helpers/sortable.directive';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { Country } from '@app/services/country';
import { CountryService } from '@app/services/country.service';
import { first, debounceTime, distinctUntilChanged, map } from 'rxjs/operators';
import {Observable,Subject} from 'rxjs';
import { BrandService } from '@app/services/master/brand/brand.service';

@Component({
  selector: 'app-add-request',
  templateUrl: './add-request.component.html',
  styleUrls: ['./add-request.component.scss'],
  encapsulation: ViewEncapsulation.None
})
export class AddRequestComponent implements OnInit {

  inputMaterialForm:any;
  //@ViewChild('inputMaterialForm') formValues;
  //@ViewChild('form', { read: NgForm }) form: any;
  @ViewChild('inputMaterialForm', {static: false}) ngForm: NgForm;
  title = 'TC Application';	
  form : FormGroup;
  productForm : FormGroup;
  inputForm : FormGroup;
  evidenceForm : FormGroup;  
  formData:FormData = new FormData();
  formEvidenceData:FormData = new FormData();
  loading:any={};
  buttonDisable = false;
  id:number;
  app_id:number;
  error:any;
  success:any;
  finalsuccess:any;
  finalerror:any;
  stocksuccess:any;
  stockerror:any;

  alertSuccessMessage:any;
  buyerlist:any=[];
  sellerlist:any=[];
  inspectionlist:any=[];
  certificationlist:any=[];
  ifoamstdlist:any=[];
  consigneelist:any=[];
  buyerconsigneelist:any=[];
  transportlist:any=[];
  standardlist:any=[];
  requestdata:any=[];
  resultdata:any=[];
  appdata:any=[];
  unitlist:any=[];


  descriptionErrors = '';
  userType:number;
  userdetails:any;
  userdecoded:any;
  
  tc_request_view = true;
  tc_request_edit = false;
  tc_product_view = false;
  tc_product_edit = false;
  sel_reduction:number;
  standardList:Standard[];
  brand_file = '';
  brandFileError ='';

  modalss:any;
  pwmodalss:any;
  productEntries:any=[];
  inputEntries:any=[];
  model: any = {id:null,action:null};
  viewproductData:any=[];
  viewinputmaterialdata:any=[];  
  inputmaterialweightlist=[];
  remainingWeightError:any;
  remainingWeightSuccess:any;
  commontxt = commontxt;
  evidenceFormStatus = false;
  enumstatus:any=[];
  requestStatus=false;
  maxDate = new Date();
  countryList:Country[];
  alertProductSuccessMessage:any;
  productWastageConfirmButtonDisable = false;
  showconsigneeaddress = false;

  standard_idErrors:any = '';
  standardUpdate = new Subject<any>();
  
  alertAdditionalWeightSuccessMessage:any;
  additionalWeightConfirmButtonDisable = false;
  alertWeightErrorMessage:any = '';
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;
  brandlist: any;
  loadingFile: boolean;
  constructor(public brandService: BrandService,public service: RawMaterialListService, private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,private buyerservice: BuyerListService,private inspectionservice: InspectionBodyListService,private requestservice: RequestListService,private authservice:AuthenticationService,private countryservice: CountryService,private standardservice: StandardService,public errorSummary: ErrorSummaryService) { }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;
		this.app_id = this.activatedRoute.snapshot.queryParams.app_id;
 
    this.brandService.getBrand({ id: this.app_id, type: 'consent' }).subscribe(res => {
      this.brandlist = res.data;
    });

    this.standardUpdate.pipe(
      debounceTime(900),
      distinctUntilChanged())
    .subscribe(value => {
      
      this.standard_idErrors = '';
      this.requestservice.checkStandardCobination({standard_id:value.id}).subscribe(res => {
        if(res.status == 0){        
          this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
        }
      }); 
      //this.getlabel(value);
  
    });


    this.id = this.activatedRoute.snapshot.queryParams.id;
  
    this.form = this.fb.group({
      id:[''],
      app_id:['',[Validators.required]],
      unit_id:['',[Validators.required]],
      buyer_id:['',[Validators.required]],
      //consignee_id:['',[Validators.required]],
      //purchase_order_number:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      standard_id:['',[Validators.required]],
      //transport_id:['',[Validators.required]],      
      //tc_number_temp:['',[Validators.required]],
      //tc_number_cds:['',[Validators.required]],
      //shipment_number:['',[Validators.required]],
      //seller_id:['',[Validators.required]],
      //certification_body_id:['',[Validators.required]],
      //inspection_body_id:['',[Validators.required]],
      //country_of_dispach:['',[Validators.required]],
      //country_of_destination:['',[Validators.required]],
      //bl_copy:[''],
      comments:['',[this.errorSummary.noWhitespaceValidator,Validators.maxLength(185)]],
      //visible_to_brand:['',[Validators.required]],
      usda_nop_compliant:['',[Validators.required]],
      ifoam_standard:[''],
      sel_reduction:['',[Validators.required]],
      brand_id : ['',[Validators.required]],
      qualification_exam:[''],
      authorized_name:['',[Validators.required]],
      brand_consent_date:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]]
    });
	
    this.productForm = this.fb.group({
        id:[''],
        product_id:['',[Validators.required]],
        trade_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]],
        packed_in:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]],
        lot_ref_number:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        gross_weight:['',[Validators.required, Validators.maxLength(10),Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(0.1)]],
        net_weight:['',[Validators.required, Validators.maxLength(10),Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(0.1)]],
        certified_weight:['',[Validators.required, Validators.maxLength(10),Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.min(0.1)]], 		
        unit_information:['',[Validators.maxLength(255),this.errorSummary.noWhitespaceValidator]],
        purchase_order_no:['',[Validators.required, Validators.maxLength(255),this.errorSummary.noWhitespaceValidator]],
		purchase_order_date:['',[Validators.required, Validators.maxLength(255)]],
        invoice_no:['',[Validators.required, Validators.maxLength(255),this.errorSummary.noWhitespaceValidator]],
        invoice_date:['',[Validators.required, Validators.maxLength(255)]],
        transport_document_no:['',[Validators.required, Validators.maxLength(255),this.errorSummary.noWhitespaceValidator]],
        transport_document_date:['',[Validators.required, Validators.maxLength(255)]],
		transport_company_name:['',[Validators.maxLength(255)]],
		vehicle_container_no:['',[Validators.maxLength(255)]],
        transport_id:['',[Validators.required]],
        consignee_id:['',[Validators.required]]
    });
	
    this.inputForm = this.fb.group({
        id:[''],      
        wastage_percentage:['',[Validators.required,Validators.min(0),Validators.max(99),Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$')]],      
    });
	
	  this.evidenceForm = this.fb.group({
        id:[''],             
        sales_invoice_with_packing_list:[''],
        transport_document:[''],
        mass_balance_sheet:[''],
        test_report:['']
    });
	


    this.loading.company = true;
    this.requestservice.getAppData().pipe(first())
    .subscribe(res => {
      if(res.status)
      {
		    this.appdata = res.appdata;        
      }
      else if(res.status == 0)
      {
        this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
      }
      else
      {			      
        this.error = {summary:res};
      }
      this.loading.company = false;
     
    },
    error => {
        this.error = error;
        this.loading.company = false;
    });

    if(this.id)
    {
		  this.getRequestData(1);
    }else{
      //if(this.userType==2){
      this.getBuyerData();
      //}
    
		  this.requestStatus=true;
	  }

    

    //this.getStandardwisematerial();

    this.inspectionservice.getData().pipe(first())
    .subscribe(res => {
      this.inspectionlist  = res.inspectionlist;
      this.certificationlist  = res.certificationlist;
    },
    error => {
        this.error = error;
    });    
	
	this.countryservice.getCountry().subscribe(res => {	
      this.countryList = res['countries'];
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
  // removequa_examFiles(){}
  // qua_examfileErrors='';
  // qua_exam_file='';

  // qua_examfileChange(element){}

  standardFormDetails:any=[];

  // removebrandFile(){
  //   this.brand_file = '';
  //   this.formData.delete('brand_file');
  // }

  // brandfileChange(element) {
  //   let files = element.target.files;
  //   this.brandFileError ='';
  //   let fileextension = files[0].name.split('.').pop();
  //   if(this.errorSummary.checkValidDocs(fileextension))
  //   {

  //     this.formData.append("brand_file", files[0], files[0].name);
  //     this.brand_file = files[0].name;
      
  //   }else{
  //     this.brandFileError ='Please upload valid file';
  //   }
  //   element.target.value = '';
   
  // }


  getBuyerData(){

    let app_id = this.f.app_id.value;
    this.buyerlist = [];
    this.sellerlist = [];
    this.consigneelist = [];
    this.buyerconsigneelist = [];
    this.transportlist = [];
    this.loading.buyer = true;
    this.buyerservice.getData({app_id:app_id}).pipe(first())
    .subscribe(res => {
      this.buyerlist  = res.buyerlist;
      this.sellerlist  = res.sellerlist;
      this.consigneelist  = res.consigneelist;
      this.buyerconsigneelist = res.buyerconsigneelist;
      this.transportlist = res.transportlist;
      this.loading.buyer = false;
      this.ifoamstdlist  = res.ifoamstdlist;
    },
    error => {
        this.error = error;
        this.loading.buyer = false;
    });
  }
  
  DownloadConsentFile(val,filename)
  {
    this.loadingFile  = true;
    this.brandService.downloadFile(val)
     .pipe(first())
     .subscribe(res => {
      this.loadingFile = false;
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
      this.error = error;
      this.loadingFile = false;
      this.modalss.close();
    });
  }

  setEvidenceFile(){
    if(this.resultdata.requestevidence){
      this.sales_invoice_with_packing_list_db=true;
      this.transport_document_db=true;
      this.mass_balance_sheet_db=true;
      this.test_report_db=true;
      this.formEvidenceData = new FormData();

      this.sales_invoice_with_packing_list = [];
      this.transport_document = [];
      this.mass_balance_sheet = [];
      this.test_report = [];

      if( this.resultdata.requestevidence){
        let requestevidence = this.resultdata.requestevidence;
        if(requestevidence.sales_invoice_with_packing_list && requestevidence.sales_invoice_with_packing_list.length>0){
          requestevidence.sales_invoice_with_packing_list.forEach(val=>{
            this.sales_invoice_with_packing_list.push({deleted:0,added:0,name:val.name,id:val.id});      
          });     
        }
        if(requestevidence.transport_document && requestevidence.transport_document.length>0){
          requestevidence.transport_document.forEach(val=>{
            this.transport_document.push({deleted:0,added:0,name:val.name,id:val.id});      
          });     
        }
        if(requestevidence.mass_balance_sheet && requestevidence.mass_balance_sheet.length>0){
          requestevidence.mass_balance_sheet.forEach(val=>{
            this.mass_balance_sheet.push({deleted:0,added:0,name:val.name,id:val.id});      
          });     
        }
        if(requestevidence.test_report && requestevidence.test_report.length>0){
          requestevidence.test_report.forEach(val=>{
            this.test_report.push({deleted:0,added:0,name:val.name,id:val.id});      
          });     
        }
        /*
        this.transport_document = this.resultdata.requestevidence.transport_document;
        this.mass_balance_sheet = this.resultdata.requestevidence.mass_balance_sheet;
        this.test_report = this.resultdata.requestevidence.test_report; 
        */
      }
      
    } 
  }
  
  getRequestData(init=0)
  {
  	this.requestservice.getData(this.id).pipe(first())
  	.subscribe(res => {
  		let result = res.data;
  		this.resultdata=res.data;
  		this.enumstatus = res.data.enumstatus;
  		this.oncompanychange(result.requestdata.app_id);
      this.onunitchange(result.requestdata.unit_id);
      this.loadAddress(result.requestdata.buyer_id,'buyer');
  		this.form.patchValue(result.requestdata);
        this.form.patchValue({
          brand_consent_date:result.requestdata.brand_consent_date?this.errorSummary.editDateFormat(result.requestdata.brand_consent_date):''
        });
        if(result.requestdata.sel_reduction==2){
          this.form.patchValue({
          brand_id:'',
          authorized_name:'',
          brand_consent_date:''
        });
      }

     // this.qua_exam_file = result.requestdata.qua_exam_file;
  		this.getBuyerData();
		this.requestStatus=true;
		
		//this.bl_copy = this.resultdata.requestdata.bl_copy; 

		if(init){
			this.setEvidenceFile();
		}
		  this.evidenceFormStatus=false;
  		if(this.resultdata.requestproduct && this.resultdata.requestproduct.length>0)
  		{
  			let openpdt = this.resultdata.requestproduct.find(x=> x.product_status==0);
  			let closepdt = this.resultdata.requestproduct.find(x=> x.product_status==1);				
  			if(openpdt === undefined && closepdt!==undefined)
  			{
  				this.evidenceFormStatus=true;
  			}	
  		}		
  	},
  	error => {
  	  this.error = error;
  	  
  	});  
  }
	
  /*	
  bl_copy:any;
  bl_copyFileErr ='';
  bl_copyChange(element) 
  {
    let files = element.target.files;
    this.bl_copyFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("bl_copy", files[0], files[0].name);
      this.bl_copy = files[0].name;
      
    }else{
      this.bl_copyFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }

  removebl_copy()
  {
    this.bl_copy = '';
	this.formData.delete("bl_copy");
  }
  */
  
  
  sales_invoice_with_packing_list:any=[];
  transport_document:any=[];
  mass_balance_sheet:any=[];
  test_report:any=[];

  newsales_invoice_with_packing_list:any=[];
  newtransport_document:any;
  newmass_balance_sheet:any;
  newtest_report:any;
  
  sales_invoice_with_packing_list_db:any=true;
  transport_document_db:any=true;
  mass_balance_sheet_db:any=true;
  test_report_db:any=true;

  sales_invoice_with_packing_listFileErr ='';
  transport_documentFileErr ='';
  mass_balance_sheetFileErr ='';
  test_reportFileErr ='';  


  evidenceDocument(element,fld) 
  {
    let files = element.target.files;
  	if(fld=='sales_invoice_with_packing_list'){
  		this.sales_invoice_with_packing_listFileErr ='';
  	}else if(fld=='transport_document'){
  		this.transport_documentFileErr ='';
  	}else if(fld=='test_report'){
  		this.test_reportFileErr ='';
  	}  
    /*
    else if(fld=='mass_balance_sheet'){	
  		this.mass_balance_sheetFileErr ='';
  	}
    */
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      if(fld=='sales_invoice_with_packing_list'){
        let sales_invoice_with_packing_listlength = this.sales_invoice_with_packing_list.length;
        this.formEvidenceData.append('sales_invoice_with_packing_list['+sales_invoice_with_packing_listlength+']', files[0], files[0].name);
      }else if(fld=='transport_document'){
        let transport_documentlength = this.transport_document.length;
        this.formEvidenceData.append('transport_document['+transport_documentlength+']', files[0], files[0].name);
      }else if(fld=='mass_balance_sheet'){  
        let mass_balance_sheetlength = this.mass_balance_sheet.length;
        this.formEvidenceData.append('mass_balance_sheet['+mass_balance_sheetlength+']', files[0], files[0].name);
      }else if(fld=='test_report'){
        let test_reportlength = this.test_report.length;
        this.formEvidenceData.append('test_report['+test_reportlength+']', files[0], files[0].name);
      } 

  		//this.formEvidenceData.append(fld[], files[0], files[0].name);
  	  this.formEvidenceData.get('fld');
  		if(fld=='sales_invoice_with_packing_list'){

  			this.sales_invoice_with_packing_list.push({deleted:0,added:1,name:files[0].name});
        //this.sales_invoice_with_packing_list_db = false;
  		}else if(fld=='transport_document'){
        this.transport_document.push({deleted:0,added:1,name:files[0].name});
  			 
  		}else if(fld=='mass_balance_sheet'){	
        this.mass_balance_sheet.push({deleted:0,added:1,name:files[0].name});
  			 
  		}else if(fld=='test_report'){
        this.test_report.push({deleted:0,added:1,name:files[0].name});
  			
  		}       
      
    }else{
  		if(fld=='sales_invoice_with_packing_list'){
  			this.sales_invoice_with_packing_listFileErr ='Please upload valid file';
  		}else if(fld=='transport_document'){
  			this.transport_documentFileErr ='Please upload valid file';
  		}else if(fld=='mass_balance_sheet'){	
  			this.mass_balance_sheetFileErr ='Please upload valid file';
  		}else if(fld=='test_report'){
  			this.test_reportFileErr ='Please upload valid file';
  		}       
    }
    element.target.value = '';
  }

  removeEvidenceDocument(fld,filedata,index)
  {
  	if(fld=='sales_invoice_with_packing_list'){
  		if(filedata.added){
        this.formEvidenceData.delete("sales_invoice_with_packing_list["+index+"]"); 
      }
      //this.sales_invoice_with_packing_list.splice(index,1);
      this.sales_invoice_with_packing_list[index].deleted =1;
  	}else if(fld=='transport_document'){
  		//this.transport_document ='';
      if(filedata.added){
        this.formEvidenceData.delete("transport_document["+index+"]"); 
      }
      //this.transport_document.splice(index,1);
      this.transport_document[index].deleted =1;
  	}else if(fld=='mass_balance_sheet'){	
  		//this.mass_balance_sheet ='';
      if(filedata.added){
        this.formEvidenceData.delete("mass_balance_sheet["+index+"]"); 
      }
      //this.mass_balance_sheet.splice(index,1);
      this.mass_balance_sheet[index].deleted =1;
  	}else if(fld=='test_report'){
  		//this.test_report ='';
      if(filedata.added){
        this.formEvidenceData.delete("test_report["+index+"]"); 
      }
      //this.test_report.splice(index,1);
      this.test_report[index].deleted =1;
  	}	
  	//this.formEvidenceData.delete(fld);	
  }
  
  onEvidenceSubmit(savetype){

    let validationStatus=true;
	  let sales_invoice_with_packing_list = this.sales_invoice_with_packing_list.filter(x=>x.deleted != 1);
    let transport_document = this.transport_document.filter(x=>x.deleted != 1);
    let mass_balance_sheet = this.mass_balance_sheet.filter(x=>x.deleted != 1);
    //let sales_invoice_with_packing_list = this.sales_invoice_with_packing_list.filter(x=>x.deleted != 1);

    this.sales_invoice_with_packing_listFileErr = '';
    if(sales_invoice_with_packing_list===undefined || sales_invoice_with_packing_list.length<=0){
		this.sales_invoice_with_packing_listFileErr = 'Please upload file';
		validationStatus=false;
    }
	
	this.transport_documentFileErr = '';
    if(transport_document===undefined || transport_document.length<=0){
		this.transport_documentFileErr = 'Please upload file';
		validationStatus=false;
    }
	
  this.mass_balance_sheetFileErr = '';
  /*
    if(mass_balance_sheet===undefined || mass_balance_sheet.length<=0 ){
		this.mass_balance_sheetFileErr = 'Please upload file';
		validationStatus=false;
    }
	*/
	this.test_reportFileErr = '';
	/*
    if(this.test_report=='' || this.test_report===undefined){
		this.test_reportFileErr = 'Please upload file';
		validationStatus=false;
    }
	*/
    
   if (this.evidenceForm.valid && validationStatus) 
	{
      
      this.loading.button = true;
	  
	  let expobject:any={};      
	  
	  expobject.id = this.id;
	  expobject.sales_invoice_with_packing_list = this.sales_invoice_with_packing_list;
      expobject.transport_document = this.transport_document;
      expobject.mass_balance_sheet = this.mass_balance_sheet;
      expobject.test_report = this.test_report;	
      expobject.savetype = savetype;
	  this.formEvidenceData.append('formvalues',JSON.stringify(expobject));
      
      //this.formEvidenceData.append('formvalues',JSON.stringify(this.evidenceForm.value)); 
	  
      this.requestservice.addEvidenceData(this.formEvidenceData)
      .pipe(
        first()        
      )
      .subscribe(res => {

			if(res.status)
			{
        this.sales_invoice_with_packing_list_db=true;
        this.transport_document_db=true;
        this.mass_balance_sheet_db=true;
        this.test_report_db=true;
				//this.id = res.id;
				if(this.id && (savetype =='draft' ))
				{
					this.getRequestData(1);
				}
					
			    this.finalsuccess = {summary:res.message};			      
			    setTimeout(() => {
					this.buttonDisable = false;	
					this.loading.button = false;
					this.tc_request_view=true;
					this.tc_request_edit=false;
          if(savetype =='approval' || savetype =='other'){
            this.router.navigateByUrl('/transaction-certificate/request/view?id='+this.id);
          }
					//this.router.navigateByUrl('/transaction-certificate/request/list');
				}, this.errorSummary.redirectTime);
			}
			else if(res.status == 0)
			{
			    this.finalerror = {summary:res.message};
				  this.loading.button = false;
				  this.buttonDisable = false;
			}
			else
			{			      
				this.finalerror = {summary:res};
				this.loading.button = false;
				this.buttonDisable = false;
			}         
      },
      error => {
          this.finalerror = {summary:error};
          this.loading.button = false;
		    this.buttonDisable = false;
      });      
    } else {
      this.finalerror = {summary:this.errorSummary.errorSummaryText};
      
    }
  }

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  downloadFile(fileid='',filetype='',filename='')
  {
    this.requestservice.downloadBLFile({id:fileid,filetype})
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

  downloadEvidenceFile(fileid='',filetype='',filename='')
  {
    
    this.requestservice.downloadEvidenceFile({id:fileid,filetype})
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

  getlabel(id)
  {
    if(id)
    {
      this.standardUpdate.next({id});
    }
  }
  
  validateStatus=false;
  validateIfoamStandard(selectedstd)
  {
	  this.validateStatus=false;
	  this.f.ifoam_standard.setValidators([]);
	  let stdselectedlen=selectedstd.length;
	  if(stdselectedlen>0)
	  {
		  for (let i = 0; i < stdselectedlen; i++)
		  {
			 let val = selectedstd[i];			 
			 if(this.standardlist !==undefined && val!='' && val!==undefined)
			 {
				let stdobj = this.standardlist.find(x=> x.id==val);
				if(stdobj.code=='GOTS' || stdobj.code=='OCS')
				{
					this.validateStatus=true;					
				}
			 }
		  }	
		  
		  if(this.validateStatus)
		  {
			this.f.ifoam_standard.setValidators([Validators.required]);
		  }
	  }	  	  
	  this.f.ifoam_standard.updateValueAndValidity();
  }

  getSelectedValue(type, val) {

    if (type = 'brand_id') {
      return this.brandlist.find(x => x.id == val).brand_name;
    }
  }
  getSelectedValue1(val)
  {
    if(this.standardlist !==undefined && val!='' && val!==undefined){
      let stdobj = this.standardlist.find(x=> x.id==val);
      if(stdobj !==undefined){
        return stdobj.name;
      }
      return '';
    }else{
      return '';
    }
    
  }  

  getifoamSelectedValue(val)
  {
    let ifoam = this.ifoamstdlist.find(x=> x.id==val);
      if(ifoam !==undefined){
        return ifoam.name;
      }
  }

  view(content)
  {
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  rawmaterialstandardids:any;
  rawmaterialstandardname:any;
  rawmaterialstandardcontent:any;
  rawmaterialwithoutstandardcontent:any;
  rawmaterialreclaimcontent:any;

  rawmaterialids:any;
  rawmaterialProductIds:any;
  rawmaterialstandardcontentstatus=false;
  rawmaterialwithoutstandardcontentstatus=false;
  rawmaterialreclaimcontentstatus=false;
  
  inputmaterialweightlistExists:any={};
  materialloadingstatus:any = false;
  getStandardwisematerial(productid:any=0,loadAllInput=1)
  {
    this.rawmaterialids=[]; 
    this.rawmaterialProductIds = [];
    this.rawmaterialstandardcontentstatus=false;
    this.rawmaterialwithoutstandardcontentstatus=false;
    this.rawmaterialreclaimcontentstatus=false;
    this.materialloadingstatus=true;
    this.requestservice.getStandardwisematerial({request_product_id:productid}).pipe(first())
    .subscribe(res => {
      this.rawmaterialstandardids  = res.rawmaterialstandardids;
      this.rawmaterialstandardname  = res.rawmaterialstandardname;
      this.rawmaterialstandardcontent = res.rawmaterialstandardcontent;
      this.rawmaterialwithoutstandardcontent = res.rawmaterialwithoutstandardcontent;
      this.rawmaterialreclaimcontent = res.rawmaterialreclaimcontent;
      this.rawmaterialids  = res.rawmaterialids;
      this.rawmaterialProductIds = res.rawmaterialProductIds;
      this.rawMaterialKeyList  = res.rawMaterialKeyList;
      this.materialloadingstatus=false;
      if(Object.keys(this.rawmaterialstandardcontent).length>0)
      {
        this.rawmaterialstandardcontentstatus=true;
      }
		
      if(Object.keys(this.rawmaterialwithoutstandardcontent).length>0)
      {
        this.rawmaterialwithoutstandardcontentstatus=true;
      }

      if(Object.keys(this.rawmaterialreclaimcontent).length>0)
      {
        this.rawmaterialreclaimcontentstatus=true;
      }
      
      if(loadAllInput){
        if(this.rawmaterialProductIds && this.rawmaterialProductIds.length>0)
        {
          this.rawmaterialProductIds.forEach(val=>{
            this.inputmaterialweightlist['input_weight'+val] = '';			
            this.inputmaterialweightlistExists['input_weight'+val] =0;
          });			
        }
      
        if(this.viewinputmaterialdata.rawmaterialusedlist && this.viewinputmaterialdata.rawmaterialusedlist.length>0)
        {
          let stockusedTotal:any;
          stockusedTotal=0;
          
          this.viewinputmaterialdata.rawmaterialusedlist.forEach(val=>{
            this.inputmaterialweightlist['input_weight'+val.tc_raw_material_product_id]= val.used_weight;	
            this.inputmaterialweightlistExists['input_weight'+val.tc_raw_material_product_id] =val.used_weight;
            stockusedTotal=stockusedTotal+parseFloat(val.used_weight);
          });
          
          //let totW = parseFloat(this.remainingcertifiedweight)-stockusedTotal;
          let totW = stockusedTotal;
          this.remainingcertifiedweight = totW.toFixed(2);
          
          let totalnetweight = this.viewinputmaterialdata.total_net_weight;
          
          if(this.remainingcertifiedweight<parseFloat(totalnetweight))
          {
            this.remainingWeightError = 'Input Weight should be greater than or equal to Raw Material Required (Net Weight + Wastage Weight + Additional Weight)';
            this.inputloading['inputdatabutton'] = true;
          }
        }	
      }
		
    },
    error => {
        this.error = error;
    });
  }

  oncompanychange(value)
  {
    this.form.patchValue({
      unit_id:'',
      standard_id:'',
      sel_reduction:'2',
    });
    this.unitlist = [];
    this.company_address = [];
    this.buyer_address = [];
    this.unit_address = [];
    if(this.userType==1 || this.userType==3){
      this.buyerlist = [];
      this.sellerlist = [];
      this.consigneelist = [];
      this.buyerconsigneelist = [];
      this.transportlist = [];
    }
    if(value)
    {

      this.loadAddress(value,'company');

      if(this.userType==1 || this.userType==3){
        this.getBuyerData();
      }
      

      this.loading.unit = true;
      this.requestservice.getUnitData({id:value}).pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.unitlist = res.unitdata;
        }
        else if(res.status == 0)
        {
          this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
        }
        else
        {			      
          this.error = {summary:res};
        }
        this.loading.unit = false;
      },
      error => {
          this.error = error;
          this.loading.unit = false;
      });

      this.brandService.getBrand({ id: value, type: 'consent' }).subscribe(res => {
        this.brandlist = res.data;
        if(this.brandlist.length==0){
          this.brandService.getData().subscribe(res=>{
            this.brandlist=res.data;
          })
        }
      });
    }
  }

  company_address:any=[];
  unit_address:any=[];
  buyer_address:any=[];
  consignee_address:any = [];
  inspection_address:any=[];
  loadAddress(value,type)
  {
    if(type=='company')
    {
      this.loading.company = true;
      this.requestservice.loadCompanyAddress({id:value}).pipe(first())
      .subscribe(res => {
          this.loading.company = false;
          if(res){
            this.company_address = res.data;
          }
          
      });
    }
    else if(type=='unit')
    {
      this.loading.unit = true;
      this.requestservice.loadUnitAddress({id:value}).pipe(first())
      .subscribe(res => {
          this.loading.unit = false;
          this.unit_address = res.data;
      });
    }
    else if(type=='buyer')
    {
      if(value)
      {
        this.loading.buyer = true;
        this.requestservice.loadBuyerAddress({id:value}).pipe(first())
        .subscribe(res => {
            this.loading.buyer = false;
            this.buyer_address = res.data;
        });
      }
    }
    else if(type=='consignee')
    {
      if(value)
      {
        this.loading.buyer = true;
        this.showconsigneeaddress = true;
        this.requestservice.loadBuyerAddress({id:value}).pipe(first())
        .subscribe(res => {
            this.loading.buyer = false;
            this.consignee_address = res.data;
        });
      }
    }
    else if(type=='inspection')
    {
      if(value)
      {
        this.inspection_address = [];
        this.loading.inspection = true;
        this.requestservice.loadInspectionAddress({id:value}).pipe(first())
        .subscribe(res => {
            this.loading.inspection = false;
            this.inspection_address = res.data;
        });
      }
    }

  }

  onunitchange(value)
  {
    this.form.patchValue({
      standard_id:'',
    });
    this.standardlist = [];
    if(value)
    {
      this.loadAddress(value,'unit');

      this.requestservice.getStandardData({id:value}).pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.standardlist = res.stddata;
        }
        else if(res.status == 0)
        {
          this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
        }
        else
        {			      
          this.error = {summary:res};
        }
    
      },
      error => {
          this.error = error;
           
      });
    }
  }

  

  get f() { return this.form.controls; } 
  get pf() { return this.productForm.controls; } 
  get inputf() { return this.inputForm.controls; } 
  get ef() { return this.evidenceForm.controls; } 

  
  onSubmit(){

    let validationStatus=true;
    let formerror =false;
	/*
    this.bl_copyFileErr = '';
    if(this.bl_copy=='' || this.bl_copy===undefined){
		this.bl_copyFileErr = 'Please upload file';
		validationStatus=false;
    }
	*/

  let companyname = this.form.get('app_id').value;
  let unitname = this.form.get('unit_id').value;
  let standardname = this.form.get('standard_id').value;
  let buyername = this.form.get('buyer_id').value;
  let udpname = this.form.get('usda_nop_compliant').value;
  let sel_brand = this.form.get('sel_reduction').value;
  let brandid = this.form.get('brand_id').value;
  let auth_person_name = this.form.get('authorized_name').value;
  let brand_consent_date = this.form.get('brand_consent_date').value?this.errorSummary.displayDateFormat(this.form.get('brand_consent_date').value):'';

  if(companyname=='' || unitname =='' || standardname=='' || buyername=='' || udpname==''|| (sel_brand==1 && brandid=='')){
    formerror=true;
  }

    
  if(this.form.value.sel_reduction==1){
    
    if(auth_person_name=='' || brand_consent_date==''){
      formerror=true;
    }
  }
    if (formerror==false && validationStatus) {
    
    
      this.loading.button = true;
      let formvalue = this.form.value;
      formvalue.brand_consent_date=brand_consent_date;
      this.formData.append('formvalues',JSON.stringify(formvalue));
      //this.form.value
      this.requestservice.addData(this.formData)
      .pipe(
        first()        
      )
      .subscribe(res => {

			if(res.status)
			{
				this.id = res.id;
				if(this.id)
				{
					this.getRequestData(1);
				}
					
			    this.success = {summary:res.message};			      
			    setTimeout(() => {
					this.success = {};
					this.error = {};
					this.buttonDisable = false;	
					this.loading.button = false;
					this.tc_request_view=true;
					this.tc_request_edit=false;		
          this.formData = new FormData();			
					//this.router.navigateByUrl('/transaction-certificate/request/list');
				}, this.errorSummary.redirectTime);
			}
			else if(res.status == 0)
			{
			      this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
				  this.loading.button = false;
				  this.buttonDisable = false;
          this.formData = new FormData();
			}
			else
			{			      
				this.error = {summary:res};
				this.loading.button = false;
				this.buttonDisable = false;
			}         
      },
      error => {
          this.error = {summary:error};
          this.loading.button = false;
		  this.buttonDisable = false;
      });      
    } else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form); 
      
    }
  }
  removequa_examFiles(){
    this.qua_exam_file = '';
    this.formData.delete('qua_exam_file');
  }

  qua_examfileErrors='';
  qua_exam_file='';

  qua_examfileChange(element){
    let files = element.target.files;
    this.qua_examfileErrors ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.formData.append("qua_exam_file", files[0], files[0].name);
      this.qua_exam_file = files[0].name;
    }else{
      this.qua_examfileErrors ='Please upload valid file';
    }
    element.target.value = '';
  }
  fnTCEdit(arg)
  {
	  //this.tc_request_view = true;
	  //this.tc_request_edit = false;
	 	  	  
	  if(arg=='request_view'){
		this.tc_request_view = true;
		this.tc_request_edit = false;
	  }else if(arg=='request_edit'){
		this.tc_request_edit = true;
		this.tc_request_view = false;		
	  }
  }
  
  // -------------- Add / Edit  / Delete Product Code Start Here ---------------
  
  addProductPop(content)
  {			
  this.showconsigneeaddress = false;
	this.productdata = '';
	this.editProductStatus=0;    	
	
	this.productForm.reset();			
	
	this.productForm.patchValue({	  
      product_id:'',	  
	  transport_id:'',
	  consignee_id:''
    });
	
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title',centered: true});
	
  }
  
  productsuccess:any;
  producterror:any;  
  productloading:any=[];
  net_weightErr = '';
  gross_weightErr = '';
  certified_weightErr = '';
  
  addProduct()
  {
	this.net_weightErr = '';
	this.gross_weightErr = '';
	this.certified_weightErr = '';
  
  	this.pf.product_id.markAsTouched();
	this.pf.trade_name.markAsTouched();
	this.pf.packed_in.markAsTouched();
	this.pf.lot_ref_number.markAsTouched();
	this.pf.gross_weight.markAsTouched();
	this.pf.net_weight.markAsTouched();
	this.pf.certified_weight.markAsTouched();	
	
	this.pf.unit_information.markAsTouched();	
	this.pf.purchase_order_no.markAsTouched();
	this.pf.purchase_order_date.markAsTouched();
	this.pf.invoice_no.markAsTouched();	
	this.pf.invoice_date.markAsTouched();	
	this.pf.transport_document_no.markAsTouched();	
	this.pf.transport_document_date.markAsTouched();	
	this.pf.transport_company_name.markAsTouched();
	this.pf.vehicle_container_no.markAsTouched();		
	this.pf.transport_id.markAsTouched();	
	this.pf.consignee_id.markAsTouched();	
	
	let gross_weight = parseFloat(this.productForm.get('gross_weight').value);
	let certified_weight = parseFloat(this.productForm.get('certified_weight').value);
	let net_weight = parseFloat(this.productForm.get('net_weight').value);	

	if(gross_weight>0 && net_weight>0 && net_weight>gross_weight)	
	{
		this.gross_weightErr = 'Gross Weight should be greater than or eqaul to Net Weight';
		this.net_weightErr = 'Net Weight should be less than or eqaul to Gross Weight';
	}		
		
	if(certified_weight>0 && net_weight>0 && certified_weight>net_weight)	
	{
		this.net_weightErr = 'Net Weight should be greater than or eqaul to Certified Weight';	
		this.certified_weightErr = 'Certified Weight should be less than or eqaul to Net Weight';	
	}
	
	/*
	if(certified_weight>0 && net_weight>0 && certified_weight>net_weight)	
	{
		this.certified_weightErr = 'Certified Weight should be less than or eqaul to Net Weight';	
	}

	if(gross_weight>0 && certified_weight>0 && net_weight>gross_weight)	
	{
		this.net_weightErr = 'Net Weight should be less than or eqaul to Gross Weight';	
	}	
	*/
    		
	if(this.productForm.valid && this.gross_weightErr=='' && this.certified_weightErr=='' && this.net_weightErr=='')
	{
		let product_id = this.productForm.get('product_id').value;  
		let trade_name = this.productForm.get('trade_name').value;  
		let packed_in = this.productForm.get('packed_in').value;     
		let lot_ref_number = this.productForm.get('lot_ref_number').value;
		let gross_weight = this.productForm.get('gross_weight').value;
		let net_weight = this.productForm.get('net_weight').value;
		let certified_weight = this.productForm.get('certified_weight').value;
	  			
		let unit_information = this.productForm.get('unit_information').value;				
		let purchase_order_no = this.productForm.get('purchase_order_no').value;
		let purchase_order_date = this.errorSummary.displayDateFormat(this.productForm.get('purchase_order_date').value);
			
		let invoice_no = this.productForm.get('invoice_no').value;				
		let transport_document_no = this.productForm.get('transport_document_no').value;
		let invoice_date = this.errorSummary.displayDateFormat(this.productForm.get('invoice_date').value);
		let transport_document_date = this.errorSummary.displayDateFormat(this.productForm.get('transport_document_date').value);
		
		let transport_company_name = this.productForm.get('transport_company_name').value;  
		let vehicle_container_no = this.productForm.get('vehicle_container_no').value;  
		
		let transport_id = this.productForm.get('transport_id').value;
		let consignee_id = this.productForm.get('consignee_id').value;		
	  
		let dataproduct:any = {tc_request_id:this.id,product_id:product_id,trade_name:trade_name,packed_in:packed_in,lot_ref_number:lot_ref_number,gross_weight:gross_weight,net_weight:net_weight,certified_weight:certified_weight,unit_information:unit_information,purchase_order_no:purchase_order_no,purchase_order_date:purchase_order_date,invoice_no:invoice_no,transport_document_no:transport_document_no,invoice_date:invoice_date,transport_document_date:transport_document_date,transport_company_name:transport_company_name,vehicle_container_no:vehicle_container_no,transport_id:transport_id,consignee_id:consignee_id};
		if(this.productdata){
			dataproduct.id = this.productdata.id;
		}

      
		this.productloading['logsbutton'] = true;
		this.requestservice.addProductData(dataproduct)
		.pipe(first())
		.subscribe(res => {

          if(res.status){           
			      this.getProductData(this.id);
            this.productsuccess = res.message;
            setTimeout(() => {
    				this.productsuccess = '';
    				this.productdata = '';
    				this.buttonDisable = false;
    				this.productloading['logsbutton'] = false; 
    				this.modalss.close('');
            },this.errorSummary.redirectTime);
                        
          }else if(res.status == 0){
      			this.productloading['logsbutton'] = false;          
      			this.buttonDisable = false;
            this.producterror = {summary:res};
          }
          
		},
		error => {
          this.productloading['logsbutton'] = false;
          this.producterror = {summary:error};
          
		});
      
    }    
  }
  
  editProductStatus=0;
  productdata:any;
  editProduct(content,index:number,productdata) 
  {
	  this.productloading['logsbutton'] = false; 	
	  this.editProductStatus=1;  
    this.productsuccess = '';
    this.productdata = productdata;	

    this.loadAddress(productdata.consignee_id,'consignee')
	
	  this.productForm.patchValue({
	    id:productdata.id,
      product_id:productdata.product_id,
	    trade_name:productdata.trade_name,
      packed_in:productdata.packed_in,      
      lot_ref_number:productdata.lot_ref_number,
      gross_weight:productdata.gross_weight,
      net_weight:productdata.net_weight,
      certified_weight:productdata.certified_weight,
      transport_document_date:this.errorSummary.editDateFormat(productdata.transport_document_date),
      invoice_date:this.errorSummary.editDateFormat(productdata.invoice_date),
      transport_document_no:productdata.transport_document_no,
      invoice_no:productdata.invoice_no,
      purchase_order_no:productdata.purchase_order_no,
      purchase_order_date:this.errorSummary.editDateFormat(productdata.purchase_order_date),	
	  transport_company_name:productdata.transport_company_name,
	  vehicle_container_no:productdata.vehicle_container_no,	  
      unit_information:productdata.unit_information,
      transport_id:productdata.transport_id,
      consignee_id:productdata.consignee_id,
      ifoam_standard:productdata.ifoam_standard
    });
	
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title',centered: true});
  } 

  getProductData(dataid){
    //this.productEntries = [];
    this.productloading['productdata'] =true;
    this.requestservice.getProductData({tc_request_id:dataid})
    .pipe(first())
    .subscribe(res => {

      this.productloading['productdata'] =false;
      if(res.status){
        //this.productEntries = res['data'];
		this.resultdata.requestproduct = res['data'];
		
		let openpdt = this.resultdata.requestproduct.find(x=> x.product_status==0);
		let closepdt = this.resultdata.requestproduct.find(x=> x.product_status==1);
    
     this.evidenceFormStatus=false;
		if(openpdt === undefined && closepdt!==undefined)
		{
			this.evidenceFormStatus=true;
		}		
      }else if(res.status == 0){
        this.error = {summary:res};
      }       
    },
    error => {
        this.error = {summary:error};
        this.productloading['productdata'] =false;
    });
  } 
  confirmContentText:any = '';
  removeProductData(content,data) {
	  
	  this.model.id = data.id;
    this.model.action = 'productdelete';
	  this.confirmContentText = 'Are you sure, do you want to delete the data?';
      this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

      this.modalss.result.then((result) => {      		
          
      }, (reason) => {
      })
      
    
  } 

  
  cloneProductData(data){
    
    this.productdata = '';

    let dataproduct:any = {tc_request_id:this.id,product_id:data.product_id,trade_name:data.trade_name,packed_in:data.packed_in,lot_ref_number:data.lot_ref_number,gross_weight:data.gross_weight,net_weight:data.net_weight,certified_weight:data.certified_weight,unit_information:data.unit_information,purchase_order_no:data.purchase_order_no,purchase_order_date:data.purchase_order_date,invoice_no:data.invoice_no,transport_document_no:data.transport_document_no,invoice_date:data.invoice_date,transport_document_date:data.transport_document_date,transport_company_name:data.transport_company_name,vehicle_container_no:data.vehicle_container_no,transport_id:data.transport_id,consignee_id:data.consignee_id};
		if(this.productdata){
			dataproduct.id = this.productdata.id;
		}
		this.productloading['logsbutton'] = true;

		this.requestservice.addProductData(dataproduct)
		.pipe(first())
		.subscribe(res => {

          if(res.status){           
			      this.getProductData(this.id);
            this.productsuccess = res.message;
            setTimeout(() => {
    				this.productsuccess = '';
    				this.productdata = '';
    				this.buttonDisable = false;
    				this.productloading['logsbutton'] = false; 
    				// this.modalss.close('');
            },this.errorSummary.redirectTime);
                        
          }else if(res.status == 0){
      			this.productloading['logsbutton'] = false;          
      			this.buttonDisable = false;
            this.producterror = {summary:res};
          }
          
		},
		error => {
          this.productloading['logsbutton'] = false;
          this.producterror = {summary:error};
          
		});
  }


  fnDeleteProduct()
  {
	  this.buttonDisable = true;
	this.requestservice.deleteProductData({id:this.model.id})
	  .pipe(first())
	  .subscribe(res => {
		  if(res.status){
			this.getProductData(this.id);
			this.success = {summary:res.message};
			
			this.alertSuccessMessage = res.message;
			setTimeout(()=>{
				this.alertSuccessMessage='';
				this.buttonDisable = false;
				this.modalss.close('');
			},this.errorSummary.redirectTime);
			
			
			this.model.id = '';
      this.model.action = '';
		  }else if(res.status == 0){
			  this.buttonDisable = false;
				this.error = {summary:res};
		  }
		  //this.loading['button'] = false;
		  
	  },
	  error => {
		  this.error = {summary:error};
		  //this.loading['button'] = false;
	  });
  }	

  viewProduct(content,data)
  {
    this.viewproductData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }  
  
  // -------------- Add / Edit  / Delete Product Code End Here ---------------
  
  
  // -------------- Add / Edit / Delete Raw Material Stock Code Start Here ---------------  
  remainingcertifiedweight:any;
  logsuccess:any;
  addRawMaterialInputPop(content,data)
  {	
	this.inputloading['stockbutton'] = false;
	this.inputloading['inputdatabutton'] = false;
	this.stocksuccess = {};
	this.stockerror = {};
	this.logsuccess = false;
	this.remainingWeightError = '';
	this.remainingWeightSuccess = '';
  this.minRawMaterialRequiredErr = '';
  this.weightError = [];
	//this.inputMaterialForm.reset();
	//this.ngForm.reset();
	
  this.remainingcertifiedweight = 0;
  
	this.viewinputmaterialdata = data;
	//this.remainingcertifiedweight = this.viewinputmaterialdata.total_net_weight;
	
	/*
	this.inputForm.patchValue({	  
      wastage_percentage:data.wastage_percentage
	});
	*/
	
	this.inputmaterialweightlist['wastage_percentage']= data.wastage_percentage;
	this.inputmaterialweightlist['additional_weight']= data.additional_weight;
	
	this.getStandardwisematerial(data.id);  
	
	this.inputdata = '';
	this.editProductStatus=0; 
	
	/*
	if(this.rawmaterialids && this.rawmaterialids.length>0)
	{
        this.viewinputmaterialdata.forEach(val=>{
			this.inputmaterialweightlist['input_weight'+val.id]= 0;			
        });
		this.remainingcertifiedweight = 0;
    }
	
	if(this.viewinputmaterialdata.rawmaterialusedlist && this.viewinputmaterialdata.rawmaterialusedlist.length>0)
	{
        this.viewinputmaterialdata.rawmaterialusedlist.forEach(val=>{
			this.inputmaterialweightlist['input_weight'+val.tc_raw_material_id]= val.used_weight;			
        });
		this.remainingcertifiedweight = 0;
    }
	*/

	//rawmaterialusedlist
	//resultdata?.requestproduct   	
	
	//this.productForm.reset();			
	
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  
  inputsuccess:any;
  inputerror:any;
  inputloading:any=[];
  rawMaterialWeightList:any=[];
  rawMaterialKeyList:any = [];
  addRawMaterialInput(f:NgForm)
  {
	this.remainingWeightError = '';
	this.remainingWeightSuccess = '';
	
	let formerror = false;
	
	let tc_request_product_id = this.viewinputmaterialdata.id;
	
	if(this.rawmaterialProductIds.length>0)
	{				
		if (f.valid) 
		{
			let rminputweight:any;
			rminputweight = 0;
			this.rawmaterialProductIds.forEach(rmK => {
				let rmid = rmK;			
				let iw = eval("f.value.input_weight"+rmid);
				if(iw!='' && iw!==undefined)
				{
					rminputweight = parseFloat(rminputweight) + parseFloat(eval("f.value.input_weight"+rmid));							
				}				
			});
			
			let tcw = this.viewinputmaterialdata.total_net_weight.toString();
			//let totW = parseFloat(tcw) - rminputweight;
			let totW = rminputweight;
			this.remainingcertifiedweight = totW.toFixed(2);
			
			this.inputloading['inputdatabutton'] = false;
			
			if(this.remainingcertifiedweight < parseFloat(tcw))
			{
				this.remainingWeightError = 'Input Weight should be greater than or equal to Raw Material Required (Net Weight + Wastage Weight + Additional Weight)';
				this.inputloading['inputdatabutton'] = true;
			}
			
			/*
			if(this.remainingcertifiedweight<0)
			{
				this.remainingWeightError = 'Input Weight should not exceed the Total Certified Weight';
				this.inputloading['inputdatabutton'] = true;
			}else if(this.remainingcertifiedweight>0){
				this.remainingWeightError = 'Input Weight should be equal to Total Certified Weight';
				this.inputloading['inputdatabutton'] = true;
			}else if(this.remainingcertifiedweight==0){
				this.remainingWeightSuccess = 'Input Weight matched with Total Certified Weight';				
			}
			*/					
			
			if(this.remainingWeightError=='')
			{
				this.remainingWeightSuccess = '';
				
				let inputweight = [];			
				this.rawmaterialProductIds.forEach(rmK => {
					let rmid = rmK;					
					let rminputweight = eval("f.value.input_weight"+rmid);	
					if(rminputweight!='' && rminputweight!==undefined)
					{					
            let rawmaterialdata = this.rawMaterialKeyList.find(rk=>rk.rawmaterial_product_id==rmid);
            let stdkey:any = '';
            let stdtype:any = '';
            if(rawmaterialdata !==undefined){
              stdkey = rawmaterialdata.stdkey;
              stdtype = rawmaterialdata.type;
            }
            //let qdata= {stdtype,stdkey,tc_raw_material_id:rmid,rminputweight:rminputweight};	
            let qdata= {stdtype,stdkey,tc_raw_material_product_id:rmid,rminputweight:rminputweight};					
            
						inputweight.push(qdata);
					}	
				});			
				
				let inputmaterialdata = {inputweight,tc_request_product_id:tc_request_product_id};

				//this.loading  = true;
								
				this.inputloading['stockbutton'] = true;
				this.inputloading['inputdatabutton'] = true;
				
				this.requestservice.productWiseRawMaterialInputs(inputmaterialdata)
				.pipe(first())
				.subscribe(res => {				  
					if(res.status==1){
						  this.stocksuccess = {summary:res.message};
						  this.logsuccess = true;
						  //this.buttonDisable = true;
						  
						  this.getProductData(this.id);
						  
						  setTimeout(() => {
							this.inputloading['stockbutton'] = false;
							this.inputloading['inputdatabutton'] = false;
							this.stocksuccess = '';
							 this.logsuccess = false;
							this.modalss.close('');
						  }, this.errorSummary.redirectTime);
						  
						}else if(res.status == 2){
              this.stockerror = {summary:res.message};
              this.weightError = [...res.weightErrorList];
              //this.rawMaterialWeightList = [...res.rawMaterialWeightList];
              let rawMaterialWeightList = res.rawMaterialWeightList;
              let errordata = 'The Net weight was changed and below the Use from Stock(kg) for the following Raw Material(s):<br>';
              let cnt =1;
              if(this.weightError && this.weightError.length>0){
                this.weightError.forEach(rawmaterialid=>{
                  let rwerr_data = rawMaterialWeightList[rawmaterialid]?rawMaterialWeightList[rawmaterialid]:undefined;
                  
                  if(rwerr_data !== undefined){
                    if(rwerr_data.rawmaterial_standard_type == 'standard'){
                      let rawmaterialindex = this.rawmaterialstandardcontent[rwerr_data.rawmaterial_standard_key].findIndex(xx=>xx.id == rawmaterialid);
                      if(rawmaterialindex !== -1){
                        if(rwerr_data.status == 1){
                          this.rawmaterialstandardcontent[rwerr_data.rawmaterial_standard_key].splice(rawmaterialindex,1);
                        }else{
                          this.rawmaterialstandardcontent[rwerr_data.rawmaterial_standard_key][rawmaterialindex].net_weight = rwerr_data.net_weight;
                        }
                      }
                      
                      
                    }else{
                      if(rwerr_data.status == 1){
                        delete this.rawmaterialwithoutstandardcontent[rwerr_data.rawmaterial_standard_key];
                      }else{
                        if(this.rawmaterialwithoutstandardcontent[rwerr_data.rawmaterial_standard_key][0]){
                          this.rawmaterialwithoutstandardcontent[rwerr_data.rawmaterial_standard_key][0].net_weight = rwerr_data.net_weight;
                        }
                        
                      }
                    }
                    
                    errordata=errordata+`${cnt}. ${rwerr_data.supplier_name} - ${rwerr_data.trade_name}<br>`;
                    cnt = cnt+ 1;
                  }
                    
                })
              }
              this.stockerror = {summary:errordata}

						  this.inputloading['stockbutton'] = false;
              this.inputloading['inputdatabutton'] = false;
              //this.getStandardwisematerial(tc_request_product_id,0);
						   
						}else if(res.status == 0){
						  this.stockerror = {summary:res.message};
						  this.inputloading['stockbutton'] = false;
              this.inputloading['inputdatabutton'] = false;
              //this.getStandardwisematerial(tc_request_product_id,0);
						   
						}else{
						  this.stockerror = {summary:res};
						   
						  this.inputloading['stockbutton'] = false;
						  this.inputloading['inputdatabutton'] = false;
						}
										  
					},
					error => {
						this.stockerror = {summary:error};
            this.inputloading['stockbutton'] = false;
            this.inputloading['inputdatabutton'] = false;
						 
					}
				);
			}		
			
		} else {
			this.stockerror = {summary:'Please fill all the mandatory fields (marked with *)'};
		}
	}
	
	
	return false;
	
	/*
	if(this.rawmaterialstandardname)
	{
		this.rawmaterialstandardname.forEach(element => {

			let qid = element.id;
			let findings = eval("f.value.input_weight"+qid);
			
			if((findings==null || findings.trim() =='')){
				f.controls["finding"+qid].markAsTouched();
				formerror=true;
			}    
		});
	}	
	return false;
	*/
    		
	if(this.productForm.valid)
	{
	  let product_id = this.productForm.get('product_id').value;  
	  let trade_name = this.productForm.get('trade_name').value;  
      let packed_in = this.productForm.get('packed_in').value;     
      let lot_ref_number = this.productForm.get('lot_ref_number').value;
	  let gross_weight = this.productForm.get('gross_weight').value;
	  let net_weight = this.productForm.get('net_weight').value;
	  let certified_weight = this.productForm.get('certified_weight').value;
	  
      let dataproduct:any = {data_id:this.id,product_id:product_id,trade_name:trade_name,packed_in:packed_in,lot_ref_number:lot_ref_number,gross_weight:gross_weight,net_weight:net_weight,certified_weight:certified_weight};
      if(this.inputdata){
        dataproduct.id = this.inputdata.id;
      }

      
      this.inputloading['logsbutton'] = true;
      this.requestservice.addProductData(dataproduct)
      .pipe(first())
      .subscribe(res => {

          if(res.status){           
			this.getProductData(this.id);
            this.inputsuccess = res.message;
            setTimeout(() => {
              this.inputsuccess = '';
              this.inputdata = '';
              this.modalss.close('');
            },this.errorSummary.redirectTime);
            
            this.buttonDisable = true;
          }else if(res.status == 0){
            this.inputerror = {summary:res};
          }
          this.inputloading['logsbutton'] = false;
          
          this.buttonDisable = false;
      },
      error => {
          this.inputloading['logsbutton'] = false;
          this.inputerror = {summary:error};
          
      });
      
    }    
  }
  
  editInputStatus=0;
  inputdata:any;
  editRawMaterialInput(content,index:number,inputdata) {
		
	this.editInputStatus=1;  
    this.inputsuccess = '';
    this.inputdata = inputdata;	
	
	this.productForm.patchValue({
      product_id:inputdata.product_id,
      packed_in:inputdata.packed_in,      
      lot_ref_number:inputdata.lot_ref_number,
	  gross_weight:inputdata.gross_weight,
	  net_weight:inputdata.net_weight,
	  certified_weight:inputdata.certified_weight
    });
	
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  } 

  getRawMaterialInputData(dataid){
    //this.productEntries = [];
    this.inputloading['inputdata'] =true;
    this.requestservice.getProductData({data_id:dataid})
    .pipe(first())
    .subscribe(res => {

      this.inputloading['inputdata'] =false;
      if(res.status){
        //this.productEntries = res['data'];
		this.resultdata.requestproduct = res['data'];
      }else if(res.status == 0){
        this.error = {summary:res};
      }       
    },
    error => {
        this.error = {summary:error};
        this.inputloading['inputdata'] =false;
    });
  }    
  // -------------- Add / Edit / Delete Raw Material Stock Code End Here ---------------
  weightError:any = [];
  calculateRemainingWeight(f:NgForm,val:any,cvalid:any,certified_weight:any=0,materialid:any=0,enteredweight:any=0)
  {
	
	if(val!='')
	{
		val = parseFloat(parseFloat(val).toFixed(2));
	}
	let totcertified_weight:any = parseFloat(certified_weight) + parseFloat(enteredweight);
	//console.log(enteredweight);
	//console.log(totcertified_weight);
	if(totcertified_weight>0)
	{
		totcertified_weight = parseFloat(parseFloat(totcertified_weight).toFixed(2));
	}
	
    //console.log(val+'---'+certified_weight);
    if(val>totcertified_weight){
      this.weightError.push(materialid);
    }else{
      let weightindex = this.weightError.indexOf(materialid)
      if( weightindex !== -1){
        this.weightError.splice(weightindex,1);
      }
    }
		this.remainingWeightError = '';
		this.remainingWeightSuccess = '';
		
		if(f.valid)
		{
			let rminputweight:any;
			rminputweight = 0;
			this.rawmaterialProductIds.forEach(rmK => {
				let rmid = rmK;			
				let iw = eval("f.value.input_weight"+rmid);
				if(iw!='' && iw!==undefined)
				{
					rminputweight = parseFloat(rminputweight) + parseFloat(eval("f.value.input_weight"+rmid));							
				}				
			});
			
			let tcw = this.viewinputmaterialdata.total_net_weight.toString();
			//let totW = parseFloat(tcw) - rminputweight;
			let totW = rminputweight;
			this.remainingcertifiedweight = totW.toFixed(2);
			
			this.inputloading['inputdatabutton'] = false;
			
			if(this.remainingcertifiedweight<parseFloat(tcw))
			{
				this.remainingWeightError = 'Input Weight should be greater than or equal to Raw Material Required (Net Weight + Wastage Weight - Additional Weight)';
				this.inputloading['inputdatabutton'] = true;
			}
			
			/*
			if(this.remainingcertifiedweight<0)
			{
				this.remainingWeightError = 'Input Weight should not exceed the Total Certified Weight';
				this.inputloading['inputdatabutton'] = true;
			}else if(this.remainingcertifiedweight>0)
			{
				this.remainingWeightError = 'Input Weight should be equal to Total Certified Weight';
				this.inputloading['inputdatabutton'] = true;
			}else if(this.remainingcertifiedweight==0){
				this.remainingWeightSuccess = 'Input Weight matched with Total Certified Weight';				
			}
			*/
		}
  }
  
  changeStatus(content,data)
  {
	  this.loading.button = true;
	  this.buttonDisable = true;
		this.model.data = data;	
    this.model.action = 'approval';  
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  changeStatusConfirm(){
    this.requestservice.changeStatus({id:this.id,tc_status:this.model.data})
    .pipe(first())
    .subscribe(res => {
     
      if(res.status)
      {
        if(this.id)
        {
          this.getRequestData();
        }
        this.alertSuccessMessage = res.message;
        setTimeout(()=>{
          this.model.id = '';
          this.model.action = '';
          this.model.data = '';

          this.alertSuccessMessage='';
          this.buttonDisable = false;
          this.modalss.close('');

        },this.errorSummary.redirectTime);

      }else if(res.status == 0){
        this.buttonDisable = false;
        this.error = {summary:res.message};
      }else{
        this.buttonDisable = false;
        this.error = {summary:res};
      }      
    },
    error => {
      this.error = {summary:error};
      this.loading.button = false;
      this.buttonDisable = false;
    });  
       
  }
  
  DownloadFile(val,tcfilename)
  {
    //this.loading  = true;
    this.requestservice.downloadFile({id:val})
     .pipe(first())
     .subscribe(res => {
      //this.loading = false;
      this.modalss.close();
      saveAs(new Blob([res],{type:'application/pdf'}),tcfilename);
    },
    error => {
      this.error = error;
      //this.loading = false;
      this.modalss.close();
    });
  }
  
  wastagePercentageErr=true;  
  inputRawMaterialNgForm:any;
  changeProductWastage(content,f:NgForm)
  {
    this.alertWeightErrorMessage = '';
	this.wastagePercentageErr=true;
	this.inputRawMaterialNgForm = f;			
	let wastagePercentage =  parseFloat(eval("this.inputRawMaterialNgForm.value.wastage_percentage"));
	if(wastagePercentage<0 || wastagePercentage>99 || isNaN(wastagePercentage))
	{
		this.wastagePercentageErr=false;	
	}	
		
	if(f.valid && this.wastagePercentageErr)
	{  
		this.model.data = this.viewinputmaterialdata;			
		this.pwmodalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
		
		this.pwmodalss.result.then((result) => {

		}, (reason) => {
			/*
			this.inputForm.patchValue({	  
				wastage_percentage:this.viewinputmaterialdata.wastage_percentage
			});	
			*/
			this.inputmaterialweightlist['wastage_percentage'] = this.viewinputmaterialdata.wastage_percentage;
		});
	}
  }
  
  additionalWeightErr=true;
  changeAdditionalWeight(content,f:NgForm)
  {
    this.alertWeightErrorMessage = '';
    this.additionalWeightErr=true;
    this.inputRawMaterialNgForm = f;			
    let additionalWeight =  parseFloat(eval("this.inputRawMaterialNgForm.value.additional_weight"));
    
    //if(additionalWeight<0 || additionalWeight>99 || isNaN(additionalWeight))		
    if(additionalWeight<0 || isNaN(additionalWeight))	
    {
      this.additionalWeightErr=false;	
    }	
      
    if(f.valid && this.additionalWeightErr)
    {  
      this.model.data = this.viewinputmaterialdata;			
      this.pwmodalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    
      this.pwmodalss.result.then((result) => {

      }, (reason) => {
        /*
        this.inputForm.patchValue({	  
          additional_weight:this.viewinputmaterialdata.additional_weight
        });	
        */
        this.inputmaterialweightlist['additional_weight'] = this.viewinputmaterialdata.additional_weight;
      });
    }
  }
  
  changeProductWastageConfirm()
  {
	this.productWastageConfirmButtonDisable=false;	
	if(this.inputRawMaterialNgForm.valid) 
	{ 
		this.remainingWeightError='';
		this.remainingWeightSuccess='';		
		
		this.productWastageConfirmButtonDisable=true;		
		let wastage_percentage = eval("this.inputRawMaterialNgForm.value.wastage_percentage");

		this.requestservice.changeProudctWastagePercentage({id:this.id,tc_request_product_id:this.viewinputmaterialdata.id,wastage_percentage:wastage_percentage})
		.pipe(first())
		.subscribe(res => {
		 
		  if(res.status)
		  {
			this.alertProductSuccessMessage = res.message;
			
			let prodData = res.productdetails;
			
			this.viewinputmaterialdata.wastage_percentage = prodData.wastage_percentage;
			this.viewinputmaterialdata.gross_weight = prodData.gross_weight;
			this.viewinputmaterialdata.net_weight = prodData.net_weight;
			this.viewinputmaterialdata.certified_weight = prodData.certified_weight;
			this.viewinputmaterialdata.wastage_weight = prodData.wastage_weight;
			this.viewinputmaterialdata.total_net_weight = prodData.total_net_weight;						
			
			let pindex = this.resultdata.requestproduct.findIndex(s => s.id ==  this.viewinputmaterialdata.id);			
			if(pindex != -1)
			{
				this.resultdata.requestproduct[pindex] = prodData;			
			}
			
			// ----------- Calculate the Certified Weight based on the Stock Selection Code Start Here-----------
			let rminputweight:any;
			rminputweight = 0;
			this.rawmaterialProductIds.forEach(rmK => {
				let rmid = rmK;			
				let iw = eval("this.inputRawMaterialNgForm.value.input_weight"+rmid);				
				if(iw!='' && iw!==undefined)
				{
					rminputweight = parseFloat(rminputweight) + parseFloat(iw);							
				}				
			});
			
			let tcw = this.viewinputmaterialdata.total_net_weight.toString();
			//let totW = parseFloat(tcw) - rminputweight;
			
			let totW = rminputweight;	
			this.remainingcertifiedweight = totW.toFixed(2);
			// ----------- Calculate the Certified Weight based on the Stock Selection Code End Here-----------
			
			if(this.remainingcertifiedweight<parseFloat(tcw))
			{
				this.remainingWeightError = 'Input Weight should be greater than or equal to Raw Material Required (Net Weight + Wastage Weight + Additional Weight)';
				this.inputloading['inputdatabutton'] = true;
			}else{
        this.inputloading['inputdatabutton'] = false;
      }
			
			/*
			else if(this.remainingcertifiedweight>0)
			{
				this.remainingWeightError = 'Input Weight should be equal to Total Certified Weight';
				this.inputloading['inputdatabutton'] = true;
			}else if(this.remainingcertifiedweight==0){
				this.remainingWeightSuccess = 'Input Weight matched with Total Certified Weight';				
			}
			*/
						
			setTimeout(()=>{
			  this.model.id = '';
			  this.model.action = '';
			  this.model.data = '';

			  this.alertProductSuccessMessage='';
			  this.productWastageConfirmButtonDisable = false;
			  this.pwmodalss.close('');

			},this.errorSummary.redirectTime);

		  }else if(res.status == 0){
        this.productWastageConfirmButtonDisable = false;
        //this.error = {summary:res.message};
        this.alertWeightErrorMessage=res.message;
        this.inputmaterialweightlist['wastage_percentage'] = this.viewinputmaterialdata.wastage_percentage;
		  }else{
			this.productWastageConfirmButtonDisable = false;
      //this.error = {summary:res};
      this.alertWeightErrorMessage=res;
      this.inputmaterialweightlist['wastage_percentage'] = this.viewinputmaterialdata.wastage_percentage;
		  }      
		},
		error => {
      //this.error = {summary:error};
      this.alertWeightErrorMessage=error;
      this.inputmaterialweightlist['wastage_percentage'] = this.viewinputmaterialdata.wastage_percentage;
		  this.loading.button = false;
		  this.buttonDisable = false;
		}); 
	}	
       
  }
  minRawMaterialRequiredErr:any = '';
  changeAdditionalWeightConfirm()
  {
    this.additionalWeightConfirmButtonDisable=false;
    this.minRawMaterialRequiredErr = '';	
    if(this.inputRawMaterialNgForm.valid) 
    { 
      this.remainingWeightError='';
      this.remainingWeightSuccess='';		
      
      this.additionalWeightConfirmButtonDisable=true;		
      let additional_weight = eval("this.inputRawMaterialNgForm.value.additional_weight");

      this.requestservice.changeAdditionalWeight({id:this.id,tc_request_product_id:this.viewinputmaterialdata.id,additional_weight:additional_weight})
      .pipe(first())
      .subscribe(res => {
      
        if(res.status)
        {
          this.alertAdditionalWeightSuccessMessage = res.message;
          
          let prodData = res.productdetails;
          
          this.viewinputmaterialdata.additional_weight = prodData.additional_weight;
          this.viewinputmaterialdata.gross_weight = prodData.gross_weight;
          this.viewinputmaterialdata.net_weight = prodData.net_weight;
          this.viewinputmaterialdata.certified_weight = prodData.certified_weight;
          this.viewinputmaterialdata.wastage_weight = prodData.wastage_weight;
          this.viewinputmaterialdata.total_net_weight = prodData.total_net_weight;						
          
          let pindex = this.resultdata.requestproduct.findIndex(s => s.id ==  this.viewinputmaterialdata.id);			
          if(pindex != -1)
          {
            this.resultdata.requestproduct[pindex] = prodData;			
          }
          
          // ----------- Calculate the Certified Weight based on the Stock Selection Code Start Here-----------
          let rminputweight:any;
          rminputweight = 0;
          this.rawmaterialProductIds.forEach(rmK => {
            let rmid = rmK;			
            let iw = eval("this.inputRawMaterialNgForm.value.input_weight"+rmid);				
            if(iw!='' && iw!==undefined)
            {
              rminputweight = parseFloat(rminputweight) + parseFloat(iw);							
            }				
          });
          
          let tcw = this.viewinputmaterialdata.total_net_weight.toString();
          //let totW = parseFloat(tcw) - rminputweight;
          
          let totW = rminputweight;	
          this.remainingcertifiedweight = totW.toFixed(2);
          // ----------- Calculate the Certified Weight based on the Stock Selection Code End Here-----------
          
          if(this.remainingcertifiedweight<parseFloat(tcw))
          {
            this.remainingWeightError = 'Input Weight should be greater than or equal to Raw Material Required (Net Weight + Wastage Weight + Additional Weight)';
            this.inputloading['inputdatabutton'] = true;
          }else{
            this.inputloading['inputdatabutton'] = false;
          }		
                
          setTimeout(()=>{
            this.model.id = '';
            this.model.action = '';
            this.model.data = '';

            this.alertAdditionalWeightSuccessMessage='';
            this.additionalWeightConfirmButtonDisable = false;
            this.pwmodalss.close('');

          },this.errorSummary.redirectTime);

        }else if(res.status == 0){
          this.inputmaterialweightlist['additional_weight']= this.viewinputmaterialdata.additional_weight;
          this.additionalWeightConfirmButtonDisable = false;
          //this.pwmodalss.close('');
          //this.minRawMaterialRequiredErr = res.message;
          this.alertWeightErrorMessage=res.message;
        }else{
          this.inputmaterialweightlist['additional_weight']= this.viewinputmaterialdata.additional_weight;
          this.additionalWeightConfirmButtonDisable = false;
          //this.pwmodalss.close('');
          this.alertWeightErrorMessage=res;
          //this.minRawMaterialRequiredErr = res;
        }      
      },
      error => {
        this.inputmaterialweightlist['additional_weight']= this.viewinputmaterialdata.additional_weight;
        this.alertWeightErrorMessage = error;
        //this.minRawMaterialRequiredErr = error;
        this.loading.button = false;
        this.buttonDisable = false;
      }); 
    }	
  }
  
  validateWeight()
  {
	this.gross_weightErr = '';
	this.net_weightErr = '';
	this.certified_weightErr = '';
	
	let gross_weight = parseFloat(this.productForm.get('gross_weight').value);
	let certified_weight = parseFloat(this.productForm.get('certified_weight').value);
	let net_weight = parseFloat(this.productForm.get('net_weight').value);
			
	if(gross_weight>0 && net_weight>0 && net_weight>gross_weight)	
	{
		this.gross_weightErr = 'Gross Weight should be greater than or eqaul to Net Weight';
		this.net_weightErr = 'Net Weight should be less than or eqaul to Gross Weight';
	}		
		
	if(certified_weight>0 && net_weight>0 && certified_weight>net_weight)	
	{
		this.net_weightErr = 'Net Weight should be greater than or eqaul to Certified Weight';	
		this.certified_weightErr = 'Certified Weight should be less than or eqaul to Net Weight';	
	}
	
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
}
