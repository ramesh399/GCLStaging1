import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import {MailService} from '@app/services/library/mail/mail.service';
import { StandardService } from '@app/services/standard.service';
import { tap,first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import { Standard } from '@app/services/standard';
import { Mail } from '@app/models/library/mail';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';


@Component({
  selector: 'app-mail',
  templateUrl: './mail.component.html',
  styleUrls: ['./mail.component.scss'],
  providers: [MailService]
})
export class MailComponent implements OnInit {

  title = 'Mail'; 
  form : FormGroup; 
  mails$: Observable<Mail[]>;
  total$: Observable<number>;
  id:number;
  mailData:any;
  MailData:any;
  previewData:any;
  error:any;
  success:any;
  signaturelist:any=[];
  partnerslist:any=[];
  clientslist:any=[];
  statuslist:any=[];
  auditorslist:any=[];
  consultantslist:any=[];
  subscriberslist:any=[];
  standardList:Standard[];
  buttonDisable = false;
  model: any = {user_access_id:null};
  accessList:any=[];
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  mailEntries:any=[];
  modalss:any;
  attachment:any;
  attachmentFileErr ='';
  submitbuttontitle = 'Save';
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private standardservice: StandardService, private router: Router,private fb:FormBuilder, public service: MailService, public errorSummary: ErrorSummaryService, private authservice:AuthenticationService){
    this.mails$ = service.mails$;
    this.total$ = service.total$;
  }

  ngOnInit() {
    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
      }else{
        this.userdecoded=null;
      }
    });
    
    this.form = this.fb.group({	
      subject:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]],  
      body_content:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
      partners:['',[Validators.required]],
      auditors:['',[Validators.required]],
      clients:[''],
      consultants:['',[Validators.required]],
      subscribers:['',[Validators.required]],
      signature_id:['',[Validators.required]],
      sent_date:[''],
      status:['',[Validators.required]],
      attachment:['']	
    });

    this.standardservice.getStandard().subscribe(res => {
      this.clientslist = res['standards'];
      });

    this.service.getData().pipe(first())
    .subscribe(res => {
      this.signaturelist = res.signaturelist;
      this.statuslist = res.statuslist;
      this.partnerslist = res.partnerslist;
      this.auditorslist = res.auditorslist;
      this.consultantslist = res.consultantslist;
      this.subscriberslist = res.subscriberslist;
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });

  }

  getSelectedValue(val)
  {
    return this.clientslist.find(x=> x.id==val).name; 
  }

  get f() { return this.form.controls; } 

  mailIndex:number=null;
  loading:any=[];
  addmail()
  {
    this.f.subject.markAsTouched();
    this.f.body_content.markAsTouched();
    this.f.partners.markAsTouched();
    //this.f.clients.markAsTouched();
    this.f.consultants.markAsTouched();
    this.f.subscribers.markAsTouched();
    this.f.signature_id.markAsTouched();
    this.f.auditors.markAsTouched();
	
	/*
	if(this.form.get('status').value==2)
	{
		this.f.sent_date.setValidators([Validators.required]);		
		this.f.sent_date.updateValueAndValidity();
		this.f.sent_date.markAsTouched();
	}else{
		this.f.sent_date.setValidators([]);		
		this.f.sent_date.updateValueAndValidity();
		this.f.sent_date.markAsTouched();
	}
	*/
	
	this.f.sent_date.setValidators([]);		
	this.f.sent_date.updateValueAndValidity();
	this.f.sent_date.markAsTouched();
    
    this.f.status.markAsTouched();

    this.attachmentFileErr = '';
   /* if(this.attachment=='' || this.attachment===undefined){
      this.attachmentFileErr = 'Please upload file';
      return false;
    }
    */

    
    if(this.form.valid && this.attachmentFileErr =='')
    {
      this.buttonDisable = true;
      this.loading['button'] = true;
      let subject = this.form.get('subject').value;
      let body_content = this.form.get('body_content').value;
      let partners = this.form.get('partners').value;
      let clients = this.form.get('clients').value;
      let signature_id = this.form.get('signature_id').value;
      let auditors = this.form.get('auditors').value;
      let consultants = this.form.get('consultants').value;
      let subscribers = this.form.get('subscribers').value;
      //let sent_date = this.form.get('sent_date').value;
	  
      let sent_date = '';
      if(this.form.get('status').value==2 && this.form.get('sent_date').value!='' && this.form.get('sent_date').value!='NA')
      {
        sent_date = this.errorSummary.displayDateFormat(this.form.get('sent_date').value);
      }
	  
      let status = this.form.get('status').value;

      let expobject:any={subject:subject,body_content:body_content,partners:partners,clients:clients,auditors:auditors,consultants:consultants,subscribers:subscribers,signature_id:signature_id,sent_date:sent_date,status:status};
      
      if(1)
      {

        if(this.mailData){
          expobject.id = this.mailData.id;
          expobject.attachment = this.mailData.attachment;
        }
        
        this.formData.append('formvalues',JSON.stringify(expobject));
        this.service.addData(this.formData)
        .pipe(first())
        .subscribe(res => {

            this.loading['button'] = false;
            if(res.status){
              this.mailData = '';
              this.formData = new FormData(); 
              this.service.customSearch();
              this.mailFormreset();
              this.success = {summary:res.message};
              this.buttonDisable = false;
              this.attachment = '';
              
              /*
              setTimeout(() => {
                
              },this.errorSummary.redirectTime);
              */
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
             this.buttonDisable = false;
        });
        
      } else {
        
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.form); 
        
      }   
    }
  }
  get showpreview(){
    let subject = this.form.get('subject').value;
    let body_content = this.form.get('body_content').value;
    let signature_id = this.form.get('signature_id').value;

    if(subject!='' && body_content!='' && signature_id!='' && subject!==null && body_content!==null && signature_id!==null)
    {
      return true;
    }else{
      return false;
    }
  }
 
  previewsubjectcontent:any;
  previewbodycontent:any;
  mailPreview(content)
  {
    this.f.subject.markAsTouched();
    this.f.body_content.markAsTouched();
    this.f.signature_id.markAsTouched();

    let subject = this.form.get('subject').value;
    let body_content = this.form.get('body_content').value;
    let signature_id = this.form.get('signature_id').value;

    if(subject!='' && body_content!='' && signature_id!='' && subject!==null && body_content!==null && signature_id!==null)
    {
      this.loading['button'] = true;
      this.buttonDisable = true;

      

      let expobject:any={subject:subject,body_content:body_content,signature_id:signature_id};

      this.service.previewData(expobject)
        .pipe(first())
        .subscribe(res => {
          if(res.status){
            this.previewsubjectcontent = res.data.subject;
            this.previewbodycontent = res.data.body_content;
            this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
          }
          else if(res.status == 0){
            this.error = {summary:res};
          }
          this.loading['button'] = false;
          this.buttonDisable = false;
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        });
    }
  }

  editStatus=0;
  editMail(maildata) 
  { 
    this.editStatus=1;
    this.submitbuttontitle = 'Update';
	
    this.formData = new FormData(); 
    this.mailData = maildata;
    this.isShowSentDate = true;
    this.attachment = maildata.attachment;
    this.form.patchValue({
      subject:maildata.subject,
      body_content:maildata.body_content,
      partners:maildata.partners,
      auditors:maildata.auditors,
      clients:maildata.clients,
      consultants:maildata.consultants,
      subscribers:maildata.subscribers,
      signature_id:maildata.signature_id,
      sent_date:(maildata.sent_date!='' && maildata.sent_date!='0000-00-00' && maildata.sent_date!='NA' ? this.errorSummary.editDateFormat(maildata.sent_date):''),
      status:maildata.status
    });
    this.statusChange(maildata.status);
    this.scrollToBottom();
  }

  removeMail(content,maildata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.mailFormreset();
        this.service.deleteMailData({id:maildata.id})
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

  attachmentChange(element) 
  {
    let files = element.target.files;
    this.attachmentFileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("attachment", files[0], files[0].name);
      this.attachment = files[0].name;
      
    }else{
      this.attachmentFileErr ='Please upload valid file';
    }
    element.target.value = '';
  }

  isShowSentDate = false;
  statusChange(elementval) 
  {
    if(elementval == '1'){
      this.submitbuttontitle = 'Send Mail';
      this.isShowSentDate = false;
    }else{
	  this.isShowSentDate = true;
	  if(this.editStatus==1)
	  {
		this.submitbuttontitle = 'Update';
	  }else{
		this.submitbuttontitle = 'Save';
	  }
      this.isShowSentDate = true;
    }
    
  }

  downloadFile(fileid='',filetype='',filename='')
  {
    this.service.downloadAttachmentFile({id:fileid,filetype})
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


  viewMail(content,data)
  {
    this.MailData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  
  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  mailFormreset()
  {
	this.isShowSentDate = false;
	this.editStatus=0;
    this.form.reset();
    this.mailData = '';
    this.formData = new FormData(); 
    this.submitbuttontitle = 'Save';
    this.attachment = '';
	
	this.form.patchValue({
      partners:'',
      auditors:'',
      clients:'',
      consultants:'',
      subscribers:'',
      signature_id:'',      
      status:''
    });
  }

  removeattachment()
  {
	//this.editStatus=0;
    this.attachment = '';
	/*
	this.submitbuttontitle = 'Save';
	this.form.patchValue({
      partners:'',
      auditors:'',
      clients:'',
      consultants:'',
      subscribers:'',
      signature_id:'',      
      status:''
    });
	*/
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
