import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { Signature } from '@app/models/master/signature';
import { SignatureListService } from '@app/services/master/signature/signature-list.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-signature',
  templateUrl: './signature.component.html',
  styleUrls: ['./signature.component.scss'],
  providers: [SignatureListService]
})
export class SignatureComponent implements OnInit {

  title = 'Signature'; 
  form : FormGroup; 
  signatures$: Observable<Signature[]>;
  total$: Observable<number>;
  id:number;
  signatureData:any;
  SignatureData:any;
  error:any;
  success:any;
  buttonDisable = false;
  model: any = {franchise_id:null};
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  signatureEntries:any=[];
  modalss:any;
  loading:any=[];
  logoFileErr ='';
  userType:number;
  userdetails:any;
  userdecoded:any;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;
  canAddData = false;
  canEditData = false;
  canDeleteData = false;
  canViewData = false;

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: SignatureListService, public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
    this.signatures$ = service.signatures$;
    this.total$ = service.total$;
  }

  ngOnInit() {
    this.form = this.fb.group({	
      description:['',[this.errorSummary.noWhitespaceValidator]],        
	  title:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z0-9 \'\-+%/&,().-]+$")]],      	  
      signature:['']	
    });

    this.authservice.currentUser.subscribe(x => {
      if(x){
        
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;

        if(this.userdetails.resource_access != 1)
        {
          if(this.userdetails.rules.includes('edit_signature')  ){
            this.canEditData = true;
          }
          if(this.userdetails.rules.includes('delete_signature') ){
            this.canDeleteData = true;
          }
          if(this.userdetails.rules.includes('add_signature')  ){
            this.canAddData = true;
          }
        }
        if(this.userdetails.resource_access == 1)
        {
          this.canAddData = true;
          this.canEditData = true;
          this.canDeleteData = true;	
        }
        
      }else{
        this.userdecoded=null;
      }
    });
  }

  get f() { return this.form.controls; }

  signatureListEntries = [];
  signatureIndex:number=null;
  addsignature()
  {
    this.f.title.markAsTouched();
    this.logoFileErr = '';
    if(this.logo=='' || this.logo===undefined){
      this.logoFileErr = 'Please upload file';
      return false;
    }

    if(this.form.valid && this.logoFileErr =='')
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let description = this.form.get('description').value;
      let title = this.form.get('title').value;

      let expobject:any={title:title,description:description};
      
      if(1)
      {
        if(this.signatureData){
          expobject.id = this.signatureData.id;
          expobject.logo = this.signatureData.logo;
        }
        
        this.formData.append('formvalues',JSON.stringify(expobject));
        this.service.addData(this.formData)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              this.signatureData = '';
              this.formData = new FormData(); 
              this.service.customSearch();
              this.signatureFormreset();
              this.success = {summary:res.message};
              this.buttonDisable = false;
              this.logo = '';
              
             
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
        this.errorSummary.validateAllFormFields(this.form); 
        
      }   
    }
  }


  viewSignature(content,data)
  {
    this.SignatureData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  editStatus=0;
  editSignature(index:number,signaturedata) 
  {
	this.editStatus=1;	  
    this.formData = new FormData(); 
    this.logoFileErr ='';
    this.signatureData = signaturedata;
    this.logo = signaturedata.logo;
    this.form.patchValue({
      title:signaturedata.title,
      description:signaturedata.description
    });
  }

  removeSignature(content,index:number,signaturedata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
    	this.signatureFormreset();
        this.service.deleteSignatureData({id:signaturedata.id})
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
            this.error = {summary:error};
            this.loading['button'] = false;
        });
    }, (reason) => {
    })
    
  
  }

  logo:any;
  signatureChange(element) 
  {
    let files = element.target.files;
    this.logoFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension,this.errorSummary.imgvalidDocs))
    {

      this.formData.append("logo", files[0], files[0].name);
      this.logo = files[0].name;
      
    }else{
      this.logoFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }

  downloadsignatureFile(fileid='',filetype='',filename='')
  {
    this.service.downloadSignatureFile({id:fileid,filetype})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    },
    error => {
        this.error = {summary:error};
        this.modalss.close();
    });
  }

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  resetsignature(){
	this.editStatus=0;  
    this.form.reset();
    this.signatureData = '';
    this.formData = new FormData(); 
   	this.logoFileErr ='';
    this.logo = '';
  }

  signatureFormreset()
  {
	this.editStatus=0; 
    this.form.reset();
    this.signatureData = '';
    this.formData = new FormData(); 
   	this.logoFileErr ='';
    this.logo = '';
  }

  removesignature()
  {
    this.logo = '';
  }

  onSubmit(){ }

}
