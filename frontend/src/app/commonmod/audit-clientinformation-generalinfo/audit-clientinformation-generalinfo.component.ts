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
  selector: 'app-audit-clientinformation-generalinfo',
  templateUrl: './audit-clientinformation-generalinfo.component.html',
  styleUrls: ['./audit-clientinformation-generalinfo.component.scss']
})
export class AuditClientinformationGeneralinfoComponent implements OnInit {

  @Input() app_id: number;
  @Input() cond_viewonly: any;
  @Input() audit_id: number;

  title = 'Audit Interview Employee'; 
  form : FormGroup; 
  supplierform : FormGroup;
  processform : FormGroup;
 
  id:number;
  //audit_id:number;
  
  error:any;
  success:any;
  dataloaded = false;
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
  unit_id:number;
  GenralInfoId:number=0;
  constructor(private modalService: NgbModal,private countryservice: CountryService,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: AuditClientinformationService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
  }

  reviewcommentlist=[];
  reviewcomments=[];
  generalOptions:any = [];
  countryList:Country[];
  sufficientaccess:number = 0;

  country_name:any ='';
  ngOnInit() 
  {
    if(!this.audit_id){
      this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    }
    

    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
     
     
    
    this.countryservice.getCountry().pipe(first()).subscribe(res => {
      this.countryList = res['countries'];
    });

     
    
    this.service.getGeneralInformation({audit_id:this.audit_id,app_id:this.app_id}).pipe(first())
    .subscribe(res => {   
        this.companydetails = res.data;
        if(this.companydetails.length>0){
          this.companydetails.forEach(xc=>{
            this.categorylist['generaldetail'+xc.id] = xc.value;
            this.categorylist['sufficient'+xc.id] = xc.sufficient;
            if(xc.id == '7'){
              this.country_name = xc.label_value;
            }
          });
        }
        this.sufficientaccess = res.sufficientaccess;
        this.GenralInfoId = res.GenralInfoId;
        this.generalOptions = res.sufficientOptions;

        this.dataloaded = true; 
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
  datasaved:any=false;
  onGeneralInfoSubmit(rf:NgForm)
  {
      this.companydetails.forEach(qtn => {
        
          //let generaldetail = eval("rf.value.generaldetail"+qtn.id);
          rf.controls["generaldetail"+qtn.id].markAsTouched();
          if(this.sufficientaccess){
            //let sufficient = eval("rf.value.sufficient"+qtn.id);
            rf.controls["sufficient"+qtn.id].markAsTouched();
          }
          
        
      });
      //console.log(rf.value);
      //console.log(this.categorylist['generaldetail'+7]);
      if (rf.valid) 
      {
        let reviewdata = [];
        this.companydetails.forEach(qtn => {
          //,comment:f.value.qtd_comments
          let sufficient:any = '';
          if(this.sufficientaccess){
            sufficient = eval("rf.value.sufficient"+qtn.id);
          }
          let generaldetailans:any=eval("rf.value.generaldetail"+qtn.id);
          if(qtn.id == 7){
            generaldetailans = this.categorylist['generaldetail'+7];
          }
          let ans = {question:qtn.name,question_id:qtn.id,answer:generaldetailans,sufficient};
          reviewdata.push(ans);
        });
        //console.log(reviewdata);
        //return false;
        let requiremnetdata={
          audit_id:this.audit_id,
          app_id:this.app_id,
          checklistdata:reviewdata
        }
        
        
        this.loading['button']  = true;
        this.service.saveGeneralInfoDetails(requiremnetdata)
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
