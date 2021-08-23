import { Component, OnInit,EventEmitter,QueryList, ViewChildren, Input } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditEnvironment } from '@app/models/audit/audit-environment';
import { AuditEnvironmentService } from '@app/services/audit/audit-environment.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Renderer, ElementRef } from '@angular/core';

@Component({
  selector: 'app-audit-environment',
  templateUrl: './audit-environment.component.html',
  styleUrls: ['./audit-environment.component.scss'],
  providers: [AuditEnvironmentService]
})
export class AuditEnvironmentComponent implements OnInit {
  @Input() app_id: number;
  @Input() unit_id: number;
  @Input() cond_viewonly: any;
  @Input() audit_id: number;

  title = 'Audit Environment'; 
  form : FormGroup; 
  remarkForm : FormGroup;
  environments$: Observable<AuditEnvironment[]>;
  total$: Observable<number>;
  sufficient_access$: Observable<number>;

  id:number;
  //audit_id:number;
  //unit_id:number;
  model: any = {sufficient:''}
  environmentData:any;
  EnvironmentData:any;
  sufficientlist:any;
  error:any;
  success:any;
  dataloaded = false;
  buttonDisable = false;
  formData:FormData = new FormData();
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  modalss:any;
  loading:any=[];
  range:Array<any> = [];
  isItApplicable=true;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;
  
  constructor(private elRef: ElementRef, private renderer: Renderer,private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service:AuditEnvironmentService, public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
    this.environments$ = service.environments$;
    this.total$ = service.total$;
    this.sufficient_access$ = service.sufficient_access$;
    
  }
  sufficient_access:number = 0;
    ngOnInit() 
    {
      if(!this.audit_id){
        this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
      }
      //this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
      //this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
      this.service.unit_id = this.unit_id;
      this.service.customSearch();
      this.sufficient_access$.subscribe(x=>{ this.sufficient_access=x; });
      this.form = this.fb.group({	
        year:['',[Validators.required, Validators.pattern("^[0-9]*$"),Validators.minLength(4),Validators.maxLength(4)]], 
        total_production_output:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
        total_water_supplied:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
        water_consumption:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
        electrical_energy_consumption:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
        gas_consumption:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
        oil_consumption:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
        coal_consumption:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
        fuelwood_consumption:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
      
        //total_solid_waste:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],	  
        total_energy_consumption_converted_to_kwh:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
        total_energy_consumption:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
        cod_in_waste_water:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
        total_cod:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
        cod_textile_output:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],
        wastage_textile_output:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],	  
            
        total_waste:['',[Validators.required, Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.maxLength(13)]],	  
        //comments:['',[Validators.required,this.errorSummary.noWhitespaceValidator]]	  
        sufficient:['',[Validators.required]],
      });
	
      this.remarkForm = this.fb.group({	
        remark:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]]
      });

      this.service.getOptionList().pipe(first())
      .subscribe(res => {   
        this.sufficientlist = res.sufficientlist;
      });


      this.service.getRemarkData({audit_id:this.audit_id,unit_id:this.unit_id,app_id:this.app_id,type:'environment_list'}).pipe(first())
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
    
      this.authservice.currentUser.subscribe(x => {
        if(x){
          let user = this.authservice.getDecodeToken();
          this.userType= user.decodedToken.user_type;
          this.userdetails= user.decodedToken;
        }else{
          this.userdecoded=null;
        }
      });
	
      let year = new Date().getFullYear();    
      this.range.push(year);
      for (let i = 1; i <=5; i++) {
          this.range.push(year-i);
      }
    }

    get f() { return this.form.controls; }
    get rf() { return this.remarkForm.controls; }

    environmentIndex:number=null;
    addenvironment()
    {
      this.f.year.markAsTouched();
      this.f.total_production_output.markAsTouched();
      this.f.total_water_supplied.markAsTouched();
      this.f.water_consumption.markAsTouched();	
      this.f.electrical_energy_consumption.markAsTouched();
      this.f.gas_consumption.markAsTouched();
      this.f.oil_consumption.markAsTouched();
      this.f.coal_consumption.markAsTouched();
      this.f.fuelwood_consumption.markAsTouched();
      //this.f.total_solid_waste.markAsTouched();
    
      this.f.total_energy_consumption_converted_to_kwh.markAsTouched();
      this.f.total_energy_consumption.markAsTouched();
      this.f.cod_in_waste_water.markAsTouched();
      this.f.total_cod.markAsTouched();
      this.f.cod_textile_output.markAsTouched();
      this.f.wastage_textile_output.markAsTouched();
      
      this.f.total_waste.markAsTouched();
      // if(this.auditor_comment_access){
      //   this.f.comments.markAsTouched(); 
      // }else{
      //   this.f.comments.setValidators([]);
      //   this.f.comments.updateValueAndValidity();
      // }

      if(this.sufficient_access){
        this.f.sufficient.markAsTouched(); 
      }else{
        this.f.sufficient.setValidators([]);
        this.f.sufficient.updateValueAndValidity();
      }
      

    
      if(this.form.valid)
      {
    
        this.buttonDisable = true;
        this.loading['button'] = true;

        let year = this.form.get('year').value;
        let total_production_output = this.form.get('total_production_output').value;
        let total_water_supplied = this.form.get('total_water_supplied').value;
        let water_consumption = this.form.get('water_consumption').value;	
        
        let electrical_energy_consumption = this.form.get('electrical_energy_consumption').value;
        let gas_consumption = this.form.get('gas_consumption').value;
        let oil_consumption = this.form.get('oil_consumption').value;
        let coal_consumption = this.form.get('coal_consumption').value;
        let fuelwood_consumption = this.form.get('fuelwood_consumption').value;
        //let total_solid_waste = this.form.get('total_solid_waste').value;
          
        let total_energy_consumption_converted_to = this.form.get('total_energy_consumption_converted_to_kwh').value;
        let total_energy_consumption = this.form.get('total_energy_consumption').value;
        let cod_in_waste_water = this.form.get('cod_in_waste_water').value;
        let total_cod = this.form.get('total_cod').value;
        let cod_textile_output = this.form.get('cod_textile_output').value;
        let wastage_textile_output = this.form.get('wastage_textile_output').value;
        let total_waste = this.form.get('total_waste').value;
        // let comments:any = '';
        // if(this.auditor_comment_access){
        //   comments = this.form.get('comments').value;
        // }
        let sufficient:any = '';
        if(this.sufficient_access){
          sufficient = this.form.get('sufficient').value;
        }
          
        let expobject:any={app_id:this.app_id,unit_id:this.unit_id,audit_id:this.audit_id,year:year,total_production_output:total_production_output,total_water_supplied:total_water_supplied,electrical_energy_consumption:electrical_energy_consumption,water_consumption:water_consumption,gas_consumption:gas_consumption,oil_consumption:oil_consumption,coal_consumption:coal_consumption,fuelwood_consumption:fuelwood_consumption,total_energy_consumption_converted_to:total_energy_consumption_converted_to,total_energy_consumption:total_energy_consumption,cod_in_waste_water:cod_in_waste_water,total_cod:total_cod,cod_textile_output:cod_textile_output,wastage_textile_output:wastage_textile_output,total_waste:total_waste,sufficient:sufficient,type:'environment_list'};
        
        if(1)
        {
          if(this.environmentData)
          {
            expobject.id = this.environmentData.id;
          }
          
          this.service.addData(expobject)
          .pipe(first())
          .subscribe(res => {

            if(res.status){
              this.success = {summary:res.message};
              this.service.customSearch();
              this.environmentFormreset();
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
            this.buttonDisable = false;
            this.error = {summary:error};
            this.loading['button'] = false;
          });
          
        } 
        else 
        {
          this.error = {summary:this.errorSummary.errorSummaryText};
          this.errorSummary.validateAllFormFields(this.form); 
          
        }   
      }
    }


  viewEnvironment(content,data)
  {
    this.sufficientsuccess = '';
    this.sufficienterror = '';

    this.model.sufficient = data.sufficient;
    this.EnvironmentData = data;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }

  editStatus=0;
  editEnvironment(index:number,environmentdata) 
  { 
    this.editStatus=1;
    this.success = {summary:''};
    this.environmentData = environmentdata;
    let sufficient = environmentdata.sufficient=== null ?'':environmentdata.sufficient;
    this.form.patchValue({
      year:environmentdata.year,
      total_production_output:environmentdata.total_production_output,   
	    water_consumption:environmentdata.water_consumption,
      total_water_supplied:environmentdata.total_water_supplied,
      electrical_energy_consumption:environmentdata.electrical_energy_consumption,
      gas_consumption:environmentdata.gas_consumption,
      oil_consumption:environmentdata.oil_consumption,
      coal_consumption:environmentdata.coal_consumption,
      fuelwood_consumption:environmentdata.fuelwood_consumption,
      //total_solid_waste:environmentdata.total_solid_waste
      total_energy_consumption_converted_to_kwh:environmentdata.total_energy_consumption_converted_to,
      total_energy_consumption:environmentdata.total_energy_consumption,
      cod_in_waste_water:environmentdata.cod_in_waste_water,
      total_cod:environmentdata.total_cod,
      cod_textile_output:environmentdata.cod_textile_output,
      wastage_textile_output:environmentdata.wastage_textile_output,
      total_waste:environmentdata.total_waste,
      //comments:environmentdata.comments,
      sufficient:environmentdata.sufficient
    });
    this.scrollToBottom();
  }  

  sufficientsuccess:any;
  sufficienterror:any;
  sufficientmodalss:any;
  changeSufficient(content,value)
  {
    this.renderer.invokeElementMethod(this.elRef.nativeElement.ownerDocument.activeElement, 'blur');
    this.sufficientmodalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.sufficientmodalss.result.then((result) => {
        this.service.changeSufficient({audit_id:this.audit_id,unit_id:this.unit_id,app_id:this.app_id,id:this.EnvironmentData.id,sufficient:value})
        .pipe(first())
        .subscribe(res => {

          if(res.status){
            this.sufficientsuccess = {summary:res.message};
            this.EnvironmentData.sufficient = value;
            this.buttonDisable = true;
            this.service.customSearch();
          }else if(res.status == 0){
            this.sufficienterror = {summary:res};
          }
          this.loading['button'] = false;
          this.buttonDisable = false;
        },
        error => {
            this.sufficienterror = {summary:error};
            this.loading['button'] = false;
        });
    }, (reason) => {
      this.model.sufficient = this.EnvironmentData.sufficient;
    })
  }
  
  removeEnvironment(content,index:number,environmentdata) 
  {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});

    this.modalss.result.then((result) => {
        this.environmentFormreset();
        this.service.deleteData({audit_id:this.audit_id,unit_id:this.unit_id,app_id:this.app_id,id:environmentdata.id})
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
          this.buttonDisable = false;
            this.error = {summary:error};
            this.loading['button'] = false;
        });
    }, (reason) => {
    })
    
  
  }

  environmentFormreset()
  {
    this.editStatus=0;
    
    this.environmentData = '';  
    this.form.reset();
    
    this.form.patchValue({     
      year:'',   
      total_production_output:'',
	    water_consumption:'',
      electrical_energy_consumption:'',
      total_water_supplied:'',
      gas_consumption:'',
      oil_consumption:'',
      coal_consumption:'',
      fuelwood_consumption:'',
      //total_solid_waste:''
      total_energy_consumption_converted_to_kwh:'',
      total_energy_consumption:'',
      cod_in_waste_water:'',
      total_cod:'',
      cod_textile_output:'',
      wastage_textile_output:'',
      total_waste:'',
      //comments:'',
      sufficient:''
    });
  }

  getConsumption(){
    //console.log(this.f.total_production_output.value);
    let consval = this.f.total_water_supplied.value / this.f.total_production_output.value * 1000;
    if(!isNaN(consval)){
		return consval.toFixed(2); 
    }else{
		return 0;
    }
    
    
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

      let expobject:any={app_id:this.app_id,unit_id:this.unit_id,audit_id:this.audit_id,comments:remark,is_applicable:this.isApplicable,type:'environment_list'}

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
        this.buttonDisable = false;
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
