import { DecimalPipe } from '@angular/common';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { Directive, Component, QueryList, ViewChildren } from '@angular/core';
import { Observable } from 'rxjs';


import { Checklist } from '@app/models/master/checklist';

import {ChecklistListService} from '@app/services/master/checklist/checklist-list.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';

import { first } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';


@Component({
  selector: 'app-list-checklist',
  templateUrl: './list-checklist.component.html',
  styleUrls: ['./list-checklist.component.scss'],
  providers: [ChecklistListService, DecimalPipe]
})
export class ListChecklistComponent{

  checklists$: Observable<Checklist[]>;
  total$: Observable<number>;
  category:number;
  //sno:number;
  title = '';
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

  constructor(private activatedRoute:ActivatedRoute,public service: ChecklistListService, private router: Router,private modalService: NgbModal,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) {
    this.checklists$ = service.checklists$;
    this.total$ = service.total$;

    this.modalOptions = this.errorSummary.modalOptions;	
    
    this.router.routeReuseStrategy.shouldReuseRoute = () => false;
      //console.log
    this.category = this.activatedRoute.snapshot.queryParams.category;
    let conditionname = '';
    if(this.category == 2){
      this.title = 'Application Unit Review Checklist';
      conditionname = 'app_unit_review_checklist';
    }else{
      this.title = 'Application Review Checklist';
       conditionname = 'app_review_checklist';
    }

    this.authservice.currentUser.subscribe(x => {
      if(x){
        
         
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        

        if(this.userType==1 && this.userdetails.resource_access==1){
          this.canAddData = true;
          this.canEditData = true;
          this.canDeleteData = true;
          this.canViewData = true;
          this.canActivateData = true;
          this.canDeactivateData = true;
        }else if(this.userType==1){
          
            if(this.userdetails.rules.includes('add_'+conditionname))
            {
              this.canAddData = true;
            }
            
            if(this.userdetails.rules.includes('edit_'+conditionname))
            {
              this.canEditData = true;
            }
            
            if(this.userdetails.rules.includes('view_'+conditionname))
            {
              this.canViewData = true;
            }     
            
            if(this.userdetails.rules.includes('delete_'+conditionname))
            {
              this.canDeleteData = true;
            }
            if(this.userdetails.rules.includes('activate_'+conditionname))
            {
              this.canActivateData = true;
            }
            if(this.userdetails.rules.includes('deactivate_'+conditionname))
            {
              this.canDeactivateData = true;
            }
           
          
        }
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

