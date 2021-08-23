import {DecimalPipe} from '@angular/common';
import {Directive, Component, OnInit, QueryList, ViewChildren } from '@angular/core';
import {Observable} from 'rxjs';
import { Router } from '@angular/router';
import { Invoice } from '@app/models/invoice/invoice';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl, Form } from '@angular/forms';
import {CertificateGenerationListService} from '@app/services/certification/certificate-generation-list.service';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { first } from 'rxjs/operators';
import {saveAs} from 'file-saver';
import { AuthenticationService } from '@app/services/authentication.service';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';
import { Country } from '@app/services/country';
import { CountryService } from '@app/services/country.service';


@Component({
  selector: 'app-certificate-generation-list',
  templateUrl: './certificate-generation-list.component.html',
  styleUrls: ['./certificate-generation-list.component.scss'],
  providers: [CertificateGenerationListService]
})
export class CertificateGenerationListComponent implements OnInit {

  listauditplan$: Observable<Invoice[]>;
  total$: Observable<number>;
  statuslist:any=[];
  error:any;
  success:any;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;
  listdata:any=[];
  buttonDisable = false;
  formData:FormData = new FormData();
  userType:number;
  userdetails:any;
  userdecoded:any;
  auditStatus:any;
  loading = false;
  standardList:Standard[];
  form : FormGroup;
  countryList:Country[];
  constructor(public errorSummary:ErrorSummaryService,private fb:FormBuilder, public service: CertificateGenerationListService, private modalService: NgbModal, private router: Router, private authservice:AuthenticationService,private standardservice: StandardService,private countryservice: CountryService) {
    this.listauditplan$ = service.listauditplan$;
    this.total$ = service.total$;   
    this.router.routeReuseStrategy.shouldReuseRoute = () => false;

    this.service.CertificateStatus().subscribe(data=>{
      this.statuslist  = data.status;
    });
	
  }
  minDate: Date;
  ngOnInit() {
    this.minDate = new Date();
    this.form = this.fb.group({
      cb_reason:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
      cb_date:['',[Validators.required]],
      cb_file:[''],      
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

    this.standardservice.getStandard().subscribe(res => {
      this.standardList = res['standards'];     
      });
    
    this.countryservice.getCountry().subscribe(res => {	
        this.countryList = res['countries'];
      });

  }

  cb_file:any;
  cb_fileChange(element) 
  {
    let files = element.target.files;
    this.cb_fileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("cb_file", files[0], files[0].name);
      this. cb_file = files[0].name;
      
    }else{
      this. cb_fileErr ='Please upload valid file';
    }
    element.target.value = '';
  }

  get f() { return this.form.controls; } 

  cb_fileErr = '';
  SaveCB()
  {
    this.f.cb_reason.markAsTouched();
    this.f.cb_date.markAsTouched();

    if(this.cb_file=='' || this.cb_file===undefined){
      this.cb_fileErr = 'Please upload file';
      return false;
    }

    if(this.form.valid && this.cb_fileErr =='')
    {
      this.buttonDisable = true;
      this.loading = true;

      let cb_reason = this.form.get('cb_reason').value;
      let cb_date = this.form.get('cb_date').value;

      let expobject:any={id:this.listdata.certificate_id,cb_reason:cb_reason,cb_date:cb_date};

      if(1)
      {
        this.formData.append('formvalues',JSON.stringify(expobject));
        this.service.addCB(this.formData)
        .pipe(first())
        .subscribe(res => {
          if(res.status==1){
            this.service.customSearch();
            this.success = {summary:res.message};
            this.formreset();
          }else if(res.status == 0){
            this.error = {summary:res.message};
          }else{
            this.error = {summary:res};
          }
          this.loading = false;
          this.modals.close();
        },
        error => {
            this.error = {summary:error};
            this.loading = false;
        });
      }
      else 
      {
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.form);   
      }   
    }

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


  modals:any;
  viewCB(content,data) 
  {
    this.formreset();
    this.listdata = data;
    this.modals = this.modalService.open(content, {size:'lg',ariaLabelledBy: 'modal-basic-title'});
  }

  modalss:any;
  openmodal(content)
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title'});
  }
  

  downloadFile(fileid,filename,type){
    this.service.downloadFile({id:fileid,type})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    
    });
  }
  
  getSelectedValue(val)
  {
    return this.standardList.find(x=> x.id==val).code;    
  }
  
  getSelectedCountryValue(val)
  {
    return this.countryList.find(x=> x.id==val).name;    
  }

  formreset()
  {
    this.form.reset();
    this.cb_file = '';
  }



  removecb_file()
  {
    this.cb_file = '';
  }


}