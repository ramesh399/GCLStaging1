import { Component, OnInit,EventEmitter,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { FaqService } from '@app/services/library/faq/faq.service';
import {FaqListService} from '@app/services/library/faq/faq-list.service';
import { UserRoleService } from '@app/services/master/userrole/userrole.service';
import { User } from '@app/models/master/user';
import { tap,first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import { Faq } from '@app/models/library/faq';
import { UserRole } from '@app/models/master/userrole';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';


@Component({
  selector: 'app-faq',
  templateUrl: './faq.component.html',
  styleUrls: ['./faq.component.scss'],
  providers: [FaqListService]
})
export class FaqComponent implements OnInit {

  title = 'FAQ'; 
  form : FormGroup; 
  faqs$: Observable<Faq[]>;
  total$: Observable<number>;
  id:number;
  faqData:any;
  FaqData:any;
  error:any;
  success:any;
  buttonDisable = false;
  model: any = {user_access_id:null};
  accessList:any=[];
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  faqEntries:any=[];
  roleList:UserRole[]=[];
  modalss:any;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private userRoleService:UserRoleService,private modalService: NgbModal,private activatedRoute:ActivatedRoute, private userservice: UserService, private router: Router,private fb:FormBuilder, public userService:UserService,public service: FaqListService, private faqService: FaqService,private errorSummary: ErrorSummaryService, private authservice:AuthenticationService) {
    this.faqs$ = service.faqs$;
    this.total$ = service.total$;
  }

  getSelectedValue(val)
  {
    
    return this.accessList.find(x=> x.id==val).name;
    
  }

  ngOnInit() {
    this.form = this.fb.group({	
      question:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
      answer:['',[Validators.required, this.errorSummary.noWhitespaceValidator]],  
      user_access_id:['',[Validators.required]]
    });

    this.faqService.getFaqList(this.id).pipe(first())
    .subscribe(res => {
      this.faqEntries = res.faqs;    
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });

    this.service.getData().pipe(first())
    .subscribe(res => {
      this.accessList = res.useraccess;
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });

    this.userRoleService.getAllRoles().subscribe(res => {
      this.roleList = res['userroles'];
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

  faqListEntries = [];
  faqIndex:number=null;
  loading:any=[];
  addfaq()
  {
    this.f.question.markAsTouched();
    this.f.answer.markAsTouched();
    this.f.user_access_id.markAsTouched();
    
    if(this.form.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] =true;
      let question = this.form.get('question').value;
      let answer = this.form.get('answer').value;
      let user_access_id = this.form.get('user_access_id').value;

      let expobject:any={question:question,answer:answer,user_access_id:user_access_id};

      // expobject["question"] = question;
      // expobject["answer"] = answer;
      // expobject["user_access_id"] = user_access_id;
      
      if(1)
      {

        if(this.faqData){
          expobject.id = this.faqData.id;
        }
        
       // this.formData.append('formvalues',JSON.stringify(expobject));
        this.service.addData(expobject)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              this.faqData = '';
              //this.formData = new FormData(); 
              this.service.customSearch();
              this.faqFormreset();
              this.success = {summary:res.message};
              this.buttonDisable = false;
              
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

  editStatus=0;
  editFaq(index:number,faqdata) {
   // this.formData = new FormData(); 
    this.editStatus=1;
    this.faqData = faqdata;
    
    this.form.patchValue({
      question:faqdata.question,
      answer:faqdata.answer,     
      user_access_id:faqdata.user_access_id
    });
    this.scrollToBottom();
  }

  getSelectedRoleValue(val)
	{
		return this.roleList.find(x=> x.id==val).role_name;    
	}


scrollToBottom()
  {
    window.scroll({ 
      top: window.innerHeight,
      left: 0, 
      behavior: 'smooth' 
    });
  }

  viewFaq(content,data)
  {
    this.FaqData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  removeFaq(content,index:number,faqdata) {

    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.faqFormreset();
        this.service.deleteFaqData({id:faqdata.id})
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

  faqFormreset()
  {
	this.editStatus=0;  
    this.form.reset();
  }

  onSubmit(){ }

}
