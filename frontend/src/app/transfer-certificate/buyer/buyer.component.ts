import { Component, OnInit,EventEmitter,QueryList, ViewChildren, HostListener  } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';

import { BuyerListService } from '@app/services/transfer-certificate/buyer/buyer-list.service';

import {Buyer} from '@app/models/transfer-certificate/buyer';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import {saveAs} from 'file-saver';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import {Observable} from 'rxjs';

import { CountryService } from '@app/services/country.service';
import { Country } from '@app/services/country';
import { State } from '@app/services/state';


@Component({
  selector: 'app-buyer',
  templateUrl: './buyer.component.html',
  styleUrls: ['./buyer.component.scss'],
  providers: [BuyerListService]
})
export class BuyerComponent implements OnInit {

  title = '';
  Buyer$: Observable<Buyer[]>;
  total$: Observable<number>;
  //source_file_status$: Observable<number>;
  //view_file_status$: Observable<number>;

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
  
  countryList:Country[];
  stateList:State[];
  companyStateList:State[];
  
  downloadTypeArray = {'buyer':'Buyer','consignee':'Consignee','seller':'Seller'};
  downloadTypeActionArray = {'buyer':'buyer','consignee':'consignee','seller':'seller'};
    
  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute,private countryservice: CountryService,private router: Router,private fb:FormBuilder, public userService:UserService,public service: BuyerListService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { 
  
    this.Buyer$ = service.buyer$;
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
  canViewData = true;
  ngOnInit() {
  	//this.type = this.activatedRoute.snapshot.queryParams.type;
	this.type = this.activatedRoute.snapshot.data['pageType'];	
		
	this.title = this.downloadTypeArray[this.type];	
	//this.title = this.activatedRoute.snapshot.data['pageType'];
	
	this.countryservice.getCountry().subscribe(res => {
      this.countryList = res['countries'];
    });
    /*
  this.service.getAccessData({type:this.type}).subscribe(res => {
    //this.countryList = res['countries'];
    if(res){

      this.canAddData = res['canEditData'];
      this.canDeleteData = res['canDeleteData'];
      this.canAddData = res['canAddData'];
    }
    
  });
  */
	this.form = this.fb.group({		
		name:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
		client_number:['',[this.errorSummary.noWhitespaceValidator,Validators.maxLength(50)]],
		email:['',[this.errorSummary.noWhitespaceValidator,Validators.email,Validators.maxLength(255)]],
		phonenumber:['',[this.errorSummary.noWhitespaceValidator,Validators.pattern("^[0-9\-]*$"), Validators.minLength(8), Validators.maxLength(15)]],
		address:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
		city:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(50)]],
		//zipcode:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.pattern("^[a-zA-Z0-9]+$"),Validators.maxLength(15)]],
		zipcode:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.maxLength(15)]],
		country_id:['',[Validators.required]],
		state_id:[''],
	});	   
    
    this.authservice.currentUser.subscribe(x => {
		if(x){
			let user = this.authservice.getDecodeToken();
			this.userType= user.decodedToken.user_type;
      this.userdetails= user.decodedToken;
      
      if(this.userdetails.resource_access != 1){
        if(this.type == 'buyer'){
          if(this.userdetails.rules.includes('edit_buyer') || this.userType ==2){
            this.canEditData = true;
          }
          if(this.userdetails.rules.includes('delete_buyer') || this.userType ==2){
            this.canDeleteData = true;
          }
        }else if(this.type == 'consignee'){
          if(this.userdetails.rules.includes('edit_consignee') || this.userType ==2){
            this.canEditData = true;
          }
          if(this.userdetails.rules.includes('delete_consignee') || this.userType ==2){
            this.canDeleteData = true;
          }
        }
        
      }else if(this.userdetails.resource_access == 1){
        this.canEditData = true;
        this.canDeleteData = true;
      }
      if(this.userType ==2){
        this.canAddData = true;
      }
			//this.canAddData = true;
			//this.canEditData = true;
			//this.canDeleteData = true;			
		}else{
			this.userdecoded=null;
		}
	});

  }

  get f() { return this.form.controls; } 
  get sf() { return this.logForm.controls; }  

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

  files:any=[];
  fileChange(element) {
    let files = element.target.files;
    this.fileErr ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      let fileindex = this.files.length;
      this.formData.append("document["+fileindex+"]", files[0], files[0].name);
      this.files.push({id:'',deleted:0,added:1,name:files[0].name});
      
    }else{
      this.fileErr ='Please upload valid source file';
    }
    element.target.value = '';
   
  }
  
  viewfiles:any=[];
  viewFileChange(element) {
    let viewfiles = element.target.files;
    this.viewErr ='';
    let fileextension = viewfiles[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      let fileindex = this.viewfiles.length;
      this.formData.append("viewdocument["+fileindex+"]", viewfiles[0], viewfiles[0].name);
      this.viewfiles.push({id:'',deleted:0,added:1,name:viewfiles[0].name});
      
    }else{
      this.viewErr ='Please upload valid view file';
    }
    element.target.value = '';
   
  }
  
  get filterFile(){
    return this.files.filter(x=>x.deleted==0);
  }
  removeFile(fileindex)
  {
  	this.files[fileindex].deleted =1;
  }
  
  removeViewFile(fileindex)
  {
  	this.viewfiles[fileindex].deleted =1;
  }
  
  getSelectedValue(val)
  {
    
    return this.accessList.find(x=> x.id==val).name;
    
  }
  
  getSelectedStandardValue(val)
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
	this.f.name.markAsTouched();
    //this.f.client_number.markAsTouched();
    this.f.address.markAsTouched();
    this.f.city.markAsTouched();
	
	this.f.country_id.markAsTouched();
	//this.f.state_id.markAsTouched();
	this.f.zipcode.markAsTouched();

	
	/*
    this.f.status.markAsTouched();
	let fileValidationStatus=false;
    let addfiles = this.files.find(x=>x.deleted==0)
    if(!addfiles || addfiles.length<=0){
    	this.fileErr = 'Please upload source file';
    	fileValidationStatus=true;
    }
	
	let addviewfiles = this.viewfiles.find(x=>x.deleted==0)	
	if(!addviewfiles || addviewfiles.length<=0){
		this.viewErr = 'Please upload view file';
    	fileValidationStatus=true;
    }
	
	if(fileValidationStatus==true)
	{
		return false;
	}
	*/
    
    if(this.form.valid)
    {
      this.loading['button'] = true;
      this.buttonDisable = true;     
     
      let name = this.form.get('name').value;
      let client_number = this.form.get('client_number').value;
      let address = this.form.get('address').value;
      let email = this.form.get('email').value;
      let phonenumber = this.form.get('phonenumber').value;
      let city = this.form.get('city').value;
	  
	  let country_id = this.form.get('country_id').value;
	  let state_id = this.form.get('state_id').value;
	  let zipcode = this.form.get('zipcode').value;
	  
	  let expobject:any={};

      expobject = {name:name,client_number:client_number,address:address,email:email,phonenumber:phonenumber,city:city,country_id:country_id,state_id:state_id,zipcode:zipcode,type:this.type};
       
            
      
	    if(this.curData){
	      expobject.id = this.curData.id;
	      //expobject['gis_file'] = this.curData.gis_file;
	    }
	    
	    this.formData.append('formvalues',JSON.stringify(expobject));

	    this.service.addData(this.formData)
	    .pipe(first())
	    .subscribe(res => {

	    
	        if(res.status){
				this.formData = new FormData(); 
				this.service.customSearch();				
				this.success = {summary:res.message};
				
				setTimeout(() => {					
					this.success = {summary:''};
					this.formReset();
					this.buttonDisable = false;	          					
				}, this.errorSummary.redirectTime);				
				
	        }else if(res.status == 0){				
				this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
	        }else{			      
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
    this.fileErr ='';
    this.viewErr ='';
  	this.formData = new FormData(); 
    this.curData = '';
    this.files = [];
	  this.viewfiles = [];
    this.editStatus=0;
    this.newVersionStatus=0;
    this.form.reset();
    this.error = {};
    this.success = {};
    this.form.patchValue({
      country_id:'',
      state_id:''		
    });
  }
  nameErrors:any='';
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
          
          this.service.deleteData({id:data.id,type:this.type})
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
		name:data.name,
		client_number:data.client_number,
		address:data.address,
		email:data.email,
		phonenumber:data.phonenumber,
		city:data.city,
		country_id:data.country_id,
		state_id:data.state_id,
		zipcode:data.zipcode
	});
	
	this.getStateList(data.country_id,1);
	
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
  
  getStateList(id:number,editS:any=0){
    this.stateList = [];
	if(editS==0)
	{
		this.form.patchValue({state_id:''});
	}	
		
	if(id>0)
	{
		this.countryservice.getStates(id).subscribe(res => {
			if(res['status'])
			{
			 	this.stateList = res['data'];
				if(editS==0)
				{
					this.form.patchValue({state_id:''});
				}				
			}  
		});
	}	
  }


}

