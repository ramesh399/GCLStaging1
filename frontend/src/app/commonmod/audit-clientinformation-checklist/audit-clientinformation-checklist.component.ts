import { Component, OnInit, Input } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditClientinformationService } from '@app/services/audit/audit-clientinformation.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { tap,map, first } from 'rxjs/operators'; 
import {Observable} from 'rxjs';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Country } from '@app/services/country';
import { CountryService } from '@app/services/country.service';

@Component({
  selector: 'app-audit-clientinformation-checklist',
  templateUrl: './audit-clientinformation-checklist.component.html',
  styleUrls: ['./audit-clientinformation-checklist.component.scss']
})
export class AuditClientinformationChecklistComponent implements OnInit {

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
   
  categorylist:any = {};
  availablelist:any;
  sufficientlist:any;
   
  
  userType:number;
  userdetails:any;
  userdecoded:any;
  modalss:any;
  loading:any=[];
  answerArr:any;
  interviewrequirements:any;
  

  constructor(private modalService: NgbModal,private countryservice: CountryService,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: AuditClientinformationService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
  }

  reviewcommentlist=[];
  reviewcomments=[];
  generalOptions:any = [];
  countryList:Country[];
  checklist_sufficient_access:number;
  ngOnInit() 
  {
    //this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    if(!this.audit_id){
      this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    }
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
     
      

    this.service.getQuestions({audit_id:this.audit_id,unit_id:this.unit_id,app_id:this.app_id}).pipe(
      tap(xx=>{
        this.checklist_sufficient_access = xx['checklist_sufficient_access'];
        this.getAnswers()
      }),
      first())
    .subscribe(res => {    
      this.interviewrequirements = res.data;
      this.checklist_sufficient_access = res['checklist_sufficient_access'];
      if(this.reviewcomments.length<=0){
        this.interviewrequirements.forEach(val => {
          val.questions.forEach(x => {
            this.reviewcommentlist['qtd_comments'+x.id]='';
            if(this.checklist_sufficient_access){
              this.reviewcommentlist['qtd'+x.id]='';
            }
            
          });
        });
      }
      
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
  
  getAnswers(){
    this.service.getchecklistAnswer({audit_id:this.audit_id,app_id:this.app_id}).pipe(first())
    .subscribe(list => {    

        if(list['status'] && list['data'])
        {
          //console.log('s');
          this.reviewcomments = list['data'];

          this.reviewcomments.forEach(val => {
           // console.log(val.question_id);
          this.reviewcommentlist['qtd_comments'+val.question_id]=val.comment;
          if(this.checklist_sufficient_access){
            this.reviewcommentlist['qtd'+val.question_id]=val.answer;
          }
          
        });
        }
    });
  }
  
  datasaved:any=false;
  onSubmit(rf:NgForm)
  {
      this.interviewrequirements.forEach(element => {
        element.questions.forEach(qtn => {
          if(this.checklist_sufficient_access){
            //let answer = eval("rf.value.qtd"+qtn.id);
            rf.controls["qtd"+qtn.id].markAsTouched();
          }
          
          //let comment = eval("rf.value.qtd_comments"+qtn.id);
          rf.controls["qtd_comments"+qtn.id].markAsTouched();
          
        });
      });

      if (rf.valid) 
      {
        let reviewdata = [];
        this.interviewrequirements.forEach(element => {
          //,comment:f.value.qtd_comments
          element.questions.forEach(qtn => {
            let answer:any = '';
            let comment = eval("rf.value.qtd_comments"+qtn.id);
            if(this.checklist_sufficient_access){
              answer = eval("rf.value.qtd"+qtn.id);
            }
            let ans = {categoryid:element.categoryid,categoryname:element.categoryname,question:qtn.name,question_id:qtn.id,answer,comment};
            reviewdata.push(ans);
          });
        });

        let requiremnetdata={
          audit_id:this.audit_id,
          app_id:this.app_id,
          checklistdata:reviewdata
        }
        
        
        this.loading['button']  = true;
        this.service.saveChecklist(requiremnetdata)
        .pipe(first())
        .subscribe(res => {
              
            if(res.status==1){
              this.datasaved = true;
                this.success = {summary:res.message};
                this.loading['button'] = false;
                
                
              }else if(res.status == 0){
                this.error = {summary:res.message};
              }else{
                this.error = {summary:res};
              }
              this.loading['button'] = false;
            
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        });
        
      }
      else 
      {
        this.error = {summary:this.errorSummary.errorSummaryText};
      }
  
   

  }
  
  scrollToBottom()
  {
    window.scroll({ 
      top: window.innerHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }
 

}
