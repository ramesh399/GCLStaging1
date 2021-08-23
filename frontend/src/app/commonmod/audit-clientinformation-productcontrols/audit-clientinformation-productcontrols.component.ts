import { Component, OnInit, Input } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditClientinformationService } from '@app/services/audit/audit-clientinformation.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { tap,map, first } from 'rxjs/operators'; 
import {Observable} from 'rxjs';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Renderer, ElementRef } from '@angular/core';

@Component({
  selector: 'app-audit-clientinformation-productcontrols',
  templateUrl: './audit-clientinformation-productcontrols.component.html',
  styleUrls: ['./audit-clientinformation-productcontrols.component.scss']
})
export class AuditClientinformationProductcontrolsComponent implements OnInit {
  @Input() app_id: number;
  @Input() unit_id: number;
  @Input() cond_viewonly: any;
  @Input() audit_id: number;

  title = 'Audit Interview Employee'; 
  form : FormGroup; 
  
  processform : FormGroup;
  id:number;
  //audit_id:number;
  //unit_id:number;
  error:any;
  success:any;
  buttonDisable = false;
  model: any = {sufficient:''}
  formData:FormData = new FormData();
  
  companydetails:any = [];
  
  processdetails:any = [];
  
  
  sufficientlist:any;
  
  processData:any;
  ProcessData:any;
  
  userType:number;
  userdetails:any;
  userdecoded:any;
  modalss:any;
  loading:any=[];
  
  //generalOptions:any = [];

  constructor(private elRef: ElementRef, private renderer: Renderer,private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: AuditClientinformationService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
  }

  ngOnInit() {
    if(!this.audit_id){
      this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    }
    //this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    //this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
     
    this.processform = this.fb.group({
      process:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]], 
      description:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],
      sufficient:['',[Validators.required]],
    });
    
    
    this.loadProcessInformation();

    this.service.getOptionlist().pipe(first())
    .subscribe(res => {   
        this.sufficientlist = res.sufficientlist;
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

  sufficient_access:number = 0;
  loadProcessInformation()
  {
    this.service.getProcessDetails({audit_id:this.audit_id,unit_id:this.unit_id,app_id:this.app_id}).pipe(first())
    .subscribe(res => { 
      this.processdetails = res.processes;
      this.sufficient_access = res.sufficient_access;
    }); 
  }

  addprocess()
  {
    this.pf.process.markAsTouched();
    this.pf.description.markAsTouched();

    if(this.sufficient_access){
      this.pf.sufficient.markAsTouched(); 
    }else{
      this.pf.sufficient.setValidators([]);
      this.pf.sufficient.updateValueAndValidity();
    }
    
    
    

    if(this.processform.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true; 

      let process = this.processform.get('process').value;
      let description = this.processform.get('description').value;

      let sufficient:any = '';
      if(this.sufficient_access){
        sufficient = this.processform.get('sufficient').value;
      }
      let expobject:any={audit_id:this.audit_id,unit_id:this.unit_id,app_id:this.app_id,process:process,description:description,sufficient:sufficient};
      
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
          this.buttonDisable = false;
          this.error = {summary:error};
          this.loading['button'] = false;
        });
      }  
    }
  }


  viewProcess(content,data)
  {
    this.sufficientsuccess = '';
    this.sufficienterror = '';
    this.model.sufficient = data.sufficient;
    this.ProcessData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  editStatus=0;
  editProcess(index:number,processdata) 
  { 
    this.editStatus=1;
    this.success = {summary:''};
    this.processData = processdata;
    let sufficient = processdata.sufficient=== null?'':processdata.sufficient;
    this.processform.patchValue({
      process:processdata.process,
      description:processdata.description,
      sufficient:sufficient
    });
    this.scrollToBottom();
  }

  sufficientsuccess:any;
  sufficienterror:any;
  changeSufficient(content,value)
  {
    this.renderer.invokeElementMethod(this.elRef.nativeElement.ownerDocument.activeElement, 'blur');
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.service.changeProductControlsSufficient({audit_id:this.audit_id,unit_id:this.unit_id,app_id:this.app_id,id:this.ProcessData.id,sufficient:value})
        .pipe(first())
        .subscribe(res => {

          if(res.status){
            this.sufficientsuccess = {summary:res.message};
            this.ProcessData.sufficient = value;
            this.buttonDisable = true;
            this.loadProcessInformation();
          }else if(res.status == 0){
            this.sufficienterror = {summary:res};
          }
          this.loading['button'] = false;
          this.buttonDisable = false;
        },
        error => {
            this.sufficienterror = {summary:error};
            this.loading['button'] = false;
        });
    }, (reason) => {
      this.model.sufficient = this.ProcessData.sufficient;
    })
  }

  removeProcess(content,index:number,processdata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.processFormreset();
        this.service.deleteProcessData({audit_id:this.audit_id,unit_id:this.unit_id,app_id:this.app_id,id:processdata.id})
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


  processFormreset()
  {
    this.editStatus=0;
    
    this.processData = '';  
    this.processform.reset();
    
    this.processform.patchValue({     
      process:'',   
      description:'',
      sufficient:''     

    });
  }

  get pf() { return this.processform.controls; }

  scrollToBottom()
  {
    window.scroll({ 
      top: document.body.scrollHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }

}
