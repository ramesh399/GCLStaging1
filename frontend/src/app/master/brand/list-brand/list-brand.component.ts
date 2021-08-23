import { Component, OnInit, QueryList, ViewChildren } from '@angular/core';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { commontxt, NgbdSortableHeader, PaginationList, SortEvent } from '@app/helpers/sortable.directive';
import { AuthenticationService } from '@app/services/authentication.service';
import { BrandService } from '@app/services/master/brand/brand.service';
import { NgbModalOptions } from '@ng-bootstrap/ng-bootstrap';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-list-brand',
  templateUrl: './list-brand.component.html',
  styleUrls: ['./list-brand.component.scss']
})
export class ListBrandComponent implements OnInit {

  // standards$: Observable<Standard[]>;
  total$: Observable<number>;
  //sno:number;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;


  closeResult: string;
  modalOptions:NgbModalOptions;
  loading = false;
  buttonDisable = false;
  error:any;
  id:number;
  success:any;
  modalss:any;
  
  model: any = {id:null,action:null};
  alertInfoMessage:any;
  alertSuccessMessage:any;
  alertErrorMessage:any;
  cancelBtn=true;
  okBtn=true;

  userType:number;
  userdetails:any;
  userdecoded:any;

  loading$: boolean = true;
  brands: any;
  constructor(public service: BrandService,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) {
    service.getData().subscribe(res => {
      this.loading$ = false;  
      this.brands= res.data;
    });
    // console.log(this.standards$)
    ////this.total$ = service.total$;
    
    this.modalOptions = this.errorSummary.modalOptions;	

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

  onSort({column, direction}: SortEvent) {
    // resetting other headers
    //console.log('sdfsdfdsf');
    this.headers.forEach(header => {
      if (header.sortable !== column) {
        header.direction = '';
      }
    });

    // this.service.sortColumn = column;
    // this.service.sortDirection = direction;
  }
ngOnInit(){
  
}
}
