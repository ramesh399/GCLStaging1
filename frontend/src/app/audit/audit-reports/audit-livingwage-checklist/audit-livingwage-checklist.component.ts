import { Component, OnInit,Input,QueryList, ViewChildren } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm } from '@angular/forms';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { AuditLivingWageChecklistService } from '@app/services/audit/audit-livingwage-checklist.service';
import { AuthenticationService } from '@app/services/authentication.service';
import { first,map } from 'rxjs/operators';
import {Observable} from 'rxjs';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-audit-livingwage-checklist',
  templateUrl: './audit-livingwage-checklist.component.html',
  styleUrls: ['./audit-livingwage-checklist.component.scss'],
  providers: [AuditLivingWageChecklistService]
})
export class AuditLivingwageChecklistComponent implements OnInit {
  @Input() cond_viewonly: any;
  title = 'Audit Interview Employee'; 
  form : FormGroup;
  remarkForm : FormGroup;   
  summary:any=[];
  id:number;
  audit_id:number;
  unit_id:number;
  employeeData:any;
  EmployeeData:any;
  migrantlist:any;
  genderlist:any;
  typelist:any;
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
  answerArr:any;
  livingrequirements:any;
  livingcategorys:any;
  reviewcommentlist=[];
  reviewcomments=[];
  categorycommentlist=[];
  categorycomments=[];
  foodCategoryID:any;
  currencyCategoryID:any;
  isItApplicable=true;
  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private modalService: NgbModal,private activatedRoute:ActivatedRoute, private router: Router,private fb:FormBuilder, public service: AuditLivingWageChecklistService,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService)
  {
   
  }

  ngOnInit() 
  {
    this.audit_id = this.activatedRoute.snapshot.queryParams.audit_id;
    this.unit_id = this.activatedRoute.snapshot.queryParams.unit_id;
    
    this.remarkForm = this.fb.group({	
      remark:['',[Validators.required, this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]]
    });
	
    this.service.getChecklist().pipe(first())
    .subscribe(res => { 
      this.loadDetails();   
      this.livingrequirements = res.requirements;
      this.livingcategorys = res.categorys;

      this.livingrequirements.forEach(val => {
        this.reviewcommentlist['qtd_comments'+val.id]='';
        if(val.name.toLowerCase() =='local currency'){
          this.currencyCategoryID = val.id;
        }
      });

      this.livingcategorys.forEach(val => {
        this.categorycommentlist['cost'+val.id]='';
        this.categorycommentlist['individual'+val.id]='';
        this.categorycommentlist['no_of_wage']='';
        if(val.name.toLowerCase() == 'food'){
          this.foodCategoryID = val.id;
        // console.log(this.foodCategoryID+'==');
        }
      });

      this.categorycommentlist['cost']='';
    });

    this.service.getRemarkData({audit_id:this.audit_id,unit_id:this.unit_id,type:'livingwage_list'}).pipe(first())
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
          this.editStatus = 1;
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
  }

  loadDetails()
  {
    this.service.getchecklistAnswer({unit_id:this.unit_id,audit_id:this.audit_id}).pipe(first())
    .subscribe(list => {    

      if(list['requirementcomment'])
      {
        this.editStatusChecklist =1;
        this.reviewcomments = list['requirementcomment'];
        this.reviewcomments.forEach(val => {
          this.reviewcommentlist['qtd_comments'+val.category_id]=val.comment;
        });
      }

      if(list['categorycomment'])
      {
        this.editStatusAverage = 1;
        this.categorycomments = list['categorycomment'];
        this.categorycomments.forEach(val => {
          this.categorycommentlist['cost'+val.category_id]=val.cost_in_local_currency;
          this.categorycommentlist['individual'+val.category_id]=val.number_of_individuals;
          this.categorycommentlist['no_of_wage']=list['number_of_wage_earners_per_family'];
        });
      }
        
    });
  }

  get f() { return this.form.controls; }
  get rf() { return this.remarkForm.controls; }
  editStatusChecklist:any = 0;
  editStatusAverage:any = 0;
  onSubmit(f:NgForm,type)
  {
    if(type!=='category')
    {
      this.livingrequirements.forEach(element => {
        let comment = eval("f.value.qtd_comments"+element.id);
        f.controls["qtd_comments"+element.id].markAsTouched();
      });
  
      if (f.valid) 
      {
        let reviewdata = [];
        this.livingrequirements.forEach(element => {
          let ans = {category:element.name,category_id:element.id,comment:eval("f.value.qtd_comments"+element.id)};
          reviewdata.push(ans);
        });
  
        let requiremnetdata={
          audit_id:this.audit_id,
          unit_id:this.unit_id,
          type:'livingwage_list',
          checklistdata:reviewdata
        }
  
        this.loading['button']  = true;
        this.service.addChecklist(requiremnetdata)
        .pipe(first())
        .subscribe(res => {
              
            if(res.status==1){
                this.editStatusChecklist =1;
                this.success = {summary:res.message};
                this.loading['button'] = false;
                this.remarkFormreset();
                
              }else if(res.status == 0){
                this.error = {summary:res.message};
              }else{
                this.error = {summary:res};
              }
              this.loading['button'] = false;
            
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        });
      }
      else 
      {
        this.error = {summary:this.errorSummary.errorSummaryText};
      }
    }
    else 
    {
      this.livingcategorys.forEach(element => {
        
        f.controls["cost"+element.id].markAsTouched();
        f.controls["individual"+element.id].markAsTouched();
      });

      if (f.valid) 
      {
        let categorydata = [];

        let number_of_wage_earners_per_family = eval('f.value.no_of_wage');
        

        this.livingcategorys.forEach(element => {
          let ans = {category:element.name,category_id:element.id,cost_in_local_currency:eval("f.value.cost"+element.id),number_of_individuals:eval("f.value.individual"+element.id)};
          categorydata.push(ans);
        });
  
        let livingCategorydata={
          audit_id:this.audit_id,
          unit_id:this.unit_id,
          type:'livingwage_list',
          number_of_wage_earners_per_family:number_of_wage_earners_per_family,
          categorydata:categorydata
        }

        this.loading['button']  = true;
        this.service.addCategory(livingCategorydata)
        .pipe(first())
        .subscribe(res => {
              
            if(res.status==1){
              this.editStatusAverage = 1;
              this.success = {summary:res.message};
              this.loading['button'] = false;
              this.remarkFormreset();
              
            }else if(res.status == 0){
              this.error = {summary:res.message};
            }else{
              this.error = {summary:res};
            }
            this.loading['button'] = false;
            
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        });
      }
      else 
      {
        this.error = {summary:this.errorSummary.errorSummaryText};
      }

     
    }
    
  }
  individualTotal:any = [];
  getCalVal(calindex){
    
    let costval:any = this.categorycommentlist['cost'+calindex];
    let individualval:any = this.categorycommentlist['individual'+calindex];
    let calcVal:any = parseFloat(costval) * parseFloat(individualval);
    let calcValformat:any;
    if(!isNaN(calcVal)){
      this.individualTotal[calindex] = calcVal;
      calcValformat= calcVal;
    }else{
      this.individualTotal[calindex] = 0;
      calcValformat= 0;
    }
    return calcValformat.toFixed(2);
  }
  totalFamilyBasket:any=0;
  getCalTotalVal(){
    this.totalFamilyBasket = 0;
    Object.keys(this.individualTotal).forEach(key=>{
        //console.log(key, obj[key]);
        this.totalFamilyBasket = parseFloat(this.individualTotal[key]) + parseFloat(this.totalFamilyBasket);
    });
    if(!isNaN(this.totalFamilyBasket)){
      return this.totalFamilyBasket;
    }else{
      return 0;
    }
  }

  getCalTotalFood(){
    let foodtotal = (parseFloat(this.individualTotal[this.foodCategoryID])/parseFloat(this.totalFamilyBasket))*100;
    if(!isNaN(foodtotal)){
      return foodtotal.toFixed(2);
    }else{
      return 0;
    }
  }

  getLivingwage(){
    let livingwagetotal = ((this.totalFamilyBasket/this.categorycommentlist['no_of_wage'])*110)/100;
    if(!isNaN(livingwagetotal) && isFinite(livingwagetotal)){
      return livingwagetotal.toFixed(2);
    }else{
      return 0;
    }
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
  
  editStatus=0;
  addRemark()
  {
    this.rf.remark.markAsTouched();

    if(this.remarkForm.valid)
    {
      this.buttonDisable = true;
      this.loading['button'] = true;

      let remark = this.remarkForm.get('remark').value;

      let expobject:any={unit_id:this.unit_id,audit_id:this.audit_id,comments:remark,is_applicable:this.isApplicable,type:'livingwage_list'}

      this.service.addRemark(expobject)
      .pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.editStatus = 1;
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


