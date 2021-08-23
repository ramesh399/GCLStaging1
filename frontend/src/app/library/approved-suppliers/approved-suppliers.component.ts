import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { ApprovedSupplier } from '@app/models/library/approvedSuppliers';
import { Country } from '@app/services/country';
import { ApprovedSupplierService } from '@app/services/library/approved-suppliers/approved-suppliers.service';
import { CountryService } from '@app/services/country.service';
import { User } from '@app/models/master/user';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-approved-suppliers',
  templateUrl: './approved-suppliers.component.html',
  styleUrls: ['./approved-suppliers.component.scss'],
  providers: [ApprovedSupplierService]
})
export class ApprovedSuppliersComponent implements OnInit {

  title = 'Approved Suppliers'; 
  form : FormGroup; 
  approvedsuppliers$: Observable<ApprovedSupplier[]>;
  total$: Observable<number>;
  id:number;
  approvedsupplierData:any;
  ApprovedsupplierData:any;
  error:any;
  success:any;
  buttonDisable = false;
  model: any = {franchise_id:null};
  franchiseList:User[];
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  approvedsupplierEntries:any=[];
  modalss:any;
  statuslist:any;
  loading:any=[];
  countryList:Country[];
  supplier_file:any;
  supplierFileErr ='';
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;
  

  constructor(private modalService: NgbModal, private countryservice: CountryService, private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: ApprovedSupplierService, public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) 
  {
    this.approvedsuppliers$ = service.approvedsuppliers$;
    this.total$ = service.total$;
  }

  ngOnInit() 
  {
    this.form = this.fb.group({	
      country_id:['',[Validators.required]],  
      supplier_name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      address:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      contact_person:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      email:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.email,Validators.maxLength(255)]],
      phone:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.pattern("^[0-9\-\+]*$"), Validators.minLength(8), Validators.maxLength(15)]],
      accreditation:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      certificate_no:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      scope_of_accreditation:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      accreditation_expiry_date:['',[Validators.required]],
      supplier_file:[''],
      status:['',[Validators.required]],  
    });

    this.countryservice.getCountry().subscribe(res => {
      this.countryList = res['countries'];
    });

    this.service.getStatusList().pipe(first())
    .subscribe(res => {
      //this.gislogEntries = res.gislogs;    
      this.statuslist  = res.statuslist;
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

  get f() { return this.form.controls; }

  approvedsupplierListEntries = [];
  approvedsupplierIndex:number=null;
  addapprovedsupplier()
  {
    this.f.country_id.markAsTouched();
    this.f.supplier_name.markAsTouched();
    this.f.address.markAsTouched();
    this.f.contact_person.markAsTouched();
    this.f.email.markAsTouched();
    this.f.phone.markAsTouched();
    this.f.accreditation.markAsTouched();
    this.f.certificate_no.markAsTouched();
    this.f.scope_of_accreditation.markAsTouched();
    this.f.accreditation_expiry_date.markAsTouched();
    this.f.status.markAsTouched();

    this.supplierFileErr = '';
    if(this.supplier_file == '' || this.supplier_file === undefined){
      this.supplierFileErr = 'Please upload file';
      return false;
    }

    if(this.form.valid && this.supplierFileErr =='')
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let country_id = this.form.get('country_id').value;
      let supplier_name = this.form.get('supplier_name').value;
      let address = this.form.get('address').value;
      let contact_person = this.form.get('contact_person').value;
      let email = this.form.get('email').value;
      let phone = this.form.get('phone').value;
      let accreditation = this.form.get('accreditation').value;
      let certificate_no = this.form.get('certificate_no').value;
      let scope_of_accreditation = this.form.get('scope_of_accreditation').value;
      let accreditation_expiry_date = this.errorSummary.displayDateFormat(this.form.get('accreditation_expiry_date').value);
      let status = this.form.get('status').value;

      let expobject:any={country_id:country_id,supplier_name:supplier_name,address:address,contact_person:contact_person,email:email,phone:phone,accreditation:accreditation,certificate_no:certificate_no,scope_of_accreditation:scope_of_accreditation,accreditation_expiry_date:accreditation_expiry_date,status:status};
      
      if(1)
      {
        if(this.approvedsupplierData){
          expobject.id = this.approvedsupplierData.id;
          expobject.supplier_file = this.approvedsupplierData.supplier_file;
        }
        
        this.formData.append('formvalues',JSON.stringify(expobject));
        this.service.addData(this.formData)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              
              this.service.customSearch();
              this.supplierFormreset();
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

  viewApprovedSupplier(content,data)
  {
    this.ApprovedsupplierData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  editStatus=0;
  editApprovedSupplier(index:number,approvedsupplierdata) 
  { 
    this.supplierFileErr=''; 
	this.editStatus=1;
    this.formData = new FormData(); 
    this.approvedsupplierData = approvedsupplierdata;
    this.supplier_file = approvedsupplierdata.supplier_file;
    
    this.form.patchValue({
      country_id:approvedsupplierdata.country_id,
      supplier_name:approvedsupplierdata.supplier_name,     
      address:approvedsupplierdata.address,
      contact_person:approvedsupplierdata.contact_person,
      email:approvedsupplierdata.email,
      phone:approvedsupplierdata.phone,
      accreditation:approvedsupplierdata.accreditation,
      scope_of_accreditation:approvedsupplierdata.scope_of_accreditation,
      certificate_no:approvedsupplierdata.certificate_no,
      accreditation_expiry_date:this.errorSummary.editDateFormat(approvedsupplierdata.accreditation_expiry_date),
      status:approvedsupplierdata.status
    });
    this.scrollToBottom();
  }
  scrollToBottom()
  {
    window.scroll({ 
      top: window.innerHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }


  removeSupplier(content,index:number,approvedsupplierdata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.resetapprovedsupplier();
        this.service.deleteApprovedSupplierData({id:approvedsupplierdata.id})
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

  supplierFileChange(element) 
  {
    let files = element.target.files;
    this.supplierFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("supplier_file", files[0], files[0].name);
      this.supplier_file = files[0].name;
      
    }else{
      this.supplierFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }

  removeSupplierFile()
  {
    this.supplier_file = '';
  }


  downloadFile(fileid='',filetype='',filename='')
  {
    this.service.downloadSupplierFile({id:fileid,filetype})
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

  resetapprovedsupplier(){
	this.editStatus=0;
	this.supplierFileErr='';
    this.form.reset();
    this.approvedsupplierData = '';
    this.formData = new FormData(); 
   
    this.supplier_file = '';
	
	this.form.patchValue({
      country_id:'',
      status:''
    });	
  }

  supplierFormreset()
  {
	this.editStatus=0;
	this.supplierFileErr='';  
    this.approvedsupplierData = '';
    this.formData = new FormData(); 
    this.supplier_file = '';
    this.form.reset();
	
	this.form.patchValue({
      country_id:'',
      status:''
    });	
  }

  removeapprovedsupplier()
  {
    this.supplier_file = '';
  }

  onSubmit(){ }
}
