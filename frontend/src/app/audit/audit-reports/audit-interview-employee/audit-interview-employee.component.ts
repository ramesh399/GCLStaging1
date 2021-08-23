import { Component, OnInit,Input,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditReportInterviewEmployee } from '@app/models/audit/audit-interview-employee';
import { AuditReportInterviewEmployeeService } from '@app/services/audit/audit-interview-employee.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first,map } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-audit-interview-employee',
  templateUrl: './audit-interview-employee.component.html',
  styleUrls: ['./audit-interview-employee.component.scss'],
  providers: [AuditReportInterviewEmployeeService]
})
export class AuditInterviewEmployeeComponent implements OnInit {
  @Input() cond_viewonly: any;
  title = 'Audit Interview Employee'; 
  form : FormGroup;
  remarkForm : FormGroup;   
  employees$: Observable<AuditReportInterviewEmployee[]>;
  total$: Observable<number>;
  summary:any=[];
  id:number;
  audit_id:number;
  unit_id:number;
  employeeData:any;
  EmployeeData:any;
  migrantlist:any;
  genderlist:any;
  typelist:any;
  error:any;
  success:any;
  dataloaded = false;
  buttonDisable = false;
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  modalss:any;
  loading:any=[];
  answerArr:any;
  interviewrequirements:any;
  isItApplicable=true;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: AuditReportInterviewEmployeeService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
    this.employees$ = service.employees$;
    this.total$ = service.total$;
  }

  reviewcommentlist=[];
  reviewcomments=[];
  ngOnInit() 
  {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;

    this.form = this.fb.group({	
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]], 
      position:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]],
      gender:['',[Validators.required]],
      type:['',[Validators.required]],
      migrant:['',[Validators.required]],
      notes:['',[Validators.required, this.errorSummary.noWhitespaceValidator]]
    });
	
    this.remarkForm = this.fb.group({	
      remark:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]]
    });
	
    this.service.getOptionList().pipe(first())
    .subscribe(res => {    
      this.genderlist  = res.genderlist;
      this.typelist  = res.typelist;
      this.migrantlist  = res.migrantlist;
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });


    this.service.getRemarkData({audit_id:this.audit_id,unit_id:this.unit_id,type:'interview_list'}).pipe(first())
    .subscribe(res => {    
      this.dataloaded = true;
      if(res!==null)
      {  
        this.isApplicable = res.status;
        if(res.status==1)
        {
          this.isItApplicable=true; 
        }else{
          this.isItApplicable=false;
        }	 
        
        if(res.comments)
        {
          this.remarkForm.patchValue({
            'remark':res.comments
          });
        }
      }
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });



    this.service.getInterviewchecklist().pipe(first())
    .subscribe(res => {    
      this.interviewrequirements = res.requirements;
      this.answerArr = res.answer;
      this.interviewrequirements.forEach(val => {
        this.reviewcommentlist['qtd_comments'+val.id]='';
        this.reviewcommentlist['qtd'+val.id]='';
      });
    });

    this.service.getchecklistAnswer({audit_id:this.audit_id,unit_id:this.unit_id}).pipe(first())
    .subscribe(list => {    

        if(list && list['requirementcomment'])
        {
          this.reviewcomments = list['requirementcomment'];

          this.reviewcomments.forEach(val => {
          this.reviewcommentlist['qtd_comments'+val.client_information_question_id]=val.comment;
          this.reviewcommentlist['qtd'+val.client_information_question_id]=val.answer;
        });
        }
        
        
        
    });
    
    this.getSummary();

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
  
  summarydetails:any = [];
  totalDetails:any;
  sampleplan:any = [];
  sampledemployeelist:any=[];
  total_employees_interviewed:any = 0;
  getSummary(){
    this.service.getSummarydetails({audit_id:this.audit_id,unit_id:this.unit_id}).pipe(first())
    .subscribe(sdata => {    
         
      this.summarydetails = sdata['summarydetails'];
      this.totalDetails = sdata['totalDetails'];
      this.sampleplan = sdata['sampleplan'];
      this.total_employees_interviewed = sdata['total_employees_interviewed'];
      this.summarydetails.forEach(xd=>{
        //this.sampledemployeelist['qtd'+xd.id] = xd.no_of_sampled_employees;
        this.sampledemployeelist['qtd'+xd.id] = xd.total_employees;
        
      })
    });
  }
  
  get f() { return this.form.controls; }
  get rf() { return this.remarkForm.controls; }

  onSubmit(rf:NgForm)
  {
      this.interviewrequirements.forEach(element => {
        let answer = eval("rf.value.qtd"+element.id);
        let comment = eval("rf.value.qtd_comments"+element.id);
        
        rf.controls["qtd"+element.id].markAsTouched();
        rf.controls["qtd_comments"+element.id].markAsTouched();
      });

      if (rf.valid) 
      {
        let reviewdata = [];
        this.interviewrequirements.forEach(element => {
          //,comment:f.value.qtd_comments
          let ans = {question:element.name,question_id:element.id,answer:eval("rf.value.qtd"+element.id),comment:eval("rf.value.qtd_comments"+element.id)};
          reviewdata.push(ans);
        });

        let requiremnetdata={
          audit_id:this.audit_id,
          unit_id:this.unit_id,
          type:'interview_list',
          checklistdata:reviewdata
        }

        this.loading['button']  = true;
        this.service.addInterviewchecklist(requiremnetdata)
        .pipe(first())
        .subscribe(res => {
              
            if(res.status==1){
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

  employeeIndex:number=null;
  addemployee()
  {
    this.f.name.markAsTouched();
    this.f.position.markAsTouched();
    this.f.gender.markAsTouched();
    this.f.type.markAsTouched();
    this.f.migrant.markAsTouched();
    this.f.notes.markAsTouched();
   

    if(this.form.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let name = this.form.get('name').value;
      let position = this.form.get('position').value;
      let gender = this.form.get('gender').value;
      let type = this.form.get('type').value;
      let migrant = this.form.get('migrant').value;
      let notes = this.form.get('notes').value;

      let expobject:any={audit_id:this.audit_id,unit_id:this.unit_id,name:name,position:position,gender:gender,emptype:type,migrant:migrant,notes:notes,type:'interview_list'};
      
      if(1)
      {
        if(this.employeeData)
        {
          expobject.id = this.employeeData.id;
        }
        
        this.service.addData(expobject)
        .pipe(first())
        .subscribe(res => {

            this.getSummary();
            if(res.status){
              this.success = {summary:res.message};
              this.service.customSearch();
              this.employeeFormreset();
              this.remarkFormreset();
              this.buttonDisable = false;
              
            }else if(res.status == 0){
              //this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.enquiryForm)};
              this.error = {summary:res};
            }
            this.loading['button'] = false;
            this.buttonDisable = false;
        },
        error => {
            this.buttonDisable = false;
            this.error = {summary:error};
            this.loading['button'] = false;
        });
        
      } else {
        
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.form); 
        
      }   
    }
  }


  viewEmployee(content,data)
  {
    this.EmployeeData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  editStatus=0;
  editEmployee(index:number,employeedata) 
  { 
    this.editStatus=1;
    this.success = {summary:''};
    this.employeeData = employeedata;
    this.form.patchValue({
      name:employeedata.name,
      position:employeedata.position,     
      gender:employeedata.gender,
      type:employeedata.type,
      migrant:employeedata.migrant,
      notes:employeedata.notes
    });
    this.scrollToBottom();
  }


  removeEmployee(content,index:number,employeedata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.employeeFormreset();
        this.service.deleteData({id:employeedata.id})
        .pipe(first())
        .subscribe(res => {

            if(res.status){
              this.service.customSearch();
              this.success = {summary:res.message};
              this.buttonDisable = true;
            }else if(res.status == 0){
              this.error = {summary:res};
            }
            this.loading['button'] = false;
            this.buttonDisable = false;
        },
        error => {
          this.buttonDisable = false;
            this.error = {summary:error};
            this.loading['button'] = false;
        });
    }, (reason) => {
    })
    
  
  }

  employeeFormreset()
  {
    this.editStatus=0;
    
    this.employeeData = '';  
    this.form.reset();
    
    this.form.patchValue({     
      name:'',     
      position:'',
      gender:'',
      type:'',
      migrant:'',
      notes:''
    });
  }

  onSummarySubmit(f:NgForm) {
    //this.review_result_status_error = false;
    //this.status_comment_error = false;
    

    this.summarydetails.forEach(element => {
      let answer = eval("f.value.qtd"+element.id);
      f.controls["qtd"+element.id].markAsTouched();
    });
     
    
    if (f.valid) {
      let sampledemployees= [];
      let unit_review_comment= [];
      this.summarydetails.forEach(element => {
        //,comment:f.value.qtd_comments
        let ans = {id:element.id,answer:eval("f.value.qtd"+element.id)};
        sampledemployees.push(ans);
      });

      
      let reviewdata={
        audit_id:this.audit_id,
        unit_id:this.unit_id,
        sampledemployees
      }
      //console.log(unit_review_comment);
      //return false;
      this.loading['button']  = true;
      this.buttonDisable = true;
      this.service.saveSummarySample(reviewdata)
      .pipe(first())
      .subscribe(res => {
        this.getSummary();
        this.buttonDisable = false;
          if(res.status==1){
              this.success = {summary:res.message};
              
              setTimeout(() => {
                //this.router.navigateByUrl('/application/apps/view?id='+this.id);
              }, this.errorSummary.redirectTime);
              
            }else if(res.status == 0){
              this.error = {summary:res.message};
            }else{
              this.error = {summary:res};
            }
            this.loading['button'] = false;
          
      },
      error => {
          this.buttonDisable = false;
          this.error = {summary:error};
          this.loading['button'] = false;
      });
      
      
      //return false;
    } else {
      this.buttonDisable = false;
      this.loading['button'] = false;
      this.error = {summary:this.errorSummary.errorSummaryText};
      //alert(JSON.stringify(this.checklistForm.value))
      //this.error = 'Please';
    }

  }

  scrollToBottom()
  {
    window.scroll({ 
      top: document.body.scrollHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }
  
  //modalss:any;
  guidanceContent='';
  openguidance(content,type) {

    if(type=='interviewannex')
    {
      this.guidanceContent='ANNEX- Questions';
    }
    
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title',centered: true});
    this.modalss.result.then((result) => {

    }, (reason) => {
      
    });
  }
  
  isApplicable:number;
  isItApp(arg)
  {
    this.isApplicable = arg;
	  if(arg==1)
	  {
		  this.isItApplicable=true;
	  }else{
		  this.isItApplicable=false;
	  }	  
  }
  
  addRemark()
  {
    this.rf.remark.markAsTouched();

    if(this.remarkForm.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let remark = this.remarkForm.get('remark').value;

      let expobject:any={unit_id:this.unit_id,audit_id:this.audit_id,comments:remark,is_applicable:this.isApplicable,type:'interview_list'}

      this.service.addRemark(expobject)
      .pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.success = {summary:res.message};
          this.buttonDisable = false;
          this.loading['button'] = false;
          this.service.customSearch();
          this.getSummary();
        }
      },
      error => {
        this.buttonDisable = false;
          this.error = {summary:error};
          this.loading['button'] = false;
      });
    }
  }

  remarkFormreset()
  {
    this.editStatus=0;
    this.remarkForm.reset();
    
    this.remarkForm.patchValue({     
      remark:''
    });
  }
  percentEmpList:any = [];
  empPercentArr:any = [];
  empTobeSmpArr:any = [];
  getEmpPercent(sid:any){
    let sampledemp:any=0;
    this.summarydetails.forEach(x=>{
      sampledemp = sampledemp + parseFloat(this.sampledemployeelist['qtd'+x.id]);
    })
   
    this.totalDetails.total_employees = sampledemp;
    let emppercent:any = (this.sampledemployeelist['qtd'+sid]/sampledemp)*100;
    
    if(!isNaN(emppercent) && isFinite(emppercent)){
      this.empPercentArr[sid] = Math.round(emppercent);//.toFixed(2);
      
      this.getToBeSampled(sid);
      return Math.round(emppercent);//.toFixed(2);
    }else{
      this.empPercentArr[sid] = 0;
      this.getToBeSampled(sid);
      return 0;
    }    
  }

  getToBeSampled(sid:any){
    // round(($emppercent*$total_employees_interviewed)/100);
    //let sampledemp:any = this.sampledemployeelist['qtd'+sid];
    let total_employee_percentage:any = 0;
    this.summarydetails.forEach(x=>{
      total_employee_percentage = total_employee_percentage + parseFloat(this.empPercentArr[x.id]);
    })
    this.totalDetails.total_employee_percentage = total_employee_percentage;
    

    let tobesamp:any = (this.empPercentArr[sid] * this.total_employees_interviewed)/100;
    if(!isNaN(tobesamp) && isFinite(tobesamp)){
      //this.empTobeSmpArr[sid] = tobesamp.toFixed(2);

     
      this.empTobeSmpArr[sid] = Math.round(tobesamp);
      //return Math.round(tobesamp);
    }else{
      this.empTobeSmpArr[sid] = 0;
     // return 0;
    }
    
    let total_employee_sampled:any = 0;
    this.summarydetails.forEach(x=>{
      total_employee_sampled = total_employee_sampled + Math.round(this.empTobeSmpArr[x.id]);
    })
    
    this.totalDetails.to_be_sampled_employees = total_employee_sampled;
    //return sampledemp*2;
  }
  
}
