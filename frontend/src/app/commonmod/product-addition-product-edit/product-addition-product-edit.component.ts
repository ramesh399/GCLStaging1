import { Component, OnInit, ViewChild, EventEmitter, Input } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray,NgForm,NgControl, Form } from '@angular/forms';
import { CountryService } from '@app/services/country.service';
import { StandardService } from '@app/services/standard.service';

import { EnquiryDetailService } from '@app/services/enquiry-detail.service';
import { ProductAdditionService } from '@app/services/change-scope/product-addition.service';

import { ProductService } from '@app/services/master/product/product.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

import { tap,map, startWith,first,switchMap } from 'rxjs/operators'; 

import { ActivatedRoute ,Params, Router } from '@angular/router';
import {LabelGrade} from '@app/models/master/labelgrade';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {Observable} from 'rxjs';

import { Product } from '@app/models/master/product';
import { Process } from '@app/models/master/process';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { Application } from '@app/models/application/application';
import { ProductType } from '@app/models/master/producttype';
import { MaterialComposition } from '@app/models/master/materialcomposition';
import { MaterialType } from '@app/models/master/materialtype';

@Component({
  selector: 'app-product-addition-product-edit',
  templateUrl: './product-addition-product-edit.component.html',
  styleUrls: ['./product-addition-product-edit.component.scss']
})
export class ProductAdditionProductEditComponent implements OnInit {

  form : FormGroup;
  @Input() id: number;
  @Input() standard_id: number;
  app_id:number;
  productEntries:any=[];
  productTypeList:ProductType[];
  loading:any=[];
  buttonDisable = false;
  showProduct = false;
  showCert = false;
  producttypeErrors='';
  enquiryForm : FormGroup;
  unitIndex:number=null;
  applicationData:any;
  processEntries:Process[] =[];
  standardEntries:any[]=[];
  unitStandardsDisable:any={};
  unitBSectorDisable:any={};
  productListDetails:any=[];
  productList:Product[];
  error:any;
  success:any;

  constructor(private modalService: NgbModal,private router:Router,private activatedRoute:ActivatedRoute, 
    private fb:FormBuilder,private productService:ProductService,private countryservice: CountryService,private standards: StandardService, public service:ProductAdditionService,public errorSummary:ErrorSummaryService,public applicationDetailService:ApplicationDetailService) { }

  ngOnInit() 
  {
		this.getProducts(this.id);
		
		this.productService.getProductList().pipe(first()).subscribe(res => {
			this.productList = res['products']; 
			this.materialTypeList = res['material_type']; 		  
		});
		
		this.enquiryForm = this.fb.group({	
			material:['',[Validators.required]],
			material_type:['',[Validators.required]],
			material_percentage:['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.max(100)]]	
		});
  }
  
  getProducts(id)
  {
		this.service.getProductDetails({id:id,standard_id:this.standard_id}).subscribe(res => {
			this.applicationData = res;
      
			this.productListDetails = res.productDetails;
			this.productEntries = res.products;
		});
  }


  modalss:any;
  guidanceContent='';
  openguidance(content,type) {

    if(type=='scopeholder')
    {
      this.guidanceContent='Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.';
    }
    else if(type=='standards')
    {
      this.guidanceContent='Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.';
    }
    else if(type=='product')
    {
      this.guidanceContent='Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.';
    }
    else
    {
      this.guidanceContent='Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.';
    }
    
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    this.modalss.result.then((result) => {

    }, (reason) => {
      
    });
  }
  
  

  editStatus = false;
  productIndex = null;
  materialList:MaterialComposition[]=[];
  productMaterialList:Array<any> = [];
  productStandardList:Array<any> = [];
  productEntry:any;
  
  productmaterial_error = '';
  editProduct(index:number){
	this.editStatus=true;
    this.productIndex = index;
    
	this.productEntry = this.productEntries[index];
    let prd= this.productEntry;
    this.getProductTypeOnEdit(prd.id,prd.product_type_id);
	
	/*
    this.enquiryForm.patchValue({
		product: prd.id,
		wastage:prd.wastage,
		product_type:prd.product_type_id,
    });
	*/    
     
    this.productStandardList = [...prd.productStandardList];
    this.productMaterialList = [...prd.productMaterialList];
		    
    this.showProduct = true;


    this.unitIndex = null;
    this.showCert = false;
  }

  getProductTypeOnEdit(productid,product_typeid){
    this.loading['producttype'] = 1;
    this.productService.getProductTypes(productid).pipe(first()).subscribe(res => {
      this.productTypeList = res['data']; 
	  //this.materialTypeList = res['material_type']; 
      this.getProductMaterial(product_typeid,0);
      this.loading['producttype'] = 0;
    });
  }

  getProductMaterial(product_typeid,makeempty=1){
    this.enquiryForm.patchValue({material:'',material_type:''});
    
      if(product_typeid>0)
      {
      this.loading['material'] = 1;
      this.productService.getMaterial(product_typeid).pipe(first()).subscribe(res => {
        this.materialList = res;
        this.loading['material'] = 0;
        if(makeempty){
        this.productMaterialList = [];
        }
      });
    }
  }
  
  get f() { return this.enquiryForm.controls; }
  
  productView()
  {
	this.editStatus=false;
  }
  
  /*
  Product Material Section
  */
  //productMaterialList:Array<any> = [];
  //productmaterial_error = '';
  materialTypeList:MaterialType[]=[];
  //materialList:MaterialComposition[]=[];
  
  removeProductMaterial(Id:number) {
    let index= this.productMaterialList.findIndex(s => s.material_id ==  Id);
    if(index !== -1)
      this.productMaterialList.splice(index,1);
  }
  touchProductMaterial(){
    this.f.material.markAsTouched();
    this.f.material_type.markAsTouched();
    this.f.material_percentage.markAsTouched();
  }
  addProductMaterial(){
    this.f.material.setValidators([Validators.required]);
    this.f.material_type.setValidators([Validators.required]);
    this.f.material_percentage.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.max(100)]);

    this.f.material.updateValueAndValidity();
    this.f.material_type.updateValueAndValidity();
    this.f.material_percentage.updateValueAndValidity();


    this.touchProductMaterial();
    let material = this.enquiryForm.get('material').value;
    let material_type = this.enquiryForm.get('material_type').value;
    let material_percentage = this.enquiryForm.get('material_percentage').value;

    let selmaterial = this.materialList.find(s => s.id ==  material);
    let selmaterialtype = this.materialTypeList.find(s => s.id ==  material_type);
    this.productmaterial_error = '';
    
    if(material=='' || material_type=='' || material_percentage=='' || this.f.material_percentage.errors){
      return false;
    }
    
    let entry= this.productMaterialList.findIndex(s => s.material_id ==  material);
    let expobject = {material_id: selmaterial.id,
                material_name: selmaterial.name,
                material_percentage:material_percentage,
                material_type_id:selmaterialtype.id,
                material_type_name:selmaterialtype.name}

    if(entry === -1){
      this.productMaterialList.push(expobject);
    }else{
      this.productMaterialList[entry] = expobject;
    }
    
    this.enquiryForm.patchValue({
      material: '',
      material_type:'',
      material_percentage:''
    });
    this.f.material.setValidators([]);
    this.f.material_type.setValidators([]);
    this.f.material_percentage.setValidators([]);

    this.f.material.updateValueAndValidity();
    this.f.material_type.updateValueAndValidity();
    this.f.material_percentage.updateValueAndValidity();

    //this.materialList = [];
    this.productmaterial_error = '';
  }
  editProductStatus=false;
  editProductMaterial(Id:number){
    let mat= this.productMaterialList.find(s => s.material_id ==  Id);
	this.editProductStatus=true;
    //this.getProductMaterial(mat.product_type_id);
	
	//console.log(mat.material_id+'--'+mat.material_type_id+'--'+mat.material_percentage);
    
    this.enquiryForm.patchValue({
      material: mat.material_id,
      material_type: mat.material_type_id,
      material_percentage:mat.material_percentage
    });
  }
  
  updateMaterialComposition()
  {
	  let materialpercentage:any=0;
	  if(this.productMaterialList.length > 0)
	  {
		this.productMaterialList.forEach((val)=>{			
			materialpercentage = parseFloat(materialpercentage) + parseFloat(val.material_percentage);
		});
	  }
	  
	  if(materialpercentage != 100)
	  {
        this.productmaterial_error = 'Total material percentage should be equal to 100';
      } 
	  
      if(this.productMaterialList.length<=0)
	  {
        this.productmaterial_error = 'Please add product material';
      }
	  
	  //console.log(this.productEntry.autoid);	  
	
	  if(this.productmaterial_error=='')
	  {
		   //3180
		   let productMaterialList=[];
		   this.productMaterialList.forEach((listval)=>{
				productMaterialList.push({material_id:listval.material_id,material_name:listval.material_name,material_percentage:listval.material_percentage,material_type_id:listval.material_type_id,material_type_name:listval.material_type_name});
		   });
		   
		   this.loading['button'] = true;
		   this.buttonDisable = true;
		   
		   this.service.updateProductMaterialComposition({product_id:this.productEntry.autoid,productmateriallist:productMaterialList})
		  .pipe(first())
		  .subscribe(res => {

			  if(res.status){
				this.success = {summary:res.message};				
				setTimeout(() => {				
				   this.getProducts(this.id);
				   this.editStatus=false;
				   this.editProductStatus=false;
				   this.loading['button'] = false;
				   this.buttonDisable = false;
				   this.success = {};
				},this.errorSummary.redirectTime);
			  }else if(res.status == 0){				
				this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.enquiryForm)};
				this.loading['button'] = false;
				this.buttonDisable = false;
			  }else{
			   	this.error = {summary:res};
				this.loading['button'] = false;
				this.buttonDisable = false;
			  }		 
		  },
		  error => {
			  this.error = {summary:error};
			  this.loading['button'] = false;
			  this.buttonDisable = false;
		  });	  
		  
	  }
  }  
  
  resetProductMaterial()
  {
	this.editProductStatus=false;
    this.enquiryForm.patchValue({
      material: '',
      material_type: '',
      material_percentage:''
    });
  }

}
