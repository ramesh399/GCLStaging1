import { Component, OnInit, Input } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditClientinformationService } from '@app/services/audit/audit-clientinformation.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { tap,map, first } from 'rxjs/operators'; 
import {Observable} from 'rxjs';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import { Renderer, ElementRef } from '@angular/core';
@Component({
  selector: 'app-audit-clientinformation-supplier',
  templateUrl: './audit-clientinformation-supplier.component.html',
  styleUrls: ['./audit-clientinformation-supplier.component.scss']
})
export class AuditClientinformationSupplierComponent implements OnInit {
  @Input() app_id: number;
  @Input() cond_viewonly: any;
  @Input() audit_id: number;

  title = 'Audit Interview Employee'; 
  form : FormGroup; 
  supplierform : FormGroup;
  processform : FormGroup;
 
  id:number;
  //audit_id:number;
  unit_id:number;

  error:any;
  success:any;
  buttonDisable = false;
  formData:FormData = new FormData();
  companyForm : any = {};
  companydetails:any = [];
  supplierdetails:any = [];
  
  categorylist:any = {};
  model: any = {sufficient:''}
  availablelist:any;
  sufficientlist:any;
  applicablelist:any;
  SupplierData:any;
  supplierData:any;
  
  
  userType:number;
  userdetails:any;
  userdecoded:any;
  modalss:any;
  loading:any=[];
  answerArr:any;
  isItApplicable=true;
  dataloaded = false;
  remarkForm : FormGroup;

  constructor(private elRef: ElementRef, private renderer: Renderer, private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: AuditClientinformationService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
  }
  
  generalOptions:any = [];
  

  ngOnInit() 
  {
    if(!this.audit_id){
      this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    }
    //this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
     
    this.supplierform = this.fb.group({	
      supplier_name:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]], 
      products_composition:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      supplier_address:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      //validity:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
     // available_in_gots_database:['',[Validators.required]],
       
      sufficient:['',[Validators.required]],
    });

    this.remarkForm = this.fb.group({	
      remark:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]]
    });

    
    this.service.getRemarkData({audit_id:this.audit_id,unit_id:this.unit_id,app_id:this.app_id,type:'supplier_list'}).pipe(first())
    .subscribe(res => {    
      this.dataloaded = true;
      if(res!==null)
      {
        this.isApplicable = res.status;
        if(res.status==1)
        {
          this.isItApplicable=true; 
        }else{
          this.isItApplicable=false;
        }	 
        
        if(res.comments)
        {
          this.editRemarkStatus =1;
          this.remarkForm.patchValue({
            'remark':res.comments
          });
        }
      }

    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });
     
    this.loadSupplierInformation();
    
    this.service.getGeneralInformation({audit_id:this.audit_id,unit_id:this.unit_id}).pipe(first())
    .subscribe(res => {    
        this.generalOptions = res.sufficientOptions;
    }); 
    
    this.service.getOptionlist().pipe(first())
    .subscribe(res => {   
        this.availablelist = res.availablelist;
        this.applicablelist = res.applicablelist;
        this.sufficientlist = res.sufficientlist;
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

  sufficient_access:number = 0;
  loadSupplierInformation()
  {
    this.service.getSupplierInformation({audit_id:this.audit_id,unit_id:this.unit_id,app_id:this.app_id}).pipe(first())
    .subscribe(res => {    
      this.dataloaded = true;
      this.supplierdetails = res.suppliers;
      this.sufficient_access = res.sufficient_access;
    }); 
  }

   
  editRemarkStatus=0;
  addsupplier()
  {
    this.sf.supplier_name.markAsTouched();
     
    this.sf.products_composition.markAsTouched();
    this.sf.supplier_address.markAsTouched();
    //this.sf.available_in_gots_database.markAsTouched();
    
    if(this.sufficient_access){
      this.sf.sufficient.markAsTouched(); 
    }else{
      this.sf.sufficient.setValidators([]);
      this.sf.sufficient.updateValueAndValidity();
    }

    if(this.supplierform.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true; 

      let supplier_name = this.supplierform.get('supplier_name').value;
      //let validity = this.supplierform.get('validity').value;
      let products_composition = this.supplierform.get('products_composition').value;
      let supplier_address = this.supplierform.get('supplier_address').value;
      //let available_in_gots_database = this.supplierform.get('available_in_gots_database').value;
      
	    let sufficient:any = '';
      if(this.sufficient_access){
        sufficient = this.supplierform.get('sufficient').value;
      }
      
      let expobject:any={app_id:this.app_id,audit_id:this.audit_id,unit_id:this.unit_id,supplier_name:supplier_name,products_composition:products_composition,supplier_address:supplier_address,sufficient:sufficient,type:'supplier_list'};
      
      if(1)
      {
        if(this.supplierData)
        {
          expobject.id = this.supplierData.id;
        }
        
        this.service.addSupplierData(expobject)
        .pipe(first())
        .subscribe(res => {
            if(res.status){
              this.editRemarkStatus =0;
              this.remarkForm.patchValue({
                'remark':''
              });
              this.success = {summary:res.message};
              this.supplierFormreset(); 
              this.loading['button'] = false;
              this.loadSupplierInformation();
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
        });
        
      } else {
        this.loading['button'] = false;
        this.buttonDisable = false;
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.supplierform); 
        
      }   
    }
  }
  
  isApplicable:number;
  isItApp(arg)
  {
    this.isApplicable = arg;
     
	  if(arg==1)
	  {
      this.editStatus = 0;
		  this.isItApplicable=true;
	  }else{
		  this.isItApplicable=false;
	  }	  
  }

  addRemark()
  {
    this.rf.remark.markAsTouched();

    if(this.remarkForm.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let remark = this.remarkForm.get('remark').value;

      let expobject:any={app_id:this.app_id,unit_id:this.unit_id,audit_id:this.audit_id,comments:remark,is_applicable:this.isApplicable,type:'supplier_list'}

      this.service.addRemark(expobject)
      .pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.editRemarkStatus =1;
          this.success = {summary:res.message};
          this.buttonDisable = false;
          this.loading['button'] = false;
        }
      },
      error => {
        this.buttonDisable = false;
          this.error = {summary:error};
          this.loading['button'] = false;
      });
    }
  }

  remarkFormreset()
  {
    this.editStatus=0;
    this.remarkForm.reset();
    
    this.remarkForm.patchValue({     
      remark:''
    });
  }

  
  viewSupplier(content,data)
  {
    this.suppliersuccess = '';
    this.suppliererror = '';
    
    this.model.sufficient = data.sufficient;
    this.SupplierData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }
 

  editStatus=0;
  editSupplier(index:number,supplierdata) 
  { 
    this.editStatus=1;
    this.success = {summary:''};
    this.supplierData = supplierdata;
    let sufficient = supplierdata.sufficient=== null ?'':supplierdata.sufficient;
    this.supplierform.patchValue({
      supplier_name:supplierdata.supplier_name,
      //validity:supplierdata.validity,
      products_composition:supplierdata.products_composition,     
      supplier_address:supplierdata.supplier_address,
      //available_in_gots_database:supplierdata.available_in_gots_database,
      sufficient:sufficient
    });
    this.scrollToBottom();
  }
  suppliersuccess:any;
  suppliererror:any;
  changeSufficient(content,value)
  {
    this.renderer.invokeElementMethod(this.elRef.nativeElement.ownerDocument.activeElement, 'blur');
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.service.changeSufficient({audit_id:this.audit_id,unit_id:this.unit_id,app_id:this.app_id,id:this.SupplierData.id,sufficient:value})
        .pipe(first())
        .subscribe(res => {

          if(res.status){
            this.suppliersuccess = {summary:res.message};
            this.SupplierData.sufficient = value;
            this.buttonDisable = true;
            this.loadSupplierInformation();
          }else if(res.status == 0){
            this.suppliererror = {summary:res};
          }
          this.loading['button'] = false;
          this.buttonDisable = false;
        },
        error => {
            this.suppliererror = {summary:error};
            this.loading['button'] = false;
        });
    }, (reason) => {
      //console.log('sdfsdf');
      this.model.sufficient = this.SupplierData.sufficient;
    })
  }
 
  get rf() { return this.remarkForm.controls; }

  removeSupplier(content,index:number,supplierdata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.supplierFormreset();
        this.service.deleteSupplierData({audit_id:this.audit_id,unit_id:this.unit_id,app_id:this.app_id,id:supplierdata.id})
        .pipe(first())
        .subscribe(res => {

            if(res.status){
              this.success = {summary:res.message};
              this.buttonDisable = true;
              this.loadSupplierInformation();
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
    });
  }
 
  supplierFormreset()
  {
    this.editStatus=0;
    
    this.supplierData = '';  
    this.supplierform.reset();
    
    this.supplierform.patchValue({     
      supplier_name:'',   
      supplier_address:'',  
      //validity:'',
      
      products_composition:'',
     // available_in_gots_database:'',
      sufficient:''     
    });
  }
 
  
   
  get sf() { return this.supplierform.controls; }
  
 

  scrollToBottom()
  {
	//console.log(screen.height+'---'+window.innerHeight);
    window.scroll({ 
      //top: window.innerHeight,
	  top: document.body.scrollHeight,
      left: 0, 
      behavior: 'smooth' 
    });	
  }
 

}
