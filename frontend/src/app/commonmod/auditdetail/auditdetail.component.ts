import { Component, OnInit, Input } from '@angular/core';
import { AuditPlan } from '@app/models/audit/audit-plan';
import { AuditPlanService } from '@app/services/audit/audit-plan.service';
import { AuditExecutionService } from '@app/services/audit/audit-execution.service';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';
import { AuthenticationService } from '@app/services';

@Component({
  selector: 'app-auditdetail',
  templateUrl: './auditdetail.component.html',
  styleUrls: ['./auditdetail.component.scss']
})
export class AuditdetailComponent implements OnInit {

  //@Input() auditPlanData:AuditPlan;
  @Input() userdecoded: any;
  @Input() id:number;

  auditPlanData:AuditPlan;
  audit_plan_id:any;
  
  panelOpenState = false;
  childmodel:any = {user_bsector_group_id:''};
  detailForm : any = {};
  planloading:any;
  error:any;
  userType:number;
  userdetails:any;
  
  reviewerhistroy: any = [];
  subTopics: any = [];
  subtopicna: any;
  lastIndex: number;
  stage_val: any;
  stage_arr: any = [];
  questionsStd: any =[];
  reviewernotes: any;

  constructor(private modalService: NgbModal, public AuditExecutionService:AuditExecutionService,private auditplanservice:AuditPlanService,private authservice:AuthenticationService) { }

  modalss:any;
  open(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  ngOnInit() {
    
    this.authservice.currentUser.subscribe(x => {
      if(x){
        
         
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        
      }else{
        
      }
    });
     

    this.planloading = true;
    this.auditplanservice.getAuditPlanDetails(this.id).pipe(first())
    .subscribe(res => {
      this.auditPlanData = res;
      this.audit_plan_id = res.id;
      this.planloading = false;
    },
    error => {
        this.error = {summary:error};
        this.planloading = false;
    });

    this.AuditExecutionService.getReviewerHistroy(this.id).subscribe(async res => {
      if (res.status) {
       this.reviewernotes = res.notesdata;
        this.reviewerhistroy = await res.reviewerhistroy;
        this.reviewerhistroy.forEach(ele =>{
          ele.standard_label = '';
        });

        this.AuditExecutionService.getQuestionStandards().subscribe(async res =>{
          this.questionsStd = await res.auditExecutionChecklists;

          for(var i=0; i<this.reviewerhistroy.length; i++){
            for(var j=0; j<this.questionsStd.length; j++){
              if((this.reviewerhistroy[i].questions.localeCompare(this.questionsStd[j].name))==0){
                this.reviewerhistroy[i].standard_label = await this.questionsStd[j].standard_label;
              }
            }
          }
         });

        

        console.log(this.reviewerhistroy);

        this.lastIndex = this.reviewerhistroy.length - 1;
        this.stage_val = this.reviewerhistroy[this.lastIndex].review_stage;
        //console.log(this.stage_val);
        for (var i = 0; i <= this.stage_val; i++) {
          this.stage_arr[i] = i;
        }
      }
    });

  }
}
