import {DecimalPipe} from '@angular/common';
import {Directive, Component, QueryList, ViewChildren } from '@angular/core';
import {Observable} from 'rxjs';
import { Router } from '@angular/router';

import { Certification } from '@app/models/certification/certification';

import {ListCertificateService} from '@app/services/certification/list-certificate.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';
import {saveAs} from 'file-saver';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';
import { Country } from '@app/services/country';
import { CountryService } from '@app/services/country.service';
import { AuthenticationService } from '@app/services/authentication.service';

@Component({
  selector: 'app-list-certificate',
  templateUrl: './list-certificate.component.html',
  styleUrls: ['./list-certificate.component.scss'],
  providers: [ListCertificateService]
})

export class ListCertificateComponent {

  invoices$: Observable<Certification[]>;
  total$: Observable<number>;
  statuslist:any=[];
  error:any;
  paginationList = PaginationList;
  commontxt = commontxt;
  standardList:Standard[];
  countryList:Country[];
  userType:number;
  userdetails:any;
  userdecoded:any;
  loading = false;

  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal, public service: ListCertificateService, private router: Router,private standardservice: StandardService,private countryservice: CountryService, private authservice:AuthenticationService) {
    this.invoices$ = service.invoices$;
    this.total$ = service.total$;   
    this.router.routeReuseStrategy.shouldReuseRoute = () => false;
	
	  this.standardservice.getStandard().subscribe(res => {
		  this.standardList = res['standards'];     
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
	
	this.service.CertificateStatus().subscribe(data=>{
      this.statuslist  = data.status;
    });
  }
  
  modalss:any;
  open(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  
  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  DownloadFile(val,filename)
  {
    this.loading  = true;
    this.service.downloadFile({id:val})
     .pipe(first())
     .subscribe(res => {
      this.loading = false;
      this.modalss.close();
      saveAs(new Blob([res],{type:'application/pdf'}),filename);
    },
    error => {
      this.error = error;
      this.loading = false;
      this.modalss.close();
    });
  }
  /*
  downloadCertificationFile(invoicecode,invoice_id,offer_id){
    this.service.downloadCertificationFile({invoice_id,offer_id})
    .subscribe(res => {
        this.modalss.close();
        saveAs(new Blob([res],{type:'application/pdf'}),'invoice_'+invoicecode+'.pdf');
    });
  }
  */


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
  
  getSelectedCountryValue(val)
  {
    return this.countryList.find(x=> x.id==val).name;    
  }

}
