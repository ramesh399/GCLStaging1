import { Component, OnInit } from '@angular/core';

import { ActivatedRoute ,Params, Router } from '@angular/router';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { Application } from '@app/models/application/application';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';
import { AuthenticationService } from '@app/services';
import {Observable,Subject} from 'rxjs';
import { first, debounceTime, distinctUntilChanged, map,tap } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {saveAs} from 'file-saver';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import { NgForm } from '@angular/forms';
//import { AppdetailComponent } from '../../appdetail/appdetail.component';

@Component({
  selector: 'app-view',
  templateUrl: './view.component.html',
  styleUrls: ['./view.component.scss']
})
export class ViewComponent implements OnInit {
	
	constructor(private userservice: UserService,private activatedRoute:ActivatedRoute,
		private applicationDetail:ApplicationDetailService, private modalService: NgbModal,
		private enquiryDetail:EnquiryDetailService,private router:Router,private authservice:AuthenticationService,private errorSummary: ErrorSummaryService) { 
    }
	
  userdecoded:any;
  bsector_group_id=[];
  id:number;
  error:any;
  success:any;
  loading = false;
  applicationdata:Application;
  panelOpenState = true;
  approvalStatusList = [];//[{id:'1',name:'Accept'},{id:'2',name:'Reject'}];
  userList:User[];
  modalss:any;
  sel_user_error='';
  user_id_error='';
  approver_user_id = '';
  
  model:any = {user_id:'',approver_user_id:'',status:'',comment:'',reject_comment:''};

  userType:number;
  userdetails:any;
  arrEnumStatus:any[];
  revieweruserList:User[];
  approveruserList:User[];
  sectorlist:any=[];
  bsectorgpUpdate = new Subject<any>();

  ngOnInit() {
    
    this.id = this.activatedRoute.snapshot.queryParams.id;
    /*
    this.userservice.getAllUser({type:1}).pipe(first())
    .subscribe(res => {
      this.userList = res.users;
    },
    error => {
        this.error = {summary:error};
    });
    */
    this.bsectorgpUpdate.pipe(
			debounceTime(700),
			distinctUntilChanged())
		.subscribe(value => {
      this.loading  = true;
      this.enquiryDetail.getSectorgpUserList(value)
       .pipe(first())
       .subscribe(res => {
            let unit_id = value.unit_id;
            let bsector = value.business_sector_id;
           // console.log(unit_id+'=='+bsector);
           if(res.status==1){
              let indexv = this.sectorlist.findIndex(x=>x.unit_id == unit_id && x.bsector==bsector);
              if(indexv !== -1){
                this.sectorlist[indexv] = { unit_id:unit_id,bsector:bsector,data: res['data']};
              }else{
                this.sectorlist.push({ unit_id:unit_id,bsector:bsector,data: res['data']});
              }
            }else if(res.status == 0){
              this.error = {summary:res.message};
            }else{
              this.error = {summary:res};
            }
            this.loading = false;
          
       },
       error => {
           this.error = {summary:error};
           this.loading = false;
       });
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
    
    let dataobj:any = {id:this.id,actiontype:'view'};
    this.applicationDetail.getApplicationDetails(dataobj)
    .pipe(
      
      tap(res=>{
        let franchise_id = res.franchise_id;
        this.userservice.getAppApprover({franchise_id:franchise_id}).pipe(first())
        .subscribe(resapprovers => {
          this.approveruserList = resapprovers['data'];
        },
        error => {
            this.error = {summary:error};
        });
        this.userservice.getAppReviewer({franchise_id:franchise_id}).pipe(first())
        .subscribe(resreviewers => {
          this.revieweruserList = resreviewers['data'];
        },
        error => {
            this.error = {summary:error};
        });
      },
      first())
    ).subscribe(res => {
      this.applicationdata = res;
      this.approvalStatusList = res['approvalStatusList'];
      this.arrEnumStatus = res['arrEnumStatus'];
      /*if(this.applicationdata.status == this.arrEnumStatus['submitted']){
        this.panelOpenState = true;
      }
       this.panelOpenState = true; */
      if(this.applicationdata.app_status==this.arrEnumStatus['submitted']){
        this.applicationdata.units.forEach((val)=>{
          let bsectorsselgroup = val['bsectorsselgroup'];
          if(bsectorsselgroup && bsectorsselgroup.length>0){
            bsectorsselgroup.forEach((element,key) => {
              this.bsector_group_id['qtd_'+element.unit_id+'_'+element.sector_id]=element.business_sector_group_ids;

              this.getbusinessgpusers(element.business_sector_group_ids,element.unit_id,element.sector_id);
            });
          }
          
        });
      }
      
      

      //bsectorsselgroup
      //console.log(this.applicationdata);
      this.model.approver_user_id = res.approverid ? res.approverid: '';
      this.model.user_id = res.reviewerid ? res.reviewerid: '';
    },
    error => {
        this.error = {summary:error};
        this.loading = false;
    });  
  } 
  
  status_error = false;
  comment_error =false;
  reject_comment_error =false;
  checkUserSel(user_type='',action=''){
    

    
    if(user_type =='ospreject'){
      this.reject_comment_error =false;
      if(this.model.reject_comment ==''){
        this.reject_comment_error =true;
      }else{
        this.modalss.close('ospreject');
      }
    }else if(user_type =='approver'){
      if(this.model.approver_user_id ==''){
        this.user_id_error ='true';
      }else{
        this.modalss.close('AssignApprover'+action);
      }
    }else if(user_type =='statusapproval'){
      if(this.model.status ==''){
        this.status_error =true;
      }else{
        this.status_error =false;
      }
      /*
      if(this.model.comment.trim() ==''){
        this.comment_error =true;
      }else{
        this.comment_error =false;
      }
      && this.model.comment.trim()!=''
      */
      if(this.model.status !='' ){
      
        this.modalss.close('StatusApproval');
      }
      
    }else if(user_type =='selfreviewer'){
      this.modalss.close('selfreviewer');
    }else if(user_type =='selfapprover'){
      this.modalss.close('selfapprover');
    }else{
      if(this.model.user_id ==''){
        this.user_id_error ='true';
      }else{
        this.modalss.close('Save'+action);
      }
    }
  }
  

  getbusinessgpusers(bsectorgp_id,unit_id,bsector){
    let value:any = {franchise_id:this.applicationdata.franchise_id,bsectorgp_id,unit_id,business_sector_id:bsector};

    this.enquiryDetail.getSectorgpUserList(value)
       .pipe(first())
       .subscribe(res => {
            let unit_id = value.unit_id;
            let bsector = value.business_sector_id;
           // console.log(unit_id+'=='+bsector);
           if(res.status==1){
              let indexv = this.sectorlist.findIndex(x=>x.unit_id == unit_id && x.bsector==bsector);
              if(indexv !== -1){
                this.sectorlist[indexv] = { unit_id:unit_id,bsector:bsector,data: res['data']};
              }else{
                this.sectorlist.push({ unit_id:unit_id,bsector:bsector,data: res['data']});
              }
            }else if(res.status == 0){
              this.error = {summary:res.message};
            }else{
              this.error = {summary:res};
            }
            this.loading = false;
          
       },
       error => {
           this.error = {summary:error};
           this.loading = false;
       });
  }


  downloadCompanyFile(filename){
    this.enquiryDetail.downloadCompanyFile({id:this.id})
    .subscribe(res => {
      
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
      this.modalss.close('');
    });
  }

  downloadChecklistFile(fileid,filename){
    this.enquiryDetail.downloadChecklistFile({id:fileid})
    .subscribe(res => {
      
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
      this.modalss.close('');
    });
  }

  downloadcertificateFile(fileid,filename){
    this.enquiryDetail.downloadcertificateFile({id:fileid})
    .subscribe(res => {
      
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
      this.modalss.close('');
    });
  }

  downloadFile(fileid,filename){
    this.enquiryDetail.downloadFile({id:fileid})
    .subscribe(res => {
      
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
      this.modalss.close('');
    });
  }


  openmodal(content) {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  editRedirect(id){
    if(this.applicationdata.audit_type == 5){
      this.router.navigateByUrl('/change-scope/unit-addition/add?id='+this.applicationdata.addition_id+'&app='+this.applicationdata.parent_app_id+'&new_app_id='+this.applicationdata.id);
    }else if(this.applicationdata.audit_type == 3){
      this.router.navigateByUrl('/change-scope/process-addition/add?id='+this.applicationdata.addition_id+'&app='+this.applicationdata.parent_app_id+'&new_app_id='+this.applicationdata.id);
    }else if(this.applicationdata.audit_type == 4){
      this.router.navigateByUrl(`/application/edit-request?id=${id}&app_id=${this.applicationdata.parent_app_id}&standard_addition_id=${this.applicationdata.addition_id}`);
    }else{
      this.router.navigateByUrl('/application/edit-request?id='+id+'&type=edit');
    }
    
  }
  logForm(val){
    //console.log(JSON.stringify(this.model));
  }
  //onSubmit(f:NgForm,actiontype) {
    appform : any = {};
    bsectrogpData:any = [];
  open(content,type='',f:NgForm) {
    
    if(type =='submitforreview'){
      this.bsectrogpData = [];
      let unitsErr:any = '';
      //let bsectrogpData:any = [];
      this.applicationdata.units.forEach((val)=>{
        let sectorErr = [];
        for (const [sectorid, sectorname] of Object.entries(val.bsectorsdetails)) {

         
          //bsector_group_id['qtd'+
          //this.form.control.get("user_id").value;
          //console.log(`${key} ${value}`);
          let qid = val.id+'_'+sectorid;
          //console.log(qid);
          let selectedgroup = eval("f.value.qtd_"+qid);
          //console.log('sectorid:'+selectedgroup);
          //console.log(selectedgroup);
          if(selectedgroup !== undefined && selectedgroup.length>0){
            selectedgroup.forEach(gpid=>{
              let sectov = this.sectorlist.find(x=>x.unit_id ==  val.id && x.bsector==sectorid);
              //console.log(sectov);
              if(sectov !==undefined){
                let gpusers = sectov['data'].find(x=>x.id ==  gpid);
                if(gpusers !== undefined && !gpusers.usersfound){
                  unitsErr = unitsErr +'<li>There are no users for '+sectorname+' in '+val.name+':</li>';
                }

              }
            })
            
          }


          //console.log(selectedgroup);
          if(selectedgroup === undefined || selectedgroup == ''){
            sectorErr.push(sectorname);
          }else{
            this.bsectrogpData.push({sectorid:sectorid,unit_id:val.id,sectorgroup_ids:selectedgroup});
          }
          //this.error = {summary:res.message};

        }
        if(sectorErr.length>0){
          unitsErr = unitsErr +'<li>Please add Business Sector Group for '+sectorErr.join(', ') +' in '+val.name+':</li>';
        }
        
      })
      if(unitsErr!=''){
        this.bsectrogpData = [];
        this.error = {summary:'<ul>'+unitsErr+'</ul>'};
        return false;
      }
      
    }
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    
    this.modalss.result.then((result) => {
      this.user_id_error ='';
      this.status_error =false;
      this.comment_error =false;

      if(result =='Save'){
        this.addReviewer('');
      }else if(result =='AssignApprover'){
        this.assignApprover('');
      }else if(result =='StatusApproval'){
        this.approveApplication();
      }else if(result =='Savechange'){
        this.addReviewer('change');
      }else if(result =='AssignApproverchange'){
        this.assignApprover('change');
      }else if(result =='selfreviewer'){
        this.addReviewer('self');
      }else if(result =='selfapprover'){
        this.assignApprover('self');
      }else if(result =='submitforreview'){
        this.submitForReview();
      }else if(result =='ospreject'){
        //this.submitForReview();
        this.rejectApplication();
      }
      
      

      
      
    }, (reason) => {
      this.user_id_error ='';
      this.status_error =false;
      this.comment_error =false;
      this.model.reject_comment ='';
      this.reject_comment_error =false;
      //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
    });
  }


  rejectApplication(){
    this.loading  = true;
    this.enquiryDetail.osprejectApp({app_id:this.id,reject_comment:this.model.reject_comment})
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            this.applicationdata.status = res.app_status_name;
            this.applicationdata.app_status = res.app_status;
            this.success = {summary:res.message};
            //if(this.model.status==4){
              setTimeout(()=>this.router.navigate(['/application/list']),this.errorSummary.redirectTime);
            //}
          }else if(res.status == 0){
            this.error = {summary:res.message};
          }else{
            this.error = {summary:res};
          }
          this.loading = false;
        
     },
     error => {
         this.error = {summary:error};
         this.loading = false;
     });
  }
  submitForReview(){
    //console.log({app_id:this.id,user_id:this.model.comment,status:this.model.status});
    
    this.loading  = true;
    this.enquiryDetail.submitAppForReview({app_id:this.id,bsectrogpData:this.bsectrogpData})
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
           
            this.applicationdata.status = res.app_status_name;
            this.applicationdata.app_status = res.app_status;
            this.success = {summary:res.message};
            if(this.model.status==4){
              setTimeout(()=>this.router.navigate(['/application/list']),this.errorSummary.redirectTime);
            }else{
              setTimeout(()=> this.loadApplication(),this.errorSummary.redirectTime);
            }
          }else if(res.status == 0){
            this.error = {summary:res.message};
          }else{
            this.error = {summary:res};
          }
          this.loading = false;
        
     },
     error => {
         this.error = {summary:error};
         this.loading = false;
     });
  }

  apploading:any;
  loadApplication(){
    this.apploading = true;
    this.success = '';
    let dataobj:any = {id:this.id,actiontype:'view'};
    this.applicationDetail.getApplicationDetails(dataobj)
    .pipe(
      first()
    ).subscribe(res => {
      this.applicationdata = res;
      this.apploading = false;
    })
  }


  approveApplication(){
    //console.log({app_id:this.id,user_id:this.model.comment,status:this.model.status});
    
    this.loading  = true;
    this.enquiryDetail.approveApplication({app_id:this.id,comment:this.model.comment,status:this.model.status})
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            this.applicationdata.status = res.app_status_name;
            this.applicationdata.app_status = res.app_status;
            this.success = {summary:res.message};
            //if(this.model.status==4){
            setTimeout(()=>{ 
              this.router.navigate(['/application/list']);
              this.loading = false;
            },this.errorSummary.redirectTime);
            //}
          }else if(res.status == 0){
            this.error = {summary:res.message};
            this.loading = false;
          }else{
            this.error = {summary:res};
            this.loading = false;
          }
          
        
     },
     error => {
         this.error = {summary:error};
         this.loading = false;
     });
  }
  filtersectorlist(unit_id,bsector){
   // console.log(unit_id+'++'+bsector);
    let sectov = this.sectorlist.find(x=>x.unit_id == unit_id && x.bsector==bsector);
    if(sectov !== undefined){
      return sectov['data'];
    }
    return [];
  }
  getBusinessSectorgpUsers(bsectorgp_id,unit_id,bsector){

    this.bsectorgpUpdate.next({franchise_id:this.applicationdata.franchise_id,bsectorgp_id,unit_id,business_sector_id:bsector});
    //bsectorgpUpdate
   // console.log({franchise_id:this.applicationdata.franchise_id,bsectorgp_id,unit_id,business_sector_id:bsector});
   /*
    this.loading  = true;
    this.enquiryDetail.getSectorgpUserList({franchise_id:this.applicationdata.franchise_id,bsectorgp_id,unit_id,business_sector_id:bsector})
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            let indexv = this.sectorlist.findIndex(x=>x.unit_id == unit_id && x.bsector==bsector);
            if(indexv !== -1){
              this.sectorlist[indexv] = { unit_id:unit_id,bsector:bsector,data: res['data']};
            }else{
              this.sectorlist.push({ unit_id:unit_id,bsector:bsector,data: res['data']});
            }
           // console.log(this.sectorlist);
           // console.log(unit_id+'=='+bsector);
            //this.applicationdata.status = res.app_status_name;
            //this.applicationdata.status = res.app_status_name;
           // this.applicationdata.app_status = res.app_status;
           // this.applicationdata.hasapprover = res.hasapprover;
           // this.success = {summary:res.message};
            
          }else if(res.status == 0){
            this.error = {summary:res.message};
          }else{
            this.error = {summary:res};
          }
          this.loading = false;
        
     },
     error => {
         this.error = {summary:error};
         this.loading = false;
     });
     */
  }
  assignApprover(action){
    //console.log({app_id:this.id,user_id:this.model.approver_user_id});
    //return false;
    this.loading  = true;
    this.enquiryDetail.assignApprover({actiontype:action,app_id:this.id,user_id:this.model.approver_user_id})
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //this.enquirydata.status = res.enquirystatus;
            //this.enquirydata.status_updated_date = res.status_updated_date;
            //this.enquirydata.status_updated_by = res.status_update_by;
            this.applicationdata.status = res.app_status_name;
            this.applicationdata.app_status = res.app_status;
            this.applicationdata.hasapprover = res.hasapprover;
            this.success = {summary:res.message};
            
          }else if(res.status == 0){
            this.error = {summary:res.message};
          }else{
            this.error = {summary:res};
          }
          this.loading = false;
        
     },
     error => {
         this.error = {summary:error};
         this.loading = false;
     });
  }

  addReviewer(action){
    //console.log(action);
      //return false;
    this.loading  = true;
    this.enquiryDetail.assignReviewer({actiontype:action,app_id:this.id,user_id:this.model.user_id})
     .pipe(first())
     .subscribe(res => {
           
         if(res.status){
            //this.enquirydata.status = res.enquirystatus;
            //this.enquirydata.status_updated_date = res.status_updated_date;
            //this.enquirydata.status_updated_by = res.status_update_by;
            this.applicationdata.showApplicationReview = 1;
            this.applicationdata.status = res.app_status_name;
            this.applicationdata.app_status = res.app_status;
            this.applicationdata.hasapprover = res.hasapprover;
            this.success = {summary:res.message};

            
          }else if(res.status == 0){
            this.error = {summary:res.message};
          }else{
            this.error = {summary:res};
          }
          this.loading = false;
        
     },
     error => {
         this.error = {summary:error};
         this.loading = false;
     });
  }
    
  editProduct(content) {	
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title',centered: true});
  }

}

