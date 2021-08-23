import { Component, OnInit,EventEmitter,QueryList, ViewChildren, HostListener  } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';

import { StandardCombinationListService } from '@app/services/master/standard-combination/standard-combination-list.service';
import { StandardService } from '@app/services/standard.service';

import {StandardCombination} from '@app/models/master/standard-combination';
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
  selector: 'app-standard-combination',
  templateUrl: './standard-combination.component.html',
  styleUrls: ['./standard-combination.component.scss'],
  providers: [StandardCombinationListService]
})
export class StandardCombinationComponent implements OnInit {

  title = '';
  StandardCombination$: Observable<StandardCombination[]>;
  total$: Observable<number>; 

  auditplanStatus$: Observable<any>;
  paginationList = PaginationList;
  commontxt = commontxt;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;


  form : FormGroup;
  logForm: FormGroup;
  
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
  standard_idErrors:any = '';
  declaration_contentErrors:any = '';

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder, public userService:UserService,public service: StandardCombinationListService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService,public standardservice:StandardService) { 
  
    this.StandardCombination$ = service.standardCombination$;
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
    this.title = 'Transaction Certificate Standard Combination';
  
    this.standardservice.getStandard().subscribe(res => {
      this.standardList = res['standards'];
    });
        
    this.form = this.fb.group({	
        standard_id:['',[Validators.required]],
		declaration_content:['',[Validators.required]],	  
    });	   
      
      this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        
        if(this.userdetails.resource_access != 1)
        {
          if(this.userdetails.rules.includes('edit_standard_combination')  ){
            this.canEditData = true;
          }
          if(this.userdetails.rules.includes('delete_standard_combination') ){
            this.canDeleteData = true;
          }
          if(this.userdetails.rules.includes('add_standard_combination')  ){
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
    get sf() { return this.logForm.controls; }   
    
    getSelectedValue(val)
    {
      
      return this.standardList.find(x=> x.id==val).name;
      
    }
    
    newVersionStatus=0;
    addNewVersion()
    {
      this.newVersionStatus=1;
      this.addData();
    }
    
    
    
    gisListEntries = [];
    gisIndex:number=null;
    loading:any=[];
    addData()
    {
      this.standard_idErrors = '';
	  this.declaration_contentErrors = '';	  
      this.f.standard_id.markAsTouched(); 	
	  this.f.declaration_content.markAsTouched(); 	
         
      if(this.form.valid)
      {
        this.loading['button'] = true;
        this.buttonDisable = true;     
       
		let standard_id = this.form.get('standard_id').value;      
		let declaration_content = this.form.get('declaration_content').value;      
        	
        let expobject:any={};  
        expobject = {standard_id:standard_id,declaration_content:declaration_content};
         
        if(this.curData)
		{
          expobject.id = this.curData.id;        
        }
        
        this.formData.append('formvalues',JSON.stringify(expobject));
  
        this.service.addData(this.formData)
        .pipe(first())
        .subscribe(res => {
  
        
            if(res.status){
              this.formData = new FormData(); 
              this.service.customSearch();
              this.formReset();
              this.success = {summary:res.message};
              this.buttonDisable = false;
              
              /*
              setTimeout(() => {
                
              },this.errorSummary.redirectTime);
              */
            }else if(res.status == 0){
              //this.error = {summary:res};
              this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
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
		this.fileErr ='';
		this.viewErr ='';
		this.formData = new FormData(); 
		this.curData = '';   
		this.editStatus=0;
		this.newVersionStatus=0;
		this.form.reset();
    }
  
    downloadData:any;
    showDetails(content,data)
    {
      this.downloadData = data;
      //console.log(data);
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
      standard_id:data.standard_id,
	  declaration_content:data.declaration_content	
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
