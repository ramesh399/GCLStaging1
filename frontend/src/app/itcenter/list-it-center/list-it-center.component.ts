import { Component, OnInit } from '@angular/core';


import {DecimalPipe} from '@angular/common';
import {Directive, QueryList, ViewChildren } from '@angular/core';
import {Observable} from 'rxjs';


 

import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { first } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { IssueService } from '@app/services/itcenter/issue.service';
import { Issues } from '@app/models/itcenter/issues';
import { ActivatedRoute, Router } from '@angular/router';
 

export interface IssuesData {
  issuetype: string;
  sno: number;
  ticketnumber	: number;
  Status:string;
  category: string;
  createddate: any;
  createdfrom: string;
  createdname:string;
  contactno: number;
  priority: number;
}
const ISSUE_DATA: IssuesData[] = [
  {sno: 1, issuetype: 'IT issues',category: 'IT',Status:'system not working	'	, ticketnumber: 100179 ,createddate:'Feb 12,2021' , createdname:'Aadhav', createdfrom:'aadhav tex', contactno: 9958374715, priority:1},
  {sno: 2, issuetype: 'audit  issues	',category: 'audit' , Status:'audit data pending'	,ticketnumber: 100521 ,createddate:'Feb 12,2021' , createdname:'Mithran', createdfrom:'Mithran tex', contactno: 9958374715, priority:1},
  {sno: 3, issuetype: 'certificate issues	',category: 'process fault',Status:'system fault	'	, ticketnumber: 100258 ,createddate:'Feb 12,2021' , createdname:'Arun', createdfrom:'Arun texmill', contactno: 9958374715, priority:1},
  {sno: 4, issuetype: 'document  issues	',category: 'doc fail', Status:'system not working	'	,ticketnumber: 100991 ,createddate:'Feb 12,2021' , createdname:'Anand', createdfrom:'	Anand texmill ', contactno:9958374715, priority:1},
  {sno: 5, issuetype: 'IT issues',category: 'IT',Status:'system not working	'	, ticketnumber: 100149 ,createddate:'Feb 12,2021' , createdname:'Sai', createdfrom:'Sai cloth', contactno: 9958374715, priority:1},
  ]
@Component({
  selector: 'app-list-it-center',
  templateUrl: './list-it-center.component.html',
  styleUrls: ['./list-it-center.component.scss']
})
export class ListItCenterComponent implements OnInit {
 IssueHead: string[] = ['sno', 'issuetype','category','Status'	, 'ticketnumber','createddate' , 'createdname' ,'createdfrom', 'contactno', 'priority'];
  //columnsToDisplay: any[] = this.displayedColumns.slice();
  dataSource = ISSUE_DATA;
  issue$: Observable<any[]>;
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
  lists: any;
  viewDetails: any;

  constructor(public service: IssueService,private modalService: NgbModal,
    private router: Router,
    private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) {
   
    this.issue$ = service.issue$;
    this.total$ = service.total$;
    this.modalOptions = this.errorSummary.modalOptions;
    this.service.searchTerm = ''
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

  moveToAdd () {
    this.service.editData = null;
    this.router.navigate(['/itcenter/add-it-center']);
  }
  ngOnInit(): void {
  //  throw new Error('Method not implemented.');
 
  }
  deleteIssuse (id) {
    this.service.deleteIssue(id).pipe(first()).subscribe(el => {
      this.service.searchTerm = ""
    })
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

  showDetails(details) {
     
    this.viewDetails = details
  }

  editDetails (details) {
    this.service.editData = details;
    this.router.navigate(['/itcenter/add-it-center']);
  }

  open(content,action,id) {
    this.model.id = id;	
    this.model.action = action;	
    this.resetBtn();
    if(action=='activate'){		
       this.alertInfoMessage='Are you sure, do you want to activate?';
    }else if(action=='deactivate'){		
       this.alertInfoMessage='Are you sure, do you want to deactivate?';
    }else if(action=='delete'){		
       this.alertInfoMessage='Are you sure, do you want to delete?';	
    }
    
    this.modalss = this.modalService.open(content, this.modalOptions);
      //this.modalService.open(content, this.modalOptions).result.then((result) => {
    
    this.modalss.result.then((result) => {	
        this.closeResult = `Closed with: ${result}`;	 
      }, (reason) => {
      this.model.id = null;  
      this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;	  
      });
    }
    
    private getDismissReason(reason: any): string {
      if (reason === ModalDismissReasons.ESC) {
        return 'by pressing ESC';
      } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
        return 'by clicking on a backdrop';
      } else {
        return  `with: ${reason}`;
      }
    }
    
     
   
     
    
    resetBtn()
    {
    this.alertInfoMessage='';
    this.alertSuccessMessage='';
    this.alertErrorMessage='';
    this.cancelBtn=true;
    this.okBtn=true;
    }

}