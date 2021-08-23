import {DecimalPipe} from '@angular/common';
import {Directive, Component, QueryList, ViewChildren } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import {Observable} from 'rxjs';

import { ApplicationChecklist } from '@app/models/master/application-checklist';

import {ApplicationChecklistListService} from '@app/services/master/checklist/application-checklist-list.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';

import { first } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';

@Component({
  selector: 'app-list-application-checklist',
  templateUrl: './list-application-checklist.component.html',
  styleUrls: ['./list-application-checklist.component.scss'],
  providers: [ApplicationChecklistListService]
})
export class ListApplicationChecklistComponent {

  title='';
  applicationchecklists$: Observable<ApplicationChecklist[]>;
  total$: Observable<number>;
  category:number;
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
  canAddData = false;
  canEditData = false;
  canDeleteData = false;
  canViewData = false;
  canActivateData = false;
  canDeactivateData = false;
  
  constructor(private activatedRoute:ActivatedRoute,private router: Router,public service: ApplicationChecklistListService,private modalService: NgbModal,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) {
    this.applicationchecklists$ = service.applicationchecklists$;
    this.total$ = service.total$;
    
    this.modalOptions = this.errorSummary.modalOptions;
	
	  this.router.routeReuseStrategy.shouldReuseRoute = () => false;
      //console.log
    
    this.title = 'Application Checklist';
     this.authservice.currentUser.subscribe(x => {
      if(x){
        
        
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;

        if(this.userdetails.resource_access != 1)
        {
          if(this.userdetails.rules.includes('edit_application_checklist')  ){
            this.canEditData = true;
          }
          if(this.userdetails.rules.includes('delete_application_checklist') ){
            this.canDeleteData = true;
          }
          if(this.userdetails.rules.includes('add_application_checklist')  ){
            this.canAddData = true;
          }
          if(this.userdetails.rules.includes('activate_application_checklist')  ){
            this.canActivateData = true;
          }
          if(this.userdetails.rules.includes('deactivate_application_checklist')  ){
            this.canDeactivateData = true;
          }
        }
        if(this.userdetails.resource_access == 1)
        {
          this.canAddData = true;
          this.canEditData = true;
          this.canDeleteData = true;
          this.canActivateData = true;
          this.canDeactivateData = true;	
        }
        
      }else{
        this.userdecoded=null;
      }
    });
    
  }

  onSort({column, direction}: SortEvent) {
    this.headers.forEach(header => {
      if (header.sortable !== column) {
        header.direction = '';
      }
    });

    this.service.sortColumn = column;
    this.service.sortDirection = direction;
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
    
    commonModalAction(){
    let reason = this.model.action;	
    
    let actionStatus=0;
    if(reason=='activate'){		
      actionStatus=0;
    }else if(reason=='deactivate'){		
      actionStatus=1;
    }else if(reason=='delete'){		
      actionStatus=2;	
    }else{
      this.modalss.close('deactivate')
    }	
    this.commonUpdateData(actionStatus);
    }  
   
    commonUpdateData(actionStatus) {
      
    this.alertInfoMessage='Please wait. Your request is processing';
    this.service.commonActionData({id:this.model.id,status:actionStatus}).pipe(first())
      .subscribe(res => {
      this.model.id = null;
      this.model.action = null;
      this.cancelBtn=false;
      this.okBtn=false;
      
      if(res.status){
        this.alertInfoMessage='';
              this.alertSuccessMessage = res.message;
        setTimeout(()=>this.modalss.close('deactivate'),this.errorSummary.redirectTime);
        this.service.searchTerm=this.service.searchTerm;
          }else if(res.status == 0){			
        this.alertInfoMessage='';
              this.alertErrorMessage = res.message;	
        }else{
        this.alertInfoMessage='';
              this.alertErrorMessage = res.message;
      }				
      },
      error => {
      this.alertInfoMessage='';
          this.alertErrorMessage = error;
         
      });
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
