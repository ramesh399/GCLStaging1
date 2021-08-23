import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { Document } from '@app/models/library/document';
import { DocumentListService } from '@app/services/library/document/document-list.service';
import { User } from '@app/models/master/user';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-document',
  templateUrl: './document.component.html',
  styleUrls: ['./document.component.scss'],
  providers: [DocumentListService]
})
export class DocumentComponent implements OnInit {

  

  title = 'OSP Document'; 
  form : FormGroup; 
  documents$: Observable<Document[]>;
  total$: Observable<number>;
  id:number;
  documentData:any;
  DocumentData:any;
  error:any;
  success:any;
  buttonDisable = false;
  model: any = {franchise_id:null};
  franchiseList:User[];
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  typelist:any;
  documentEntries:any=[];
  modalss:any;
  loading:any=[];
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  documentFileErr ='';

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private userservice: UserService, private router: Router,private fb:FormBuilder, public service: DocumentListService, public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
    this.documents$ = service.documents$;
    this.total$ = service.total$;
  }

  getSelectedValue(type,val)
  {
    if(type=='franchise_id'){
      return this.franchiseList.find(x=> x.id==val).osp_details;
    }
  }

  getSelectedFranchiseValue(val)
	{
		return this.franchiseList.find(x=> x.id==val).osp_details;    
  }

  ngOnInit() 
  {
    this.form = this.fb.group({	
      note:['',[this.errorSummary.noWhitespaceValidator]],  
      franchise_id:['',[Validators.required]],
      document_type_id:['',[Validators.required]],
      document:['']	
    });


    this.service.getDocTypeList().pipe(first())
    .subscribe(res => {
      //this.gislogEntries = res.gislogs;    
      this.typelist  = res.typelist;
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });

    this.userservice.getAllUser({type:3,filteruser:1}).pipe(first())
    .subscribe(res => {
      this.franchiseList = res.users;
    },
    error => {
        this.error = {summary:error};
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
  
  documentListEntries = [];
  documentIndex:number=null;
  adddocument()
  {
    this.f.document_type_id.markAsTouched();
    this.f.franchise_id.markAsTouched();
    this.documentFileErr = '';
    if(this.document=='' || this.document===undefined){
      this.documentFileErr = 'Please upload file';
      return false;
    }

    if(this.form.valid && this.documentFileErr =='')
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let note = this.form.get('note').value;
      let document_type_id = this.form.get('document_type_id').value;
      let franchise_id = this.form.get('franchise_id').value;

      let expobject:any={note:note,document_type_id:document_type_id,franchise_id:franchise_id};
      
      if(1)
      {
        if(this.documentData){
          expobject.id = this.documentData.id;
          expobject.document = this.documentData.document;
        }
        
        this.formData.append('formvalues',JSON.stringify(expobject));
        this.service.addData(this.formData)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              this.success = {summary:res.message};
              this.service.customSearch();
              this.documentFormreset();
              
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
        this.errorSummary.validateAllFormFields(this.form); 
        
      }   
    }
  }


  viewDocument(content,data)
  {
    this.DocumentData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  editStatus=0;
  editDocument(index:number,documentdata) 
  { 
    this.editStatus=1;
    this.documentFileErr = '';
    this.success = {summary:''};
    this.formData = new FormData(); 
    this.documentData = documentdata;
    this.document = documentdata.document;
    this.form.patchValue({
      note:documentdata.note,
      document_type_id:documentdata.document_type_id,     
      franchise_id:documentdata.franchise_id
    });
    this.scrollToBottom();
  }

  document:any;
  documentChange(element) 
  {
    let files = element.target.files;
    this.documentFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("document", files[0], files[0].name);
      this.document = files[0].name;
      
    }else{
      this.documentFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }

  downloaddocFile(fileid='',filetype='',filename='')
  {
    this.service.downloadDocumentFile({id:fileid,filetype})
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

  removeDocument(content,index:number,documentdata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.resetdocument();
        this.service.deleteDocumentData({id:documentdata.id})
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

  resetdocument(){
	this.editStatus=0;
	this.documentFileErr='';
    this.form.reset();
    this.documentData = '';
    this.formData = new FormData(); 
   
    this.document = '';
	
	this.form.patchValue({     
      document_type_id:'',     
      franchise_id:''
    });
  }

  documentFormreset()
  {
	this.editStatus=0;
	this.documentFileErr='';
    this.documentData = '';
    this.formData = new FormData(); 
    this.document = '';
    this.form.reset();
	
	this.form.patchValue({     
      document_type_id:'',     
      franchise_id:''
    });
  }

  removedocument()
  {
    this.document = '';
  }

  onSubmit(){ }

  scrollToBottom()
  {
    window.scroll({ 
      top: window.innerHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }

}
