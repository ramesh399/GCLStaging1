import { Component, OnInit,Input,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditSampling } from '@app/models/audit/audit-sampling';
import { AuditSamplingService } from '@app/services/audit/audit-sampling.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-audit-sampling',
  templateUrl: './audit-sampling.component.html',
  styleUrls: ['./audit-sampling.component.scss'],
  providers: [AuditSamplingService]
})
export class AuditSamplingComponent implements OnInit {
  @Input() cond_viewonly: any;
  title = 'Audit Report Sampling List'; 
  form : FormGroup; 
  sampleForm: FormGroup;
  remarkForm : FormGroup; 

  samplings$: Observable<AuditSampling[]>;
  total$: Observable<number>;
  id:number;
  audit_id:number;
  unit_id:number;
  samplingData:any;
  SamplingData:any;
  sampledata:any;
  suspicionlist:any;
  error:any;
  success:any;
  buttonDisable = false;
  dataloaded = false;
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  modalss:any;
  loading:any=[];
  sampleEntries:any=[];
  isItApplicable=true;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service:AuditSamplingService, public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
    //this.samplings$ = service.samplings$;
    //this.total$ = service.total$;
  }

  ngOnInit() 
  {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
    this.form = this.fb.group({	
      operator_title:['',[Validators.required ,Validators.maxLength(255)]], 
      sampling_date:['',[Validators.required]],
      operator_responsible_person:['',[Validators.required ,Validators.maxLength(255)]],
      sample_no:['',[Validators.required ,Validators.maxLength(255)]],
      staff_who_took_sample:['',[Validators.required ,Validators.maxLength(255)]],
      type_of_samples:['',[Validators.required ,Validators.maxLength(255)]],
      samples_were_taken_from:['',[Validators.required ,Validators.maxLength(255)]],
       
      //processing_line:['',[Validators.required,Validators.maxLength(255)]],
      //other_such_as_market:['',[Validators.required,Validators.maxLength(255)]],
      number_of_sub_samples_per_sample:['',[Validators.required, Validators.pattern("^[0-9]*$"),Validators.maxLength(255)]],
      describe_other_details_of_sampling_method:['',[Validators.required ,Validators.maxLength(255)]],
      samples_were_taken_based_on_a_specific_suspicion:['',[Validators.required]],
      reason:[''],
      representative_sealed:['',[Validators.required ,Validators.maxLength(255)]],
      representative_unsealed:['',[Validators.required ,Validators.maxLength(255)]],
      representative_sample_bag_number:['',[Validators.required ,Validators.maxLength(255)]],
      operator_sealed:['',[Validators.required,Validators.maxLength(255)]],
      operator_unsealed:['',[Validators.required ,Validators.maxLength(255)]],
      operator_sample_bag_number:['',[Validators.required ,Validators.maxLength(255)]],
      further_comments:['',[Validators.required]],
      sample_number:['',[]],
      taken_from:['',[]],
    });
	
    this.remarkForm = this.fb.group({	
      remark:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]]
    });
	
    /*
    this.sampleForm = this.fb.group({
      sample_number:['',[Validators.required, Validators.pattern("^[0-9]*$"),Validators.maxLength(255)]],
      taken_from:['',[Validators.required,Validators.maxLength(255)]],
    });
    */

    

    this.service.getOptionList().pipe(first())
    .subscribe(res => {    
      this.suspicionlist  = res.suspicionlist;
    },
    error => {
        this.error = error;
        this.loading['button'] = false;
    });


    this.service.getRemarkData({audit_id:this.audit_id,unit_id:this.unit_id,type:'sampling_list'}).pipe(first())
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

    this.loadDetails();
    
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
  //get sf() { return this.sampleForm.controls; }
  samplingdata:any = [];
  loadDetails()
  {
    this.service.getsamplingdata({audit_id:this.audit_id,unit_id:this.unit_id}).pipe(first())
    .subscribe(res => {    
      //this.interviewrequirements = res.samplings;
      this.dataloaded = true;

      let samplingdata:any = res.samplings;
      this.samplingdata = samplingdata;
      //console.log(1);
      if(Object.values(samplingdata).length>0){
        this.editStatus = 1;
        //console.log(typeof samplingdata);
        this.form.patchValue({
          operator_title:samplingdata.operator_title,
          sampling_date:this.errorSummary.editDateFormat(samplingdata.sampling_date),     
          operator_responsible_person:samplingdata.operator_responsible_person,
          sample_no:samplingdata.sample_no,
          staff_who_took_sample:samplingdata.staff_who_took_sample,
          type_of_samples:samplingdata.type_of_samples,
          samples_were_taken_from:samplingdata.samples_were_taken_from,
           
          //processing_line:samplingdata.processing_line,
          //other_such_as_market:samplingdata.other_such_as_market,
          number_of_sub_samples_per_sample:samplingdata.number_of_sub_samples_per_sample,
          describe_other_details_of_sampling_method:samplingdata.describe_other_details_of_sampling_method,
          samples_were_taken_based_on_a_specific_suspicion:samplingdata.samples_were_taken_based_on_a_specific_suspicion,
          reason:samplingdata.reason,
          representative_sealed:samplingdata.representative_sealed,
          representative_unsealed:samplingdata.representative_unsealed,
          representative_sample_bag_number:samplingdata.representative_sample_bag_number,
          operator_sealed:samplingdata.operator_sealed,
          operator_unsealed:samplingdata.operator_unsealed,
          operator_sample_bag_number:samplingdata.operator_sample_bag_number,
          further_comments:samplingdata.further_comments
        });
      } 
      
      
      if(samplingdata.sampling_list && samplingdata.sampling_list.length>0){
        this.sampleEntries = [...samplingdata.sampling_list];
        //samplingdata.sampling_list.forEach(xval => {
          
        //});
      } 
      
    });
  }

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  sample_number_error:any;
  taken_from_error:any;
  addSample()
  {
    //this.sampledata = '';
    //this.sampleForm.reset();
	
	  //this.sample_number_error =false;
    //this.taken_from_error =false;
    this.f.sample_number.setValidators([Validators.required]);
    this.f.taken_from.setValidators([Validators.required]);
    
    this.f.sample_number.updateValueAndValidity();
    this.f.taken_from.updateValueAndValidity();
    this.f.sample_number.markAsTouched();
    this.f.taken_from.markAsTouched();
    if(this.f.sample_number.errors || this.f.taken_from.errors){
      return false;
    }

    let sample_number = this.form.get('sample_number').value;
    let taken_from = this.form.get('taken_from').value;

    let expobject = {sample_number,taken_from};
    if(this.sampleindex === undefined){
      this.sampleEntries.push(expobject);
    }else{
      this.sampleEntries[this.sampleindex] = expobject;
    }

    this.sampleindex = undefined;
    this.editSampleStatus=0;  
    this.form.patchValue({
      sample_number: '',
      taken_from:''
    });
    this.f.sample_number.setValidators([]);
    this.f.taken_from.setValidators([]);
    
    this.f.sample_number.updateValueAndValidity();
    this.f.taken_from.updateValueAndValidity();
    this.f.sample_number.markAsTouched();
    this.f.taken_from.markAsTouched();
    //this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }


  editSampleStatus=0;
  sampleindex:any;
  editSample(index:number) 
  {
	
    this.sample_number_error =false;
    this.taken_from_error =false;
    this.sampleindex = index;
	  this.editSampleStatus=1;  
    //this.sampledata = sampledata;
    let sampledata:any = this.sampleEntries[index];
	
    
    this.form.patchValue({
      sample_number:sampledata.sample_number,  
      taken_from:sampledata.taken_from
    });
    this.f.sample_number.setValidators([Validators.required]);
    this.f.taken_from.setValidators([Validators.required]);
    
    this.f.sample_number.updateValueAndValidity();
    this.f.taken_from.updateValueAndValidity();
    this.f.sample_number.markAsTouched();
    this.f.taken_from.markAsTouched();
    //this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }


  
  removeSample(index) {
    //let index= this.productMaterialList.findIndex(s => s.material_id ==  Id);
    this.sampleindex = undefined;
    this.form.patchValue({
      sample_number: '',
      taken_from:''
    });
    this.f.sample_number.setValidators([]);
    this.f.taken_from.setValidators([]);
    
    this.f.sample_number.updateValueAndValidity();
    this.f.taken_from.updateValueAndValidity();
    this.f.sample_number.markAsTouched();
    this.f.taken_from.markAsTouched();

    if(index != -1)
      this.sampleEntries.splice(index,1);
    //this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

 
  logsuccess:any;
  logerror:any;
  logloading:any;
  submitLogAction()
  {
    
      
      
  }

  samplingIndex:number=null;
  //errorSummary.editDateFormat()
  addsampling()
  {
    this.f.operator_title.markAsTouched();
    this.f.sampling_date.markAsTouched();
    this.f.operator_responsible_person.markAsTouched();
    this.f.sample_no.markAsTouched();
    this.f.staff_who_took_sample.markAsTouched();
    this.f.type_of_samples.markAsTouched();
    this.f.samples_were_taken_from.markAsTouched();
     
    //this.f.processing_line.markAsTouched();
    //this.f.other_such_as_market.markAsTouched();
    this.f.number_of_sub_samples_per_sample.markAsTouched();
    this.f.describe_other_details_of_sampling_method.markAsTouched();
    this.f.samples_were_taken_based_on_a_specific_suspicion.markAsTouched();
    //this.f.reason.markAsTouched();
    this.f.representative_sealed.markAsTouched();
    this.f.representative_unsealed.markAsTouched();
    this.f.representative_sample_bag_number.markAsTouched();
    this.f.operator_sealed.markAsTouched();
    this.f.operator_unsealed.markAsTouched();
    this.f.operator_sample_bag_number.markAsTouched();
    this.f.further_comments.markAsTouched();

    if(this.form.get('samples_were_taken_based_on_a_specific_suspicion').value==1)
    {
      this.f.reason.setValidators([Validators.required]);
    }
    else
    {
      this.f.reason.setValidators([]);
    }
    this.f.reason.updateValueAndValidity();

    if(this.sampleEntries.length<=0){
      this.f.sample_number.setValidators([Validators.required]);
      this.f.taken_from.setValidators([Validators.required]);
      
      this.f.sample_number.updateValueAndValidity();
      this.f.taken_from.updateValueAndValidity();
      this.f.sample_number.markAsTouched();
      this.f.taken_from.markAsTouched();
    }else{
      this.f.sample_number.setValidators([]);
      this.f.taken_from.setValidators([]);
      
      this.f.sample_number.updateValueAndValidity();
      this.f.taken_from.updateValueAndValidity();
      this.f.sample_number.markAsTouched();
      this.f.taken_from.markAsTouched();
    }
    //console.log(this.form.valid);
    if(this.form.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let operator_title = this.form.get('operator_title').value;
      let sampling_date = this.errorSummary.displayDateFormat(this.form.get('sampling_date').value);
      let operator_responsible_person = this.form.get('operator_responsible_person').value;
      let sample_no = this.form.get('sample_no').value;
      let staff_who_took_sample = this.form.get('staff_who_took_sample').value;
      let type_of_samples = this.form.get('type_of_samples').value;
      let samples_were_taken_from = this.form.get('samples_were_taken_from').value;
      //let storage_room = this.form.get('storage_room').value;
      //let processing_line = this.form.get('processing_line').value;
      //let other_such_as_market = this.form.get('processing_line').value;
      let number_of_sub_samples_per_sample = this.form.get('number_of_sub_samples_per_sample').value;
      let describe_other_details_of_sampling_method = this.form.get('describe_other_details_of_sampling_method').value;
      let samples_were_taken_based_on_a_specific_suspicion = this.form.get('samples_were_taken_based_on_a_specific_suspicion').value;
      let reason = this.form.get('reason').value;
      let representative_sealed = this.form.get('representative_sealed').value;
      let representative_unsealed = this.form.get('representative_unsealed').value;
      let representative_sample_bag_number = this.form.get('representative_sample_bag_number').value;
      let operator_sealed = this.form.get('operator_sealed').value;
      let operator_unsealed = this.form.get('operator_unsealed').value;
      let operator_sample_bag_number = this.form.get('operator_sample_bag_number').value;
      let further_comments = this.form.get('further_comments').value;


      let samplinglist:any=[...this.sampleEntries];
      /*this.sampleEntries.forEach(x=>{
        samplinglist.push({x:});
      })*/
      let expobject:any={samplinglist,audit_id:this.audit_id,unit_id:this.unit_id,operator_title:operator_title,sampling_date:sampling_date,operator_responsible_person:operator_responsible_person,sample_no:sample_no,staff_who_took_sample:staff_who_took_sample,type_of_samples:type_of_samples,samples_were_taken_from:samples_were_taken_from,number_of_sub_samples_per_sample:number_of_sub_samples_per_sample,describe_other_details_of_sampling_method:describe_other_details_of_sampling_method,samples_were_taken_based_on_a_specific_suspicion:samples_were_taken_based_on_a_specific_suspicion,reason:reason,representative_sealed:representative_sealed,representative_unsealed:representative_unsealed,representative_sample_bag_number:representative_sample_bag_number,operator_sealed:operator_sealed,operator_unsealed:operator_unsealed,operator_sample_bag_number:operator_sample_bag_number,further_comments:further_comments,type:'sampling_list'};

      //console.log(expobject); return false;
       
      this.service.addData(expobject)
      .pipe(first())
      .subscribe(res => {

      
          if(res.status){
            this.editStatus = 1;
            this.success = {summary:res.message};
            this.remarkFormreset();
            
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
          this.buttonDisable = false;
          this.error = {summary:this.errorSummary.errorSummaryText};
          this.errorSummary.validateAllFormFields(this.form);
      });
        
       
    }
  }

  

  editStatus=0;
   
   
  samplingFormreset()
  {
    this.editStatus=0;
    
    this.samplingData = '';  
    this.form.reset();
    
    this.form.patchValue({     
      operator_title:'',
      sampling_date:'',   
      operator_responsible_person:'',
      sample_no:'',
      staff_who_took_sample:'',
      type_of_samples:'',
      samples_were_taken_from:'',
      
       
      number_of_sub_samples_per_sample:'',
      describe_other_details_of_sampling_method:'',
      samples_were_taken_based_on_a_specific_suspicion:'',
      reason:'',
      representative_sealed:'',
      representative_unsealed:'',
      representative_sample_bag_number:'',
      operator_sealed:'',
      operator_unsealed:'',
      operator_sample_bag_number:'',
      further_comments:''
    });
  }

  onSubmit(){ }
 
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

      let expobject:any={unit_id:this.unit_id,audit_id:this.audit_id,comments:remark,is_applicable:this.isApplicable,type:'sampling_list'}

      this.service.addRemark(expobject)
      .pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.success = {summary:res.message};
          this.buttonDisable = false;
          this.loading['button'] = false;
          this.loadDetails();
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
