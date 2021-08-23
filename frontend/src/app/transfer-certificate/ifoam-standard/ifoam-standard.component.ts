import { Component, OnInit,EventEmitter,QueryList, ViewChildren, HostListener  } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';

import { IfoamStandardListService } from '@app/services/transfer-certificate/ifoam-standard/ifoam-standard-list.service';

import {Standard} from '@app/models/transfer-certificate/standard';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import {saveAs} from 'file-saver';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import {Observable} from 'rxjs';

@Component({
  selector: 'app-ifoam-standard',
  templateUrl: './ifoam-standard.component.html',
  styleUrls: ['./ifoam-standard.component.scss'],
  providers: [IfoamStandardListService]
})
export class IfoamStandardComponent implements OnInit {

  title = '';
  Standard$: Observable<Standard[]>;
  total$: Observable<number>;
  //source_file_status$: Observable<number>;
  //view_file_status$: Observable<number>;

  auditplanStatus$: Observable<any>;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;


  form : FormGroup;
  
  buttonDisable = false;
  error:any;
  id:number;
  typelist:any=[];
  statuslist:any=[];
  
  model: any = {id:null,action:null,type:'',description:'',date:''};
  success:any;
  modalss:any;

  formData:FormData = new FormData();
  userType:number;
  userdetails:any;
  userdecoded:any;

  gislogEntries:any=[];

  fileErr = '';
  viewErr = '';
  type:any;
  reviewerList:any;
  accessList:any;
  standardList:any; 
    
  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, public userService:UserService,public service: IfoamStandardListService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { 
  
    this.Standard$ = service.standard$;
    this.total$ = service.total$;		
    
	
	window.scroll({ 
      top: 0, 
      left: 0, 
      behavior: 'smooth' 
    });
  }
  canAddData = false;
  canEditData = false;
  canDeleteData = false;
  canViewData = false;
  ngOnInit() {	
	this.title = 'IFOAM Standard';		
			
	this.form = this.fb.group({
		name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]]
	});	   
    // Validators.pattern("^[a-zA-Z \'\-().,]+$")
    this.authservice.currentUser.subscribe(x => {
		if(x){
			let user = this.authservice.getDecodeToken();
			this.userType= user.decodedToken.user_type;
      this.userdetails= user.decodedToken;
      
      if(this.userdetails.resource_access == 1){
        this.canAddData = true;
        this.canEditData = true;
        this.canDeleteData = true;
      }else{
        
        if(this.userdetails.rules.includes('edit_ifoam_standard')  ){
          this.canEditData = true;
        }
        if(this.userdetails.rules.includes('delete_ifoam_standard') ){
          this.canDeleteData = true;
        }
        if(this.userdetails.rules.includes('add_ifoam_standard')  ){
          this.canAddData = true;
        }
        
      }
        
		}else{
			this.userdecoded=null;
		}
	});

  }

  get f() { return this.form.controls; } 

  onSort({column, direction}: SortEvent) 
  {
    this.headers.forEach(header => {
      if (header.sortable !== column) {
        header.direction = '';
      }
    });

    this.service.sortColumn = column;
    this.service.sortDirection = direction;
  }

  
  
  
  gisListEntries = [];
  gisIndex:number=null;
  loading:any=[];
  addData()
  {
    this.f.name.markAsTouched();
       
    if(this.form.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true;     
     
      let name = this.form.get('name').value;
      
      let expobject:any={};

      expobject = {name:name};
       
            
      
	    if(this.curData){
	      expobject.id = this.curData.id;
	    }
	    
	    this.formData.append('formvalues',JSON.stringify(expobject));

	    this.service.addData(this.formData)
	    .pipe(first())
	    .subscribe(res => {

        if(res.status)
        {
          this.formData = new FormData(); 
          this.service.customSearch();
          this.formReset();
          this.success = {summary:res.message};
          this.buttonDisable = false;			
        }
        else if(res.status == 0)
        {
				  this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
        }
        else
        {			      
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
  

  formReset()
  {
    this.curData = '';
	  this.editStatus=0;
    this.form.reset();
  }

  downloadData:any;
  showDetails(content,data)
  {
    this.downloadData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }
  
  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  
  removeData(content,index:number,data) {

      this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

      this.modalss.result.then((result) => {

          
          this.formReset();
          
          this.service.deleteData({id:data.id})
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

  editStatus=0;
  curData:any;
  editData(index:number,data) {
    this.formData = new FormData(); 
    this.curData = data;
	this.editStatus = 1;

	
	this.form.patchValue({
    name:data.name
	});
	
	this.scrollToBottom();	
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
