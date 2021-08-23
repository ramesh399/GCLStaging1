import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { MaterialCompositionService } from '@app/services/master/materialcomposition/materialcomposition.service';
import { ProductService } from '@app/services/master/product/product.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';
import { Product } from '@app/models/master/product';
import { ProductType } from '@app/models/master/producttype';

@Component({
  selector: 'app-add-materialcomposition',
  templateUrl: './add-materialcomposition.component.html',
  styleUrls: ['./add-materialcomposition.component.scss']
})
export class AddMaterialcompositionComponent implements OnInit {

  title = 'Add Material';
  btnLabel = 'Save';
  productList:Product[];
  productTypeList:ProductType[];
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  product_idErrors = '';
  product_type_idErrors = '';
  nameErrors = '';
  codeErrors = '';
  
  formData:FormData = new FormData();
  
  constructor(private router: Router,private fb:FormBuilder,private productService: ProductService,private materialCompositionService:MaterialCompositionService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
		
	this.productService.getProductList().subscribe(res => {
      this.productList = res['products'];      
    });	
	
	this.form = this.fb.group({
      product_id:['',[Validators.required]], 
	  product_type_id:['',[Validators.required]],  
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255), Validators.pattern("^[a-zA-Z0-9 \'\-+%/&,().-]+$")]],
	  code:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(50),Validators.pattern("^[a-zA-Z0-9 \'\-+%/&,().-]+$")]],
	  description:['',[this.errorSummary.noWhitespaceValidator]],	  
    });
	
  }
  
  getProductTypeList(id:number){
	this.productTypeList=[];
	this.form.patchValue({product_type_id:''});
	if(id>0)
	{
		this.productService.getProductType(id).subscribe(res => {
		  if(res['status'])
		  {
			this.productTypeList = res['data'];
		  }	
		});
	}	
  } 

  get f() { return this.form.controls; }
    
  onSubmit(){
    if (this.form.valid) {
      
      this.loading = true;
	  
	  this.materialCompositionService.addData(this.form.value)
      .pipe(
        first()        
      ).subscribe(res => {
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable = true;
            setTimeout(()=>this.router.navigate(['/master/materialcomposition/list']),this.errorSummary.redirectTime);            
          }else if(res.status == 0){
            this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};				      
          }else{			      
            this.error = {summary:res};
          }
          this.loading = false;         
      },
      error => {
          this.error = {summary:error};
          this.loading = false;
      });      
    } else {
	  this.error = {summary:this.errorSummary.errorSummaryText};
      this.errorSummary.validateAllFormFields(this.form);       
    }
  }

}