import { Component, OnInit,Input } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditClientinformationService } from '@app/services/audit/audit-clientinformation.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { tap,map, first } from 'rxjs/operators'; 
import {Observable} from 'rxjs';
import { Process } from '@app/models/master/process';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Country } from '@app/services/country';
import { CountryService } from '@app/services/country.service';

@Component({
  selector: 'app-audit-clientinformation',
  templateUrl: './audit-clientinformation.component.html',
  styleUrls: ['./audit-clientinformation.component.scss'],
  providers: [AuditClientinformationService]
})
export class AuditClientinformationComponent implements OnInit {
  @Input() app_id: number;
  @Input() unit_id: number;
  @Input() cond_viewonly: any;
  
  title = 'Audit Interview Employee'; 
  form : FormGroup; 
  supplierform : FormGroup;
  processform : FormGroup;
 
  id:number;
  audit_id:number;
  
  error:any;
  success:any;
  buttonDisable = false;
  formData:FormData = new FormData();
  companyForm : any = {};
  companydetails:any = [];
  supplierdetails:any = [];
  processdetails:any = [];
  categorylist:any = {};
  availablelist:any;
  sufficientlist:any;
  SupplierData:any;
  supplierData:any;
  processData:any;
  ProcessData:any;
  
  userType:number;
  userdetails:any;
  userdecoded:any;
  modalss:any;
  loading:any=[];
  answerArr:any;
  interviewrequirements:any;
  //unit_id:number;

  constructor(private modalService: NgbModal,private countryservice: CountryService,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: AuditClientinformationService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
  }

  reviewcommentlist=[];
  reviewcomments=[];
  generalOptions:any = [];
  countryList:Country[];

  ngOnInit() 
  {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    //this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
     /*
    this.supplierform = this.fb.group({	
      supplier_name:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]], 
      products_composition:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      supplier_address:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      validity:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
      available_in_gots_database:['',[Validators.required]],
      //sufficient:['',[Validators.required]],
    });

    this.processform = this.fb.group({
      process:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]], 
      description:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
    });
    
    this.loadSupplierInformation();
    this.loadProcessInformation();
    
    this.countryservice.getCountry().pipe(first()).subscribe(res => {
      this.countryList = res['countries'];
    });

    this.service.getQuestions({audit_id:this.audit_id,unit_id:this.unit_id}).pipe(
      tap(xx=>{
        this.getAnswers()
      }),
      first())
    .subscribe(res => {    
      this.interviewrequirements = res.data;
      if(this.reviewcomments.length<=0){
        this.interviewrequirements.forEach(val => {
          val.questions.forEach(x => {
            this.reviewcommentlist['qtd_comments'+x.id]='';
            this.reviewcommentlist['qtd'+x.id]='';
          });
        });
      }
      
    });
    
    this.service.getGeneralInformation({audit_id:this.audit_id}).pipe(first())
    .subscribe(res => {    
        this.companydetails = res.data;
        if(this.companydetails.length<=0){
          this.companydetails.forEach(xc=>{
            this.categorylist['generaldetail'+xc.id] = xc.value;
            this.categorylist['sufficient'+xc.id] = xc.sufficient;
          });
        }
        this.generalOptions = res.sufficientOptions;
    }); 
    
     
    */
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
  /*
  loadSupplierInformation()
  {
    this.service.getSupplierInformation({audit_id:this.audit_id}).pipe(first())
    .subscribe(res => {    
        this.supplierdetails = res.suppliers;
        this.availablelist = res.availablelist;
        this.sufficientlist = res.sufficientlist;
    }); 
  }

  loadProcessInformation()
  {
    this.service.getProcessDetails({audit_id:this.audit_id}).pipe(first())
    .subscribe(res => { 
      this.processdetails = res.processes;
    }); 
  }


  addsupplier()
  {
    this.sf.supplier_name.markAsTouched();
    this.sf.validity.markAsTouched();
    this.sf.products_composition.markAsTouched();
    this.sf.supplier_address.markAsTouched();
    this.sf.available_in_gots_database.markAsTouched();
    //this.sf.sufficient.markAsTouched();

    if(this.supplierform.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true; 

      let supplier_name = this.supplierform.get('supplier_name').value;
      let validity = this.supplierform.get('validity').value;
      let products_composition = this.supplierform.get('products_composition').value;
      let supplier_address = this.supplierform.get('supplier_address').value;
      let available_in_gots_database = this.supplierform.get('available_in_gots_database').value;
      //let sufficient = this.supplierform.get('sufficient').value;
	  
      let expobject:any={audit_id:this.audit_id,supplier_name:supplier_name,validity:validity,products_composition:products_composition,supplier_address:supplier_address,available_in_gots_database:available_in_gots_database};
      
      if(1)
      {
        if(this.supplierData)
        {
          expobject.id = this.supplierData.id;
        }
        
        this.service.addSupplierData(expobject)
        .pipe(first())
        .subscribe(res => {
            if(res.status){
              this.success = {summary:res.message};
              this.supplierFormreset(); 
              this.loading['button'] = false;
              this.loadSupplierInformation();
              this.buttonDisable = false;

            }else if(res.status == 0){
              //this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.enquiryForm)};
              this.error = {summary:res};
            }
            this.loading['button'] = false;
            this.buttonDisable = false;
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        });
        
      } else {
        
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.supplierform); 
        
      }   
    }
  }

  addprocess()
  {
    this.pf.process.markAsTouched();
    this.pf.description.markAsTouched();
    

    if(this.processform.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true; 

      let process = this.processform.get('process').value;
      let description = this.processform.get('description').value;
      
	  
      let expobject:any={audit_id:this.audit_id,process:process,description:description};
      
      if(1)
      {
        if(this.processData)
        {
          expobject.id = this.processData.id;
        }
        
        this.service.addProcessData(expobject)
        .pipe(first())
        .subscribe(res => {
            if(res.status){
              this.success = {summary:res.message};
              this.processFormreset(); 
              this.loading['button'] = false;
              this.loadProcessInformation();
              this.buttonDisable = false;

            }else if(res.status == 0){
              //this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.enquiryForm)};
              this.error = {summary:res};
            }
            this.loading['button'] = false;
            this.buttonDisable = false;
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        });
        
      } else {
        
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.processform); 
        
      }   
    }
  }

  viewSupplier(content,data)
  {
    this.SupplierData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  viewProcess(content,data)
  {
    this.ProcessData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }


  editStatus=0;
  editSupplier(index:number,supplierdata) 
  { 
    this.editStatus=1;
    this.success = {summary:''};
    this.supplierData = supplierdata;
    this.supplierform.patchValue({
      supplier_name:supplierdata.supplier_name,
      validity:supplierdata.validity,
      products_composition:supplierdata.products_composition,     
      supplier_address:supplierdata.supplier_address,
      available_in_gots_database:supplierdata.available_in_gots_database
      //sufficient:supplierdata.sufficient
    });
    this.scrollToBottom();
  }


  editProcess(index:number,processdata) 
  { 
    this.editStatus=1;
    this.success = {summary:''};
    this.processData = processdata;
    this.processform.patchValue({
      process:processdata.process,
      description:processdata.description
    });
    this.scrollToBottom();
  }


  removeSupplier(content,index:number,supplierdata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.supplierFormreset();
        this.service.deleteSupplierData({id:supplierdata.id})
        .pipe(first())
        .subscribe(res => {

            if(res.status){
              this.success = {summary:res.message};
              this.buttonDisable = true;
              this.loadSupplierInformation();
            }else if(res.status == 0){
              this.error = {summary:res};
            }
            this.loading['button'] = false;
            this.buttonDisable = false;
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        });
    }, (reason) => {
    });
  }

  removeProcess(content,index:number,processdata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.processFormreset();
        this.service.deleteProcessData({id:processdata.id})
        .pipe(first())
        .subscribe(res => {

            if(res.status){
              this.success = {summary:res.message};
              this.buttonDisable = true;
              this.loadProcessInformation();
            }else if(res.status == 0){
              this.error = {summary:res};
            }
            this.loading['button'] = false;
            this.buttonDisable = false;
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        });
    }, (reason) => {
    });
  }

  supplierFormreset()
  {
    this.editStatus=0;
    
    this.supplierData = '';  
    this.supplierform.reset();
    
    this.supplierform.patchValue({     
      supplier_name:'',   
      supplier_address:'',  
      validity:'',
      products_composition:'',
      available_in_gots_database:''
      //sufficient:''     
    });
  }

  processFormreset()
  {
    this.editStatus=0;
    
    this.processData = '';  
    this.processform.reset();
    
    this.processform.patchValue({     
      process:'',   
      description:''

    });
  }

  getAnswers(){
    this.service.getchecklistAnswer({audit_id:this.audit_id}).pipe(first())
    .subscribe(list => {    

        if(list['status'] && list['data'])
        {
          console.log('s');
          this.reviewcomments = list['data'];

          this.reviewcomments.forEach(val => {
           // console.log(val.question_id);
          this.reviewcommentlist['qtd_comments'+val.question_id]=val.comment;
          this.reviewcommentlist['qtd'+val.question_id]=val.answer;
        });
        }
    });
  }
  
  get f() { return this.form.controls; }
  get sf() { return this.supplierform.controls; }
  get pf() { return this.processform.controls; }

  onSubmit(rf:NgForm)
  {
      this.interviewrequirements.forEach(element => {
        element.questions.forEach(qtn => {
          let answer = eval("rf.value.qtd"+qtn.id);
          let comment = eval("rf.value.qtd_comments"+qtn.id);
          
          rf.controls["qtd"+qtn.id].markAsTouched();
          rf.controls["qtd_comments"+qtn.id].markAsTouched();
        });
      });

      if (rf.valid) 
      {
        let reviewdata = [];
        this.interviewrequirements.forEach(element => {
          //,comment:f.value.qtd_comments
          element.questions.forEach(qtn => {
            let ans = {categoryid:element.categoryid,categoryname:element.categoryname,question:qtn.name,question_id:qtn.id,answer:eval("rf.value.qtd"+qtn.id),comment:eval("rf.value.qtd_comments"+qtn.id)};
            reviewdata.push(ans);
          });
        });

        let requiremnetdata={
          audit_id:this.audit_id,
          checklistdata:reviewdata
        }
        
        
        this.loading['button']  = true;
        this.service.saveChecklist(requiremnetdata)
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
  

  onGeneralInfoSubmit(rf:NgForm)
  {
      this.companydetails.forEach(qtn => {
        
          let generaldetail = eval("rf.value.generaldetail"+qtn.id);
          let sufficient = eval("rf.value.sufficient"+qtn.id);
          
          rf.controls["generaldetail"+qtn.id].markAsTouched();
          rf.controls["sufficient"+qtn.id].markAsTouched();
        
      });

      if (rf.valid) 
      {
        let reviewdata = [];
        this.companydetails.forEach(qtn => {
          //,comment:f.value.qtd_comments
          let ans = {question:qtn.name,question_id:qtn.id,answer:eval("rf.value.generaldetail"+qtn.id),sufficient:eval("rf.value.sufficient"+qtn.id)};
          reviewdata.push(ans);
        });

        let requiremnetdata={
          audit_id:this.audit_id,
          checklistdata:reviewdata
        }
        
        
        this.loading['button']  = true;
        this.service.saveGeneralInfoDetails(requiremnetdata)
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

  scrollToBottom()
  {
    window.scroll({ 
      top: window.innerHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }
 */

}
