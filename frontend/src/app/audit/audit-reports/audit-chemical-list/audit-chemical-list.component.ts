import { Component, OnInit,Input,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditChemicalList } from '@app/models/audit/audit-chemical-list';
import { AuditChemicalListService } from '@app/services/audit/audit-chemical-list.service';
import { CountryService } from '@app/services/country.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-audit-chemical-list',
  templateUrl: './audit-chemical-list.component.html',
  styleUrls: ['./audit-chemical-list.component.scss'],
  providers: [AuditChemicalListService]
})
export class AuditChemicalListComponent implements OnInit {
  @Input() cond_viewonly: any;
  title = 'Audit Report Chemical List'; 
  s_id=[1,2,3];
   
  form : FormGroup; 
  remarkForm : FormGroup; 
  chemicals$: Observable<AuditChemicalList[]>;
  total$: Observable<number>;
  id:number;
  audit_id:number;
  unit_id:number;
  standard_id:number;
  chemicalData:any;
  ChemicalData:any;
  msdslist:any;
  conformitylist:any;
  countryList:any;
  prooflist:any;
  sel_brand_ch:number;
  comply_file='';
  msds_file='';

  error:any;
  success:any;
  displayElement = false;
  buttonDisable = false;
  dataloaded = false;
  
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  modalss:any;
  loading:any=[];
  typeErrors='';
  isItApplicable=true;
  
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;
  standard_ids: any=[];
  chemFormData: FormData=new FormData();
  hcodeError: string='';
  complythreeError: string='';
  showgotgrs: boolean=false;
  showgots: boolean=false;
  showgrs: boolean=false;
  d22list: any;

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service:AuditChemicalListService, private countryservice: CountryService, public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
    this.chemicals$ = service.chemicals$;
    this.total$ = service.total$;
  }

  ngOnInit() 
  {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
    this.standard_id=this.activatedRoute.snapshot.queryParams.standard_id;
    this.form = this.fb.group({	
		trade_name:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]], 
		ingredient_name:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
    supplier_name:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
    version_name:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
    cas_no:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
    approval_no:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
    product_name:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
    suppier:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
		country_id:['',[Validators.required]],
		utilization:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
		proof:['',[Validators.required]],
		//type_of_conformity:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
		validity_or_issue_date:['',[Validators.required]],
    approval_date:['',[Validators.required]],
		msds_issued_date:['',[Validators.required,]],
		msds_available:['',[Validators.required]],
    comply_file :[''],
    msds_file:[''],
    complygots:['',[Validators.required]],
    complygrs:['',[Validators.required]],
    hcode_ch:['',[Validators.required]],
    complyone:['',[Validators.required]],
    complytwo:['',[Validators.required]],
    comply_ch:['',[Validators.required]],
    hcode_no:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
    // complythree:['',[Validators.required]],
		conformity_auditor:['',[Validators.required]],
		comments:['',[Validators.required,this.errorSummary.noWhitespaceValidator]]	  
    });
    // if(this.brand_file ='' && this.f.sel_brand_ch.value==1 ){
    //   this.brandFileError =='Please upload brand file';
    // }
	
    this.remarkForm = this.fb.group({	
      remark:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]]
    });
	
    this.service.getStandardIds({id:this.unit_id}).subscribe(res=>{
      if(res.status){
        this.standard_ids=res.data;
        if (this.standard_ids.length != 0 && (this.standard_ids.includes(1) && this.standard_ids.includes(3))){
          this.showgotgrs=true;
        }
        if(this.standard_ids.length!=0 && (this.standard_ids.includes(1) )){
          this.showgots=true;
        }
        if(this.standard_ids.length!=0 && ( this.standard_ids.includes(3))){
          this.showgrs=true;
        }
      }
      error => {
        this.error = error;
        this.loading['button'] = false;
    }
    });
    this.service.getOptionList().pipe(first())
    .subscribe(res => {    
      this.msdslist  = res.msdslist;
      this.d22list = res.D22list;
      this.conformitylist  = res.conformitylist;
      this.prooflist  = res.prooflist;
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });


    this.service.getRemarkData({audit_id:this.audit_id,standard_id:this.standard_id,unit_id:this.unit_id,type:'chemical_list'}).pipe(first())
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

    this.countryservice.getCountry().subscribe(res => {
      this.countryList = res['countries'];
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
  get rf() { return this.remarkForm.controls; }

  openmodal(content)
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  downloadFile(fileid='',filetype='',filename='')
  {
    this.service.downloadFile({id:fileid,filetype:filetype})
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
  
  chemicalIndex:number=null;
  addchemical()
  {
    let formerror=false;
    let expobject:any={};
    console.log(this.form.value);
    // this.f.trade_name.markAsTouched();
    // this.f.suppier.markAsTouched();
    // this.f.country_id.markAsTouched();
    // this.f.msds_available.markAsTouched();
    // this.f.utilization.markAsTouched();
    // this.f.conformity_auditor.markAsTouched();
    // this.f.proof.markAsTouched();
    // this.f.validity_or_issue_date.markAsTouched();
	  // this.f.comments.markAsTouched();  

      // let trade_name = this.form.get('trade_name').value;
      // let suppier = this.form.get('suppier').value;
      // let country_id = this.form.get('country_id').value;
      // let utilization = this.form.get('utilization').value;
      // let proof = this.form.get('proof').value;
      // let validity_or_issue_date = this.form.get('validity_or_issue_date').value?this.errorSummary.displayDateFormat(this.form.get('validity_or_issue_date').value):'';
      // let msds_available = this.form.get('msds_available').value;
      // let conformity_auditor = this.form.get('conformity_auditor').value;
      // let comments = this.form.get('comments').value;
      
      let ingredient_name = this.form.get('ingredient_name').value;
      let supplier_name = this.form.get('supplier_name').value;
      let product_name = this.form.get('product_name').value;
      let msds_issued_date = this.form.get('msds_issued_date').value?this.errorSummary.displayDateFormat(this.form.get('msds_issued_date').value):'';
      let complygots = this.form.get('complygots').value;
      let complygrs = this.form.get('complygrs').value;

      let version_name = this.form.get('version_name').value;
      let approval_no = this.form.get('approval_no').value;
      let approval_date = this.form.get('approval_date').value?this.errorSummary.displayDateFormat(this.form.get('approval_date').value):'';

      let cas_no = this.form.get('cas_no').value;
      let hcode = this.form.get('hcode_ch').value;
      let hcode_no = this.form.get('hcode_no').value;
      let complyone = this.form.get('complyone').value;
      let complytwo = this.form.get('complytwo').value;
      let complythree = this.form.get('comply_ch').value;

      // if(trade_name=='' || suppier=='' || country_id=='' || utilization=='' || proof=='' || validity_or_issue_date=='' || msds_available=='' || conformity_auditor=='' || comments==''){
      //   formerror=true;
      // } 
      if(this.standard_ids.length!=0 && (this.standard_ids.includes(1) && this.standard_ids.includes(3))){
        this.f.ingredient_name.markAsTouched();
        this.f.supplier_name.markAllAsTouched();
        this.f.product_name.markAsTouched();
        this.f.msds_issued_date.markAsTouched();
    
        if(ingredient_name=='' || supplier_name==''  || product_name=='' || msds_issued_date=='' ){
          formerror=true;
        }else if(this.msds_file=='' || this.msds_file===null){
          this.msdsFileError='Please the upload the MSDS file!';
          formerror=true;
        }

      } 
      if(this.standard_ids.length!=0 && this.standard_ids.includes(1)){
        this.f.version_name.markAsTouched();
        this.f.approval_no.markAsTouched();
        this.f.approval_date.markAsTouched();
        this.f.complygots.markAsTouched();
        

        if(version_name=='' || approval_no=='' || approval_date==''||  complygots==''){
          formerror=true;
        }
      }
       
      if(this.standard_ids.length!=0 && this.standard_ids.includes(3)){
        this.f.cas_no.markAsTouched();
        this.f.hcode_ch.markAsTouched();
        this.f.complyone.markAsTouched();
        this.f.complytwo.markAsTouched();
        this.f.comply_ch.markAsTouched();
        this.f.complygrs.markAsTouched();
        this.hcodeError='';
        this.complythreeError='';
        if(cas_no=='' || hcode==''  || complyone=='' || complytwo=='' || complythree=='' || complygrs==''){
          if(hcode==''){
            this.hcodeError='Please select is hcode identified?';
          }else if(complythree==''){
            this.complythreeError='Please select Comply D2.3?';
          }

          formerror=true;
        }else if(hcode==1 && complythree==1 && (this.comply_file=='' || this.comply_file===null)){
          this.complyFileError='Please upload Comply File';
          formerror=true;
        }else if(hcode==1){
         this.f.hcode_no.markAsTouched();
         if(hcode_no==''){
           formerror=true;
         }
            
        }
       
      }
 
    if(!formerror)
    {
      this.buttonDisable = true;
      this.loading['button'] = true;
      
      expobject={
        audit_id:this.audit_id,
        unit_id:this.unit_id,
        standard_id:this.standard_id,
        // trade_name:trade_name,
        // suppier:suppier,
        // country_id:country_id,
        // utilization:utilization,
        // proof:proof,
        // validity_or_issue_date:validity_or_issue_date,
        // msds_available:msds_available,
        // conformity_auditor:conformity_auditor,
        // comments:comments,

        ingredient_name:ingredient_name,
        supplier_name:supplier_name,
        product_name:product_name,
        msds_issued_date:msds_issued_date,
        complygots:complygots,
        complygrs:complygrs,

        version_name:version_name,
        approval_no:approval_no,
        approval_date:approval_date,
        
        cas_no:cas_no,
        hcode:hcode,
        hcode_no:hcode_no,
        complyone:complyone,
        complytwo:complytwo,
        complythree:complythree,
        type:'chemical_list'
      };
      
      if(1)
      {
        if(this.chemicalData)
        {
          expobject.id = this.chemicalData.id;
        }
        
        let formvalue = this.form.value;
        formvalue.chemical_data=expobject;
        this.chemFormData.append('formvalue',JSON.stringify(formvalue));
        this.service.addData(this.chemFormData)
        .pipe(first())
        .subscribe(res => {

        
            if(res.status){
              this.success = {summary:res.message};
              this.service.customSearch();
              this.chemicalFormreset();
              this.remarkFormreset();
              formvalue={};
              this.buttonDisable = false;
              this.msds_file='';
              this.comply_file='';
              
              
             
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


  viewChemical(content,data)
  {
    this.ChemicalData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  editStatus=0;
  editChemical(index:number,chemicaldata) 
  { 
    this.editStatus=1;
    this.success = {summary:''};
    this.chemicalData = chemicaldata;
    // this.form.patchValue({
    //   trade_name:chemicaldata.trade_name,
    //   suppier:chemicaldata.suppier,
    //   country_id:chemicaldata.country_id,
    //   utilization:chemicaldata.utilization,
    //   proof:chemicaldata.proof,
    //   validity_or_issue_date:chemicaldata.validity_or_issue_date?this.errorSummary.editDateFormat(chemicaldata.validity_or_issue_date):'',
    //   msds_available:chemicaldata.msds_available,
    //   conformity_auditor:chemicaldata.conformity_auditor,
    //   comments:chemicaldata.comments, 
    // });
    
      this.form.patchValue({
        ingredient_name: chemicaldata.ingredient_name,
        product_name: chemicaldata.utilization_product,
        supplier_name: chemicaldata.supplier_name,
        msds_issued_date:chemicaldata.msds_issued_date? this.errorSummary.editDateFormat(chemicaldata.msds_issued_date):'',
      });
      this.msds_file = chemicaldata.msds_file;
    
     if(this.standard_ids.length!=0 && (this.standard_ids.includes(1) )){
      this.form.patchValue({
        version_name: chemicaldata.gots_version,
        approval_no: chemicaldata.gots_approval_no,
        approval_date:chemicaldata.gots_approval_date? this.errorSummary.editDateFormat(chemicaldata.gots_approval_date):'',
        complygots: chemicaldata.comply_gots,
      });
    }
    if(this.standard_ids.length!=0 && (this.standard_ids.includes(3) )){
      this.form.patchValue({
        cas_no: chemicaldata.cas_no,
        hcode_ch: chemicaldata.is_hcode_identified,
        hcode_no:chemicaldata.hcode_no,
        complyone: chemicaldata.comply_d21,
        complytwo: chemicaldata.comply_d22,
        comply_ch : chemicaldata.comply_d23,
        complygrs: chemicaldata.comply_grs,
      });
      this.comply_file = chemicaldata.comply_file;
    }

    
    this.scrollToBottom();
  }


  removeChemical(content,index:number,chemicaldata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.chemicalFormreset();
        this.service.deleteData({id:chemicaldata.id})
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

  chemicalFormreset()
  {
    this.editStatus=0;
    
    this.chemicalData = '';  
    this.form.reset();
    
    this.form.patchValue({     
      trade_name:'',
      ingredient_name:'',
      supplier_name:'',   
      version_name:'',
      cas_no:'',
      approval_no:'',
      product_name:'',
      country_id:'',  
      proof:'',
      suppier:'',
      //type_of_conformity:'',
      validity_or_issue_date:'',
      approval_date:'',
      utilization:'',
      msds_issued_date:'',
      conformity_auditor:'',
      msds_available:'',
      hcode_ch:'',
      complyone:'',
      complytwo:'',
      complythree:'',
      complygots:'',
      complygrs:'',
      comments:''
    });

  }
  removecomplyFile(){
    this.comply_file = '';
    this.chemFormData.delete('comply_file');
  }
  complyFileError=''
  complyfileChange(element) {
    let files = element.target.files;
    this.complyFileError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.chemFormData.append("comply_file", files[0], files[0].name);
      this.comply_file = files[0].name;
      
    }else{
      this.complyFileError ='Please upload valid file';
    }
    element.target.value = '';
   
  }


  removemsdsFile(){
    this.msds_file = '';
    this.chemFormData.delete('msds_file');
  }
  msdsFileError=''
  msdsfileChange(element) {
    let files = element.target.files;
    this.msdsFileError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.chemFormData.append("msds_file", files[0], files[0].name);
      this.msds_file = files[0].name;
      
    }else{
      this.msdsFileError ='Please upload valid file';
    }
    element.target.value = '';
   
  }

  onSubmit(){ }

  scrollToBottom()
  {
    window.scroll({ 
      top: document.body.scrollHeight,
      left: 0, 
      behavior: 'smooth' 
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

      let expobject:any={unit_id:this.unit_id,standard_id:this.standard_id,audit_id:this.audit_id,comments:remark,is_applicable:this.isApplicable,type:'chemical_list'}

      this.service.addRemark(expobject)
      .pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.success = {summary:res.message};
          this.buttonDisable = false;
          this.loading['button'] = false;
          this.service.customSearch();
        }
      },
      error => {
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

}
