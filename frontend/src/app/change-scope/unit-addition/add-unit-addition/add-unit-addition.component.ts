import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';

import { ActivatedRoute ,Params, Router } from '@angular/router';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { Application } from '@app/models/application/application';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import { AuthenticationService } from '@app/services';
import {Observable,Subject} from 'rxjs';
import { first, debounceTime, distinctUntilChanged, map,tap } from 'rxjs/operators';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {saveAs} from 'file-saver';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Process } from '@app/models/master/process';
import { ProcessService } from '@app/services/master/process/process.service';
import { UnitAdditionService } from '@app/services/change-scope/unit-addition.service';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';

import { Country } from '@app/services/country';
import { State } from '@app/services/state';
import { CountryService } from '@app/services/country.service';
import { StandardService } from '@app/services/standard.service';
import { Standard } from '@app/services/standard';
import { BusinessSector } from '@app/models/master/business-sector';
import { BusinessSectorService } from '@app/services/master/business-sector/business-sector.service';
import { Units } from '@app/models/master/units';

@Component({
  selector: 'app-add-unit-addition',
  templateUrl: './add-unit-addition.component.html',
  styleUrls: ['./add-unit-addition.component.scss']
})
export class AddUnitAdditionComponent implements OnInit {

   constructor(private enquiryDetail:EnquiryDetailService, private userservice: UserService,private activatedRoute:ActivatedRoute,
    private applicationDetail:ApplicationDetailService, private modalService: NgbModal
    ,private router:Router,private authservice:AuthenticationService,public errorSummary: ErrorSummaryService, private processService:ProcessService,private additionservice: UnitAdditionService,private fb:FormBuilder,
	private countryservice: CountryService, private BusinessSectorService: BusinessSectorService) { 
    
     
    }
  form : FormGroup;	
  
  appform : any = {};
  processList: Process[];
  unitprocessList:any = [];
  process_ids=[];
  userdecoded:any;
  id:number;
  app_id:number;
  error:any;
  success:any;

  uniterror:any;
  unitsuccess:any;
  unitIndex:any;
  loading:any=[];
  applicationdata:any;
  panelOpenState = true;
  approvalStatusList = [];//[{id:'1',name:'Accept'},{id:'2',name:'Reject'}];
  userList:User[];
  modalss:any;
  processEntries:any=[];
  processerror:any=[];
  units:any;
  model:any = {process_ids:'',user_id:'',approver_user_id:'',status:'',comment:'',reject_comment:''};

  userType:number;
  userdetails:any;
  arrEnumStatus:any[];
  
  countryList:Country[];
  stateList:State[];
  bsectorList:BusinessSector[];
  company_unit_typeErrors:any;
  
  selStandardList:Array<any> = [];
  selUnitStandardList:Array<any> = [];
  selStandardIds:Array<any> = [];

  productEntries:any=[];
  productListDetails:any=[];
  new_app_id:number;
  standardList:Standard[];
  reductionstandardList:Standard[];
  unitType:any=[];
  redirecttype:any;
  maxDate = new Date();
  ngOnInit() {
  	this.app_id = this.activatedRoute.snapshot.queryParams.app;
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.new_app_id = this.activatedRoute.snapshot.queryParams.new_app_id;
    this.redirecttype = this.activatedRoute.snapshot.queryParams.redirecttype;

  	this.loadUnitList();
	
	  this.countryservice.getCountry().pipe(first()).subscribe(res => {
      this.countryList = res['countries'];
    });
	
	
	this.form = this.fb.group({
    unit_name:['',[Validators.required]],
    unit_address:['',[Validators.required]],
    unit_zipcode:['',[Validators.required]],
	  unit_country_id:['',[Validators.required]],	 	  
    unit_state_id:['',[Validators.required]],	
    unit_city:['',[Validators.required]],
	  no_of_employees:['',[Validators.required]],
	  business_sector_id:['',[Validators.required]],
	  sel_process:['',[Validators.required]],
	  sel_reduction:['2',[Validators.required]],
    unitstandardsChk:  this.fb.array([]),
    unit_product_id:['',[Validators.required]],
    sel_standard:[''],
    unit_id:[''],
    license_number:[''],
    expiry_date:[''],
  });


   

  }

  loadUnitList(){
    this.unitEntries = [];
    this.loading['data'] = true;
    let getappid:any;

    /*
    if(this.new_app_id === undefined || this.new_app_id=== null || this.new_app_id <=0){
      getappid = this.app_id
    }else{
      getappid = this.new_app_id;
    }
    */
    
    this.additionservice.getApplication({id:this.id,app_id:this.app_id,new_app_id:this.new_app_id})
    .pipe(first())
    .subscribe(resdata => {
      let res = resdata.applicationdata;
      this.standardList = resdata.standard;
      this.reductionstandardList = resdata.reductionstandard;
      this.processList = resdata.processList;
      this.unitType = resdata.unitType;
      this.applicationdata = resdata.applicationdata;
      res.standard_ids.forEach(val=>{
        //this.selStandardIds.push(""+val+"");
        //this.selStandardList = this.standardList.filter(x=>this.selStandardIds.includes(x.id));

      });
      this.selStandardList = res.standard_lists;

      this.productListDetails = res.productDetails;
      //console.log(this.productListDetails);
      this.productEntries = res.products;

      this.loading['data'] = false;
      
      if(this.id && res['new_units']){
       
       res['new_units'].forEach((val)=>{
          
          let processEntries:any= val.process_ids;
          let standardEntries:any= [];
          if(val.certified_standard && val.certified_standard.length>0){
            val.certified_standard.forEach((standard)=>{
              let expobject:any=[];
              
              let fname = [];
              standard.files.forEach(element => { 
                fname.push({name: element.name,added:0,deleted:0,type:element.type});
              });

              expobject["id"] = standard.id;
              expobject["name"] = standard.standard;//this.registrationForm.get('expname').value;
              expobject["uploadedFiles"] = fname;
              expobject["uploadedFileNames"] = fname;
              expobject["license_number"] = standard.license_number;
              expobject["expiry_date"] = standard.expiry_date;
              
              standardEntries.push(expobject);
            })
          }
          /*
          if(val.standards && val.standards.length>0)
          {
            let standardvals=val.standards;
            this.BusinessSectorService.getBusinessSectorsbystds({standardvals}).subscribe(res => {
              this.bsectorList = res['bsectors'];
            }); 
          }
          */

           
          let unitProductList= [...val.product_details];
          
          let expobject:Units;
          expobject = {
            "unit_type":val.unit_type,
            "unit_name": val.name,
            "unit_id": val.id,

            "unit_address":val.address,
            "unit_zipcode":val.zipcode,
            "unit_country_id":val.country_id,
            "unit_state_id":val.state_id,
            "business_sector_id":val.bsector_ids,
            "bsectorList":val.bsector_data,
            "unitProductList":unitProductList,

            "unit_country_name":val.country_id_name,
            "unit_state_name":val.state_id_name,
            "unit_city":val.city,
            "no_of_employees":val.no_of_employees,
            
            "sel_standard":standardEntries,
            "sel_process":processEntries,
           
            "unitStateList": val.state_list,
            "selUnitStandardList":val.standards
            
          }
          
          this.unitEntries.push(expobject);
          
          standardEntries = [];
          processEntries = [];

        })
        
      }
      
      
    },
    error => {
        this.error = {summary:error};
        this.loading['data'] = false;
    });
  }
  removeProcess(processId:number) {
    let index= this.processEntries.findIndex(s => s.id ==  processId);
    if(index !== -1)
      this.processEntries.splice(index,1);
  }
  processErrors:any;
  addProcess(){
    let processId = this.form.get('sel_process').value;
    let selprocess = this.processList.find(s => s.id ==  processId);
    this.processErrors = '';
    
    this.f.sel_process.setValidators([Validators.required]);
    this.f.sel_process.updateValueAndValidity();

    this.f.sel_process.markAsTouched();
    
    if(this.f.sel_process.errors){
      return false;
    }
    
    let entry= this.processEntries.find(s => s.id ==  processId);
    if(entry === undefined){
      let expobject:any=[];
      expobject["id"] = selprocess.id;
      expobject["name"] = selprocess.name;//this.registrationForm.get('expname').value;
      this.processEntries.push(expobject);
    }
    
    this.f.sel_process.setValidators(null);
    this.f.sel_process.updateValueAndValidity();

    this.form.patchValue({
      sel_process: '',
    });
  }

  downloadCompanyFile(filename){
    this.enquiryDetail.downloadCompanyFile({id:this.id})
    .subscribe(res => {
      
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
      this.modalss.close('');
    });
  }

  open(content) {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  
  buttonDisable=false;
  
  showunit =false;
  setshowUnit(){
    if(this.showunit){
      this.showunit = false;
    }else{
      this.showunit = true;
    }
  }
  getStateList(id:number,stateid=''){
    
    this.stateList = [];
	  this.form.patchValue({state_id:''});
    this.loading['state'] = 1;
        
    this.countryservice.getStates(id).pipe(first()).subscribe(res => {       
        this.stateList = res['data'];
        this.loading['state'] = 0;       
    });    
  }
  
  filterProduct(){
    
    let appstandards = this.selUnitStandardList.map(String);
    
    if(appstandards.length>0){
     
      return this.productListDetails.filter(x =>  appstandards.includes(""+x.standard_id+""));
    }
    
  }
  

  onUnitStandardChange(id: number, isChecked: boolean) {
    
    let standardDetails = this.selStandardList.find(x => x.id == id);
    
    const standardsFormArray = <FormArray>this.form.get('unitstandardsChk');
    if(isChecked){
      standardsFormArray.push(new FormControl(id));
      this.selUnitStandardList.push(standardDetails.id);

    } else {
      let index = standardsFormArray.controls.findIndex(x => x.value == id);
      if(index !== -1){
        standardsFormArray.removeAt(index);
      }

      this.selUnitStandardList = this.selUnitStandardList.filter(x => x != id);
    }
    this.emptyUnitPrdStd();
  }
  emptyUnitPrdStd(){
    this.unitProductList = [];
  }



  /*
  Unit Product Section
  */
  removeUnit(content,unit_id:number) {
    

      
      this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
      
      this.modalss.result.then((result) => {
         this.loading['button'] = true;
         this.buttonDisable = true;   
         this.showunit = false;
        this.additionservice.deleteData({unit_id:unit_id})
        .pipe(first())
        .subscribe(res => {

            if(res.status){
              this.emptyUnits();
              this.unitsuccess = {summary:res.message};
              this.buttonDisable = true;           
              setTimeout(() => {
                this.loading['button'] = false;
                this.unitsuccess = {summary:''};
                this.loadUnitList();
              }, this.errorSummary.redirectTime);           
            }else{
              this.buttonDisable = false;
              this.loading['button'] = false;
              //this.submittedError =1;
              this.uniterror = {summary:res};
            }
            
           
        },
        error => {
            this.uniterror = {summary:error};
            this.loading['button'] = false;
            this.buttonDisable = false;
        });
        
      }, (reason) => {
         
      });
    

    
  }

  submitForAddition(content){
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
      
    this.modalss.result.then((result) => {
      this.loading['button'] = true;
      this.additionservice.submitForAddition({id:this.id,app_id:this.app_id,new_app_id:this.new_app_id})
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.emptyUnits();
            this.unitsuccess = {summary:res.message};
            this.buttonDisable = true;           
            setTimeout(() => {
              this.router.navigateByUrl('/application/apps/view?id='+res.new_app_id); 
              //this.router.navigateByUrl('/change-scope/unit-addition/list');
            }, this.errorSummary.redirectTime);           
          }else{
            this.buttonDisable = false;
            this.loading['button'] = false;
            //this.submittedError =1;
            this.uniterror = {summary:res};
          }
          
         
      },
      error => {
          this.uniterror = {summary:error};
          this.loading['button'] = false;
          this.buttonDisable = false;
      });
      
    }, (reason) => {
       
    });
  }


  unitproductErrors = '';
  unitProductList:any=[];
  removeUnitProduct(pdtindex:number) {
    this.unitProductList.splice(pdtindex,1);
  }
  addUnitProduct(){
    let unitProductIndex = this.form.get('unit_product_id').value;
    let pdtdetails = this.productListDetails.find(x=>x.pdt_autoid ==unitProductIndex );
    let selunitproduct = {...pdtdetails,'pdtListIndex':unitProductIndex};
    

    this.f.unit_product_id.setValidators([Validators.required]);
    this.f.unit_product_id.updateValueAndValidity();
    this.f.unit_product_id.markAsTouched();
    
    if(this.f.unit_product_id.errors){
      return false;
    }
    
    this.unitproductErrors='';
    let entry= this.unitProductList.find(s => s.pdt_autoid ==  unitProductIndex);
    if(entry === undefined){
      this.unitProductList.push(selunitproduct);
    }
    
    this.f.unit_product_id.setValidators(null);
    this.f.unit_product_id.updateValueAndValidity();
    
    this.form.patchValue({
      unit_product_id: '',
    });
  }

  unitypename = '';
  currentunittype:number=0;
  setfacilityunit(event,value){
    this.currentunittype = value;
    if(value==2){
      this.unitypename = 'Facility';
    }else if(value==3){
      this.unitypename = 'Subcontractor';
    }else if(value==1){
      this.unitypename = 'Scope Holder';
    }else{
      this.unitypename = '';
    }
  }

   getBsectorList(reqType="unit"){
    let standardvals=[];
    if(reqType =="main"){
      this.selStandardList.forEach(val=>{
        standardvals.push(val.id);
      })
    }else{
      standardvals=  [...this.selUnitStandardList];//this.standardsChkDb.concat(this.form.get('standardsChk').value);
    }
    
    
    if(standardvals.length>0)
    {
      this.BusinessSectorService.getBusinessSectorsbystds({standardvals}).subscribe(res => {
        this.bsectorList = res['bsectors'];
        this.form.patchValue({business_sector_id:''});
        
      }); 
    }else{    
      this.bsectorList = [];
      this.form.patchValue({business_sector_id:''});   
      
    }
    
  }

  getSelectedValue(type,val)
  {
    if(type=='business_sector_id')
    {
      return this.bsectorList.find(x=> x.id==val).name;
    }
  }
  unitstandard_error = '';
  addstandard_error = '';
  uploadedFiles:any = [];
  uploadedFileNames:any=[];
  addauditstandard_error = '';
  formData:FormData = new FormData();
  standardEntries:any[]=[];
  fileChange(element,type) {
    
    let standardId = this.form.get('sel_standard').value;
    if(standardId ==''){
      this.unitstandard_error='Please select standard to upload files';
      element.target.value = '';
      return false;
    }
    this.unitstandard_error='';
    this.addstandard_error='';
    this.addauditstandard_error='';

    let filesadded = this.uploadedFileNames.filter(x=>x.deleted==0 && x.type==type);
    if(filesadded.length>0){

      if(type == 'cert'){
        this.addstandard_error='Please remove the file to add new files';
        element.target.value = '';
        return false;
      }
      if(type == 'audit'){
        this.addauditstandard_error='Please remove the file to add new files';
        element.target.value = '';
        return false;
      }
    }

    let files = element.target.files;
    for (let i = 0; i < files.length; i++) {
      
      let fileextension = files[i].name.split('.').pop();
      if(this.errorSummary.checkValidDocs(fileextension))
      {
        this.uploadedFiles.push(files[i]);
        this.uploadedFileNames.push({name:files[i].name,added:1,deleted:0,type});
      }else{
        if(type == 'cert'){
          this.addstandard_error='Please upload valid file';
          element.target.value = '';
          return false;
        }
        if(type == 'audit'){
          this.addauditstandard_error='Please upload valid file';
          element.target.value = '';
          return false;
        }
        return false;
      }
      
    }
    
    
    
    
    for (let i = 0; i < files.length; i++) {
      this.formData.append("uploads["+standardId+"]["+type+"]", files[i], files[i].name);
    }
    element.target.value = '';
  }

  removeFiles(index,type){
    let filenames =  this.uploadedFileNames.map(x => {
      //return num * 2;
      if(x.deleted==0 && x.type==type){
        x.deleted=1;
      }
      return x;
    });
    
    this.uploadedFileNames = filenames;
    
    this.addstandard_error = '';
    this.addauditstandard_error = '';
  }
  get filterFile(){
    return this.uploadedFileNames.filter(x=>x.deleted==0 && x.type=='cert');
  }
  get auditfilterFile(){
    return this.uploadedFileNames.filter(x=>x.deleted==0 && x.type=='audit');
  }
  get standardFiles(){
    return this.uploadedFileNames.filter(x=>x.deleted==0);
  }
  filterItemsOfType(uploadedfiles){
    return uploadedfiles.filter(x=>x.deleted==0);
  }

  license_number_error:any='';
  expiry_date_error:any = '';

  addStandard(){
    let standardId = this.form.get('sel_standard').value;
    let license_number:any = this.form.get('license_number').value;
    let expiry_date:any = this.form.get('expiry_date').value;

    //let selstandard = this.standardList.find(s => s.id ==  standardId);
    let selstandard = this.reductionstandardList.find(s => s.id ==  standardId);
    
    this.unitstandard_error = '';
    this.addstandard_error = '';
    this.license_number_error = '';
    this.expiry_date_error = '';
    let stdadderror = false;
    if(standardId==''){
      this.unitstandard_error = 'Please select the standard';
      stdadderror = true;
      return false;
    }
    if(this.reductionStandardDetails.includes('license_number') && license_number==''){
      this.license_number_error = 'Please enter the license number';
      stdadderror = true;
      //return false;
    }
    if(this.reductionStandardDetails.includes('expiry_date') && expiry_date==''){
      this.expiry_date_error = 'Please enter the expiry date';
      stdadderror = true;
    }
    this.addstandard_error='';
    this.addauditstandard_error='';
    

    
    let curuploadfiles = this.uploadedFileNames.filter(val=>val.deleted ==0 && val.type=='cert');
    if(this.reductionStandardDetails.includes('certificate_file') && curuploadfiles.length <=0){
      this.addstandard_error = 'Please upload certification file';
      stdadderror = true;
    }
    let curaudituploadfiles = this.uploadedFileNames.filter(val=>val.deleted ==0 && val.type=='audit');
    if(this.reductionStandardDetails.includes('latest_audit_report') && curaudituploadfiles.length <=0){
      this.addauditstandard_error = 'Please upload latest audit report file';
      stdadderror = true;
    }
    if(stdadderror){
      return false;
    }
    
    
    let entry= this.standardEntries.findIndex(s => s.id ==  standardId);

    let expobject:any=[];
    expobject["id"] = selstandard.id;
    expobject["name"] = selstandard.name;//this.registrationForm.get('expname').value;
    expobject["uploadedFiles"] = this.uploadedFiles;
    expobject["uploadedFileNames"] = this.uploadedFileNames;
    expobject["license_number"] = license_number;
    expobject["expiry_date"] = expiry_date?this.errorSummary.displayDateFormat(expiry_date):'';

    this.uploadedFiles = [];
    this.uploadedFileNames=[];

    if(entry === -1){
      this.standardEntries.push(expobject);
    }else{
      this.standardEntries[entry] = expobject;
    }
    this.reductionStandardDetails = [];
    this.form.patchValue({
      sel_standard: '',
      license_number: '',
      expiry_date: '',
    });
  }
  editStandard(standardId:any){
    let reductionStandardData = this.reductionstandardList.find(x=>x.id == standardId);
    if(reductionStandardData !== undefined){
      this.reductionStandardDetails = [...reductionStandardData.required_fields];
    }else{
      this.reductionStandardDetails = [];
    }

    let prd= this.standardEntries.find(s => s.id ==  standardId);
    this.uploadedFiles= [];
    this.uploadedFileNames = [];
   
    for (let i = 0; i < prd['uploadedFiles'].length; i++) {
      this.uploadedFiles.push(prd['uploadedFiles'][i]);
      this.uploadedFileNames.push({name:prd['uploadedFiles'][i].name,added:prd['uploadedFileNames'][i].added,deleted:prd['uploadedFileNames'][i].deleted,type:prd['uploadedFileNames'][i].type});
      
    }
   
    this.form.patchValue({
      sel_standard: prd.id,
      license_number: prd.license_number,
      expiry_date: prd.expiry_date?this.errorSummary.editDateFormat(prd.expiry_date):'',
    });
  }
  removeStandard(standardId:any) {
    let index= this.standardEntries.findIndex(s => s.id ==  standardId);
    if(index !== -1)
      this.standardEntries.splice(index,1);
    
    //console.log(this.unitEntries[this.unitIndex]);
  }

  appunitstandardErrors= '';
  unitErrors='';
  addUnit(type){
    
    
    let unit_id = this.f.unit_id.value;
    let unit_name = this.f.unit_name.value;
    let unit_address = this.f.unit_address.value;
    let unit_zipcode = this.f.unit_zipcode.value;
    let unit_country_id = this.f.unit_country_id.value;
    let unit_state_id = this.f.unit_state_id.value;
    let unit_city = this.f.unit_city.value;
    let no_of_employees = this.f.no_of_employees.value;
    let business_sector_id = this.f.business_sector_id.value;
    let sel_reduction = this.f.sel_reduction.value;
    this.touchUnit();

    this.processErrors = '';
    if(this.processEntries.length <=0){
      this.processErrors = 'Please add process';
    }
    
    this.appunitstandardErrors = '';
    if(this.selUnitStandardList.length<=0 && this.currentunittype!=1){
      this.appunitstandardErrors = 'Please select the standard';
    }
    
    this.company_unit_typeErrors = '';
    if(!this.currentunittype){
      this.company_unit_typeErrors = 'Please select facility type';
    }
    this.unitproductErrors = '';
    if(this.unitProductList.length <=0){
      this.unitproductErrors = 'Please add product';
    }
    
    if(this.unitproductErrors || unit_name =='' || unit_address=='' || unit_zipcode=='' || unit_country_id=='' || unit_state_id=='' || unit_city=='' || no_of_employees=='' || business_sector_id=='' || this.appunitstandardErrors!='' || this.processErrors!='' || this.f.no_of_employees.errors || this.company_unit_typeErrors !=''
    ){
      this.error = {summary:this.errorSummary.errorSummaryText};
       return false;
    }
   
    
    
    this.unitErrors = '';
    
    
     let countrysel = this.countryList.find(s => s.id ==  unit_country_id);
     let statesel = this.stateList.find(s => s.id ==  unit_state_id);
    
    
      


       //processDataEntries.push({id:val.id})
      let processDataEntries = [];
      let standardDataEntries = [];
      let bsectorList = [];

      this.processEntries.forEach((val)=>{
        processDataEntries.push(val.id); 
      });

      business_sector_id.forEach((val)=>{
        bsectorList.push(val);
      });

      this.standardEntries.forEach((valstds)=>{
        let upfilesArr= [];
        let upfiles = valstds.uploadedFiles;
        let upfilesdetails = valstds.uploadedFileNames;
        for (let i = 0; i < upfiles.length; i++) {
          upfilesArr.push({name:upfiles[i].name,added:upfilesdetails[i].added,deleted:upfilesdetails[i].deleted,type:upfilesdetails[i].type});
        }
        standardDataEntries.push({standard:valstds.id,files:upfilesArr,license_number:valstds.license_number,expiry_date:valstds.expiry_date});
      });
      let expobjectdataunit = {
        "unit_id" : unit_id,
        "unit_type":this.currentunittype,
        "name": unit_name,

        "address":unit_address,
        "zipcode":unit_zipcode,
        "country_id":unit_country_id,
        "state_id":unit_state_id,
        "products":this.unitProductList,
        
         
        "city":unit_city,
        "no_of_employees":no_of_employees,
        "business_sector_id":bsectorList,
        "sel_reduction":sel_reduction,
        "certified_standard":sel_reduction==1?standardDataEntries:[],
        "processes":processDataEntries,
        "standards":this.selUnitStandardList,
      }







    if (1) {
      

 
      this.buttonDisable = true;       
      this.loading['button'] = true;
     
      let expobjectdata:any={};
      expobjectdata['units'] =[];
      expobjectdata['units'].push(expobjectdataunit);
      expobjectdata['app_id'] = this.app_id;
      expobjectdata['new_app_id'] = this.new_app_id;      
      expobjectdata['type'] = type;
      expobjectdata['id'] = this.id;
       
      

      this.formData.append('formvalues',JSON.stringify(expobjectdata));

      this.additionservice.updateAppUnitData(this.formData)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            this.emptyUnits();
            this.success = {summary:res.message};
            this.buttonDisable = true;           
            setTimeout(() => {
              if(type == 'draft'){
                
                this.id = res.id;
                this.app_id = res.app_id;
                this.loadUnitList();
                this.showunit = false;
                //this.router.navigateByUrl('/change-scope/unit-addition/add?app='+res.app_id+'&id='+res.id); 
                //this.router.navigateByUrl('/change-scope/unit-addition/list'); 
              }else{
                this.router.navigateByUrl('/application/apps/view?id='+res.new_app_id); 
              }
              
            }, this.errorSummary.redirectTime);           
          }else{
          this.buttonDisable = false;
            //this.submittedError =1;
            this.error = {summary:res};
          }
          this.loading['button'] = false;
         
      },
      error => {
          this.error = {summary:error};
          this.loading['button'] = false;
          this.buttonDisable = false;
      });
      

    }else {
      this.error = {summary:this.errorSummary.errorSummaryText};
      //this.errorSummary.validateAllFormFields(this.form); 
      
    }

      //unitIndex
      
    
      
  }

  unitEntries:Units[] = [];
  editUnit(index:number){
    this.showunit = true;
    let unit_data = this.unitEntries[index];
    
    this.processEntries = [...unit_data['sel_process']];
    this.standardEntries = [...unit_data['sel_standard']];
    
    let selUnitStandardList = unit_data['selUnitStandardList'].map(String);

    this.selUnitStandardList = selUnitStandardList;//['1','3'];//[...unit_data['selUnitStandardList']];
    this.unitProductList = [...unit_data['unitProductList']];
    this.bsectorList = [...unit_data['bsectorList']];
    
    this.getStateList(unit_data['unit_country_id']);
    this.currentunittype = unit_data['unit_type'];
    
    this.setfacilityunit('',unit_data['unit_type']);
    let sel_reduction = '2';
    if(this.standardEntries.length>0){
      sel_reduction ='1';
    }
    this.form.patchValue({
      unit_id: unit_data['unit_id'],
      unit_name: unit_data['unit_name'],
      unit_address: unit_data['unit_address'],
      unit_zipcode: unit_data['unit_zipcode'],
      unit_country_id: unit_data['unit_country_id'],
      unit_state_id: unit_data['unit_state_id'],
      unit_city: unit_data['unit_city'],
      no_of_employees: unit_data['no_of_employees'],
      business_sector_id: unit_data['business_sector_id'].map(String),
      sel_reduction:sel_reduction
    });
    this.reductionStandardDetails = [];
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

  emptyUnits(){
    this.formData = new FormData();
    this.standardEntries = [];
    this.processEntries = [];
    this.uploadedFiles= [];
    this.uploadedFileNames = [];
    this.unitstandard_error = '';
    this.selUnitStandardList = [];
    
    this.appunitstandardErrors = '';
    this.company_unit_typeErrors = '';
    this.unitProductList = [];

    this.bsectorList=[];
    this.form.reset();
    this.currentunittype =0;
    this.form.patchValue({
      sel_reduction:'2',
      unit_country_id: '',
      unit_state_id: '',
      unit_product_id: '',
      sel_process:'',
      license_number:'',
      expiry_date:''
    });
    
  }

  touchUnit(){
    this.f.unit_name.markAsTouched();
    this.f.unit_address.markAsTouched();
    this.f.unit_zipcode.markAsTouched();
    this.f.unit_country_id.markAsTouched();
    this.f.unit_state_id.markAsTouched();
    this.f.unit_city.markAsTouched();
    this.f.no_of_employees.markAsTouched();
    this.f.unit_product_id.markAsTouched();
    this.f.business_sector_id.markAsTouched();
  }

  get f() { return this.form.controls; } 
  //loading:any=[];
  addData()
  {
   
  }


  selectedProductIds:any = [];
  onProductCheckboxChange(id: number, isChecked: boolean) {
    if (isChecked) {
      this.selectedProductIds.push(id);
    } else {
      let index = this.selectedProductIds.findIndex(x => x == id);
      if(index !== -1){
        this.selectedProductIds.removeAt(index);
      }
    }

  }
  filterProductStandard(stdId){
    const unitProductIndex = this.unitProductList.map(x=>x.pdt_index).map(String);
    return this.productListDetails.filter(x =>  stdId==x.standard_id && !unitProductIndex.includes(""+x.pdt_index+"")  );
    
  }
  unitproductremainingstatus=true;
  selProductStandardList:Array<any> = [];
  logsuccess:any;
  addUnitProductPop(content)
  {	
    if(this.productListDetails && this.productListDetails.length>0){
      this.productListDetails.forEach(pdtdata=>{
        this.popunitproductlist['input_weight'+pdtdata.pdt_index] = false;
      })
    }

    if(this.currentunittype==1)
    {		
      this.selProductStandardList=this.selStandardIds;
     // 
    }else{
      this.selProductStandardList=this.selUnitStandardList;
      //
    }
    
    this.unitproductremainingstatus=true;
    let productfilters = this.filterProduct();
    if(productfilters && productfilters.length==this.unitProductList.length)
    {
      this.unitproductremainingstatus=false;
    }
 
    this.selectedProductIds = [];
 
    this.logsuccess = false;
    
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title',centered: true});
  }
  
  getStandardName(stdId:number){
    let std= this.standardList.find(s => s.id ==  stdId);
    return std.name;
  }
  
 
  productpopupsuccess:any;
  productpopuperror:any;
  popunitproductlist:any = [];
  addUnitProductFromPop(){
    		
	this.productpopuperror='';
    if(this.selectedProductIds.length<=0){
      this.productpopuperror = {summary:"Please select the product"};
      return false;
    }  
   
    this.selectedProductIds.forEach(pdt=>{
      let selunitproduct = this.productListDetails.find(s => s.pdt_index ==  pdt); 
      let entry= this.unitProductList.find(s => s.pdt_index ==  pdt);
      if(entry === undefined){
        //this.unitProductList.push(selunitproduct);
        this.unitProductList.push({...selunitproduct,addition_type:1});
      }
    });
	
	this.modalss.close();   
  }


  reductionStandardDetails:any = [];
  certifiedstandardChange(standardId:any){
    let reductionStandardData = this.reductionstandardList.find(x=>x.id == standardId);
    if(reductionStandardData !== undefined){
      this.reductionStandardDetails = [...reductionStandardData.required_fields];
    }else{
      this.reductionStandardDetails = [];
    }
  }
}




