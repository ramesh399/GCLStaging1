import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ProductTypeService } from '@app/services/master/producttype/producttype.service';
import { ProductService } from '@app/services/master/product/product.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';
import { Product } from '@app/models/master/product';

@Component({
  selector: 'app-add-producttype',
  templateUrl: './add-producttype.component.html',
  styleUrls: ['./add-producttype.component.scss']
})
export class AddProducttypeComponent implements OnInit {

  title = 'Add Product Description';
  btnLabel = 'Save';
  productList:Product[];
  form : FormGroup;
  loading = false;
  buttonDisable = false;
  error:any;
  submittedError = false;
  success:any;
  product_idErrors = '';
  nameErrors = '';
  codeErrors = '';
  formData:FormData = new FormData();
  
  constructor(private router: Router,private fb:FormBuilder,private productService: ProductService,private producttypeService:ProductTypeService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {
		
	this.productService.getProductList().subscribe(res => {
      this.productList = res['products'];      
    });	
	
	this.form = this.fb.group({
      product_id:['',[Validators.required]],      
      name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255), Validators.pattern("^[a-zA-Z0-9 \'\-+%/&,().-]+$")]],
	  code:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(50),Validators.pattern("^[a-zA-Z0-9 \'\-+%/&,().-]+$")]],
	  //description:['',[this.errorSummary.noWhitespaceValidator]], 	  
    });
	
  }

  get f() { return this.form.controls; }
  
  onSubmit(){
    if (this.form.valid) {
      
      this.loading = true;
	  
	  this.producttypeService.addData(this.form.value)
      .pipe(
        first()        
      ).subscribe(res => {
          if(res.status){
            this.success = {summary:res.message};
			this.buttonDisable=true;
            setTimeout(()=>this.router.navigate(['/master/producttype/list']),this.errorSummary.redirectTime);            
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