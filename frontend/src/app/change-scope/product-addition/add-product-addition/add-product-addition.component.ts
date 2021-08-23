import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { AuthenticationService } from '@app/services/authentication.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { tap,map, startWith,first,switchMap } from 'rxjs/operators'; 
import { StandardService } from '@app/services/standard.service';
import { ProductService } from '@app/services/master/product/product.service';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import { ProductAdditionService } from '@app/services/change-scope/product-addition.service';
import { Standard } from '@app/services/standard';
import {LabelGrade} from '@app/models/master/labelgrade';
import { Units } from '@app/models/master/units';
import { Product } from '@app/models/master/product';
import { ProductType } from '@app/models/master/producttype';
import { MaterialComposition } from '@app/models/master/materialcomposition';
import { MaterialType } from '@app/models/master/materialtype';
import { NgForm } from '@angular/forms';
@Component({
  selector: 'app-add-product-addition',
  templateUrl: './add-product-addition.component.html',
  styleUrls: ['./add-product-addition.component.scss']
})
export class AddProductAdditionComponent implements OnInit {

  title = 'Product Addition';	
  form : FormGroup;
  unitForm : FormGroup;
  loading:any={};
  unitlist:any=[];
  buttonDisable = false;
  AddProductbtnDisable = true;
  id:number;
  error:any;
  success:any;
  requestdata:any=[];
  unitProductList:any=[];
  appdata:any=[];
  productList:Product[];
  standardList:Standard[];
  selStandardIds = [];
  selStandardList:Array<any> = [];
  materialTypeList:MaterialType[]=[];
  productMaterialList:Array<any> = [];
  productmaterial_error = '';
  materialList:MaterialComposition[]=[];
  formData:FormData = new FormData();
  modalss:any;
  panelOpenState = true;
  productErrors='';
  wastageErrors='';
  productTypeList:ProductType[];
  producttypeErrors='';
  compositionErrors = '';
  std_with_product_std_error='';

  userType:number;
  userdetails:any;
  userdecoded:any;
  
  units:any;
  unitstandard:any=[];
  redirecttype:any;
  new_app_id:any;
  app_id:any;
  applicationdata:any=[];
  selUnitStandardList:any=[];
  constructor(private modalService: NgbModal, private additionservice: ProductAdditionService, private productService:ProductService,private standards: StandardService, private activatedRoute:ActivatedRoute,private router: Router,private fb:FormBuilder,public errorSummary: ErrorSummaryService, private authservice:AuthenticationService) { }

  ngOnInit() {
	   this.authservice.currentUser.subscribe(x => {
      if(x){
        
         
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        
      }else{
        this.userdecoded=null;
      }
    });

	  this.app_id = this.activatedRoute.snapshot.queryParams.app_id;
  	this.units = this.activatedRoute.snapshot.queryParams.units;	

    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.new_app_id = this.activatedRoute.snapshot.queryParams.new_app_id;
    this.redirecttype = this.activatedRoute.snapshot.queryParams.redirecttype;

    this.form = this.fb.group({
      autoid:[''],
      app_id: ['',[Validators.required]],
      product: ['',[Validators.required]],
      wastage: ['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.max(100)]],
      product_type:['',[Validators.required]],
      
      material:['',[Validators.required]],
      material_type:['',[Validators.required]],
      material_percentage:['',[Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.max(100)]],

      
      composition_standard:['',[Validators.required]],
      label_grade:['',[Validators.required]],
    });

    this.unitForm = this.fb.group({
      unit_id: ['',[Validators.required]]
    });

    this.productService.getProductList().pipe(first()).subscribe(res => {
      this.productList = res['products']; 
      this.materialTypeList = res['material_type'];   
    });
	
	/*
    this.standards.getStandard().pipe(first()).subscribe(res => {
      this.standardList = res['standards'];     
    });
	*/
	
	
    
    this.loadProductList(this.id)
  }

  get f() { return this.form.controls; }  
  get uf() { return this.unitForm.controls; } 
  
  getPdtStandardList(units){
    this.additionservice.getAppStandard({unit_id:units}).pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.selStandardList = res['data'];           
        }else
        {           
          this.error = {summary:res};
        }        
    },
    error => {
        this.error = error;
        this.loading.company= false;
    });
  }
  oncompanychange(value)
  {
    this.form.patchValue({
      composition_standard:'',
    });

    if(value)
    {
      this.additionservice.getAppStandard(value).pipe(first())
      .subscribe(res => {
        if(res.status)
        {
          this.selStandardList = res['data'];
          this.AddProductbtnDisable = false;
        }
        else if(res.status == 0)
        {
          this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};
        }
        else
        {			      
          this.error = {summary:res};
        }
        
      },
      error => {
          this.error = error;
          this.loading.company= false;
      });

      this.additionservice.getUnit(value)
      .pipe(first())
      .subscribe(res => {
        this.loading['unitdata'] = false;
        if(res.status){
          this.unitlist = res.data.unitlist;
        }else if(res.status == 0){
          this.error = {summary:res};
        }       
      },
      error => {
          this.error = {summary:error};
          this.loading['unitdata'] = false;
      });
    }


  }

  unit_id_error:any;
  /*addUnitProduct(content)
  {
    this.unit_id_error = false;
    this.loading['unitdata'] = true;
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title'});
  }
  */
  product_ids:any=[];
  newproducterror:any=[];

  removeUnitProduct(unit_id,pdtindex:number) {
    this.unitProductList[unit_id].splice(pdtindex,1);
  }

  addUnitProduct(unit_id){
     
    let selproduct;
     
    let unitProductIndex = this.product_ids['qtd_'+unit_id];
     this.newproducterror[unit_id] = '';
  
    if(unitProductIndex== '' || unitProductIndex===null || unitProductIndex===undefined){
      this.newproducterror[unit_id] = 'true';
      return false;
    } 

    
    

      let selunitlist = this.productListDetails.filter(x => this.unitstandard[unit_id].includes(x.standard_id));
     let selunitproduct = {...selunitlist[unitProductIndex],'pdtListIndex':unitProductIndex};
    this.unitproductErrors='';
    //console.log(this.unitProductList[unit_id]);
    let entry= this.unitProductList[unit_id].find(s => s.pdtListIndex ==  unitProductIndex);
    if(entry === undefined){
      this.unitProductList[unit_id].push(selunitproduct);
    }
    this.product_ids['qtd_'+unit_id] = '';
  }


  productListDetails:any=[];
  unitvalproductErrors = '';
  unitproductErrors = '';
  productEntries:any=[];
  productIndex = null;
  productMasterList = [];
  unitEntries:Units[] = [];

  addProduct(){
    this.f.product.setValidators([Validators.required]);
    this.f.wastage.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.max(100)]);
    this.f.product_type.setValidators([Validators.required]);
    

    this.f.product.updateValueAndValidity();
    this.f.wastage.updateValueAndValidity();
    this.f.product_type.updateValueAndValidity();
    
    if(this.productStandardList.length<=0){

      this.f.composition_standard.setValidators([Validators.required]);
      this.f.label_grade.setValidators([Validators.required]);

      this.f.composition_standard.updateValueAndValidity();
      this.f.label_grade.updateValueAndValidity();
    }

    this.touchProduct();
    let productId:number = this.form.get('product').value;
    let wastage = this.form.get('wastage').value;
    let product_type = this.form.get('product_type').value;
    let autoid:any = this.form.get('autoid').value;
    let app_id:any = this.form.get('app_id').value;
    
    
    let materialcomposition = [];
    let materialcompositionname = '';
    let materialpercentage:any=0;
    if(this.productMaterialList.length > 0){

      this.productMaterialList.forEach((val)=>{
        materialcomposition.push(val.material_percentage+'% '+val.material_name);
        materialpercentage = parseFloat(materialpercentage) + parseFloat(val.material_percentage);
      });

      materialcompositionname = materialcomposition.join(' + ');
     
    }
    this.unitvalproductErrors = '';
    /*
    if(this.productEntries.length>0){
      
      this.productEntries.forEach((val,index) => {
        let proddesc = val.product_type_id;
        let prodcat = val.id;
       
        if((this.productIndex!=null && this.productIndex !=index)|| this.productIndex===null ){
          
          //if(prodcat== productId && proddesc == product_type ){
          //  this.unitvalproductErrors = 'Product with same Category & Description was already added';
          //}
        }
          
        
        
      });

    }
    */

    this.unitproductErrors='';
    this.productmaterial_error = '';
    this.productstandardgrade_error = '';
    this.unitproductErrors = '';
    this.productErrors = '';
    this.wastageErrors = '';
    let productStandardListLength = this.productStandardList.length;
    let selStandardListLength = this.selStandardList.length;

    materialpercentage=materialpercentage.toFixed(5);
   
    if(this.unitvalproductErrors != '' || productId <=0 || productId=== null || wastage=='' || product_type==''  || this.productStandardList.length<=0 || this.f.wastage.errors || this.productMaterialList.length<=0 || materialpercentage!= 100){
     
      if(materialpercentage != 100){
        this.productmaterial_error = 'Total material percentage should be equal to 100';
      }
      if(this.productStandardList.length<=0){
        this.productstandardgrade_error = 'Please add standard and label grade';
      }
      if(this.productMaterialList.length<=0){
        this.productmaterial_error = 'Please add product material';
      }
	

      return false;
    }
    let selproduct = this.productList.find(s => s.id ==  productId);
    let selproducttype = this.productTypeList.find(s => s.id ==  product_type);
    
    
    
    let productEntries:any = [];
    let expobject:any=[];
   
    expobject["id"] = selproduct.id;
    expobject["name"] = selproduct.name;
    

    expobject["product_type_id"] = selproducttype.id;
    expobject["product_type_name"] = selproducttype.name;
    expobject["wastage"] = wastage;
    expobject["productMaterialList"] = this.productMaterialList;
    expobject["materialcompositionname"] = materialcompositionname;
    
    
    expobject["productStandardList"] = this.productStandardList;
    productEntries.push(expobject);
    

    let productdatas = [];
    

    productEntries.forEach((val)=>{
      let productStandardList = [];
      val.productStandardList.forEach((listval)=>{
        productStandardList.push({standard_id:listval.standard_id,standard_name:listval.standard_name,label_grade:listval.label_grade,label_grade_name:listval.label_grade_name});
      });
      let productMaterialList=[];
      val.productMaterialList.forEach((listval)=>{
        productMaterialList.push({material_id:listval.material_id,material_name:listval.material_name,material_percentage:listval.material_percentage,material_type_id:listval.material_type_id,material_type_name:listval.material_type_name});
      });
      
      productdatas.push({autoid:autoid,product_id:val.id,name:val.name,wastage:val.wastage,product_type:val.product_type_id,productStandardList:productStandardList,productMaterialList});
    });

    /*
    let formvalue = this.form.value;
    formvalue.products = [];
    formvalue.products = productdatas;
    this.formData.append('formvalues',JSON.stringify(formvalue));
    */
    let pdtdetailsobj:any = {app_id:app_id,id:this.id,products:productdatas};
    if (productdatas.length>0) 
    {
    //console.log(pdtdetailsobj);
   // return false;
      this.loading['button'] = true;
      this.buttonDisable = true;
      this.additionservice.addData(pdtdetailsobj)
        .pipe(first())
        .subscribe(res => {

          if(res.status){
            window.scroll({ 
              top: 0, 
              left: 0, 
              behavior: 'smooth' 
            });
            
            this.id = res.id;
            this.productReset();
            this.showProduct = false;
            //this.enquiryForm.reset();
            //this.submittedSuccess =1;
            this.success = {summary:res.message};
            
            this.loadProductList(res.id);
            
            // setTimeout(() => {
            //   this.router.navigateByUrl('/application/apps/view?id='+res.app_id); 
            // },this.errorSummary.redirectTime);
          }else{
          // this.submittedError =1;
            this.error = {summary:res};
          }
          this.loading['button'] = false;
          this.buttonDisable = false;
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
            this.buttonDisable = false;
        });
    }
    
  }

  removeProduct(content,autoid:number) {

    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
    
    this.modalss.result.then((result) => {
       this.buttonDisable = true;
      this.showProduct = false;
      this.loading['button'] = true;
      this.additionservice.deleteData({autoid:autoid})
        .pipe(first())
        .subscribe(res => {

          if(res.status){
             
            this.productReset();
            this.success = {summary:res.message};
            
            this.loadProductList(res.id);
            
            
          }else{
            this.error = {summary:res};
          }
          this.buttonDisable = false;
          this.loading['button'] = false;

        },
        error => {
            this.buttonDisable = false;
            this.error = {summary:error};
            this.loading['button'] = false;
        });
      
      
    }, (reason) => {
      
    });
    
  }
  productdetails:any;

  loadProductList(id){
    this.productEntries = [];
    this.loading['data'] = true;
    this.loading.company = true;


    this.additionservice.getAppData({id:id}).pipe(first())
    .subscribe(res => {
      if(res.status)
      {
        this.appdata = res.appdata;
        this.applicationdata = res.appdetails;

        this.productdetails = res.productdetails;

       

        if(res.productdetails && res.productdetails.app_id){
          //this.oncompanychange(res.productdetails.app_id);
          this.form.patchValue({
            app_id:res.productdetails.app_id,
          });
        }
        
        this.productListDetails = res.productdetails.productDetails;
        this.productEntries = res.productdetails.products;
        
        let unitids = [];
         this.applicationdata.units.forEach(unit=>{
            
            this.selUnitStandardList[unit.id] = [];
            unitids.push(unit.id);
            if( res.units &&  res.units[unit.id] && res.units[unit.id].product_details){
              this.unitProductList[unit.id] = [...res.units[unit.id].product_details];
            }else{
              this.unitProductList[unit.id] = [];
            }
            let appstandards = unit.standards; 
            this.unitstandard[unit.id] = [...unit.standards];
            if(appstandards.length>0){
              if( this.productListDetails){
                let selunitlist = this.productListDetails.filter(x =>  appstandards.includes(x.standard_id));
                 
                if(selunitlist){

                 this.selUnitStandardList[unit.id] = selunitlist;
                }
              }
            }
            

        })

        this.getPdtStandardList(unitids);
      }else
      {           
        this.error = {summary:res};
      }
      this.loading.company= false;
      this.loading['data'] = false;
    },
    error => {
        this.error = error;
        this.loading.company= false;
        this.loading['data'] = false;
    });
    /*
    this.additionservice.getAppData({id:this.id}).pipe(first())
    .subscribe(res => {
      this.loading['data'] = false;
      if(res.status)
      {
        this.appdata = res.appdata;
         
        
        this.productEntries = res.productdetails.products;
      }else
      {           
        this.error = {summary:res};
      }
    },
    error => {
        this.error = error;
        this.loading['data'] = false;
    });
    */

     
  }

  touchProduct(){
    this.f.product.markAsTouched();
    this.f.wastage.markAsTouched();
    
    this.f.product_type.markAsTouched();
  }
  addProductDetails(){
    let pdt_index =0;
    this.productListDetails=[];
    this.productEntries.forEach((selproduct)=>{
      let entry = [];
      entry["id"] = selproduct.id;
      entry["name"] = selproduct.name;
      entry["product_type_id"] = selproduct.product_type_id;
      entry["product_type_name"] = selproduct.product_type_name;
      entry["wastage"] = selproduct.wastage;
      entry["productMaterialList"] = selproduct.productMaterialList;
      entry["materialcompositionname"] = selproduct.materialcompositionname;
      let prdexpobject = {...entry};

      selproduct.productStandardList.forEach(selstandard=>{
        
        prdexpobject["standard_id"] = selstandard.standard_id;
        prdexpobject["standard_name"] = selstandard.standard_name;//this.registrationForm.get('expname').value;
        prdexpobject["label_grade"] = selstandard.label_grade;
        prdexpobject["label_grade_name"] = selstandard.label_grade_name;
        prdexpobject["pdt_index"] = pdt_index;
        pdt_index++;
        this.productListDetails.push({...prdexpobject});
         
      })
    })
  }

  getStandardGrade(standardid){
    this.labelGradeList = [];	
	this.form.patchValue({label_grade:''});
	
	if(standardid>0)
	{
		this.loading['labelgrade'] = 1;
		this.productService.getStandardLabel(standardid).pipe(first()).subscribe(res => {
		  this.labelGradeList = res['data'];   
		  this.loading['labelgrade'] = 0;   
		});
	}
  }

  getProductType(productid)
  {
    this.productTypeList = [];
    this.materialList = [];
    this.productMaterialList = [];
    this.form.patchValue({product_type:'',material:'',material_type:''});
      
    if(productid>0)
    {	
      this.loading['producttype'] = 1;
      
      this.productService.getProductTypes(productid).pipe(first()).subscribe(res => {
        this.productTypeList = res['data']; 
        this.materialList = [];
        this.productMaterialList = [];
        this.loading['producttype'] = 0;
      });
    }	
  }

  getProductMaterial(product_typeid,makeempty=1)
  {
    this.form.patchValue({material:'',material_type:''});

      if(product_typeid>0)
      {
        this.loading['material'] = 1;
        this.productService.getMaterial(product_typeid).pipe(first()).subscribe(res => {
          this.materialList = res;
          this.loading['material'] = 0;
          if(makeempty)
          {
            this.productMaterialList = [];
          }  
        });
      }
  }
 
  removeProductMaterial(Id:number) 
  {
    let index= this.productMaterialList.findIndex(s => s.material_id ==  Id);
    if(index != -1)
      this.productMaterialList.splice(index,1);
  }

  touchProductMaterial()
  {
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
   let material = this.form.get('material').value;
   let material_type = this.form.get('material_type').value;
   let material_percentage = this.form.get('material_percentage').value;

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
   
   this.form.patchValue({
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
 editProductMaterial(Id:number){
    this.showProduct= true;
    let mat= this.productMaterialList.find(s => s.material_id ==  Id);
   
   this.form.patchValue({
     material: mat.material_id,
     material_type: mat.material_type_id,
     material_percentage:mat.material_percentage
   });
 }

 setshowProduct(){
    if(this.showProduct){
      this.showProduct = false;
    }else{
      this.showProduct = true;
    }
  }

 productId:number=0;
 editProduct(index:number){
    this.productIndex = index;
    let prd= this.productEntries[index];
    this.getProductTypeOnEdit(prd.id,prd.product_type_id);
    this.form.patchValue({
      autoid:prd.autoid?prd.autoid:'',
      product: prd.id,
      wastage:prd.wastage,
      product_type:prd.product_type_id,
    });
    
     
    this.productStandardList = [...prd.productStandardList];
    this.productMaterialList = [...prd.productMaterialList];
    
    this.showProduct = true;

    this.showCert = false;
    
  }

  getProductTypeOnEdit(productid,product_typeid){
    this.loading['producttype'] = 1;
    this.productService.getProductTypes(productid).pipe(first()).subscribe(res => {
      this.productTypeList = res['data']; 
      this.getProductMaterial(product_typeid,0);
      this.loading['producttype'] = 0;
    });
  }

 grade_error:any;
/*
  Product Standard Section
  */
 productStandardList:Array<any> = [];
 productstandard_error = '';
 labelGradeList:LabelGrade[]=[];
 productstandardgrade_error = '';
 removeProductStandard(standardId:number) {
  let index= this.productStandardList.findIndex(s => s.standard_id ==  standardId);
  if(index != -1)
    this.productStandardList.splice(index,1);
}
touchProductStandard(){
  this.f.composition_standard.markAsTouched();
  this.f.label_grade.markAsTouched();
}
addProductStandard(){
  this.productstandardgrade_error = '';
  this.f.composition_standard.setValidators([Validators.required]);
  this.f.label_grade.setValidators([Validators.required]);

  this.f.composition_standard.updateValueAndValidity();
  this.f.label_grade.updateValueAndValidity();

  this.touchProductStandard();
  let standardId = this.form.get('composition_standard').value;
  let label_grade = this.form.get('label_grade').value;

  let selstandard = this.selStandardList.find(s => s.id ==  standardId);
  let sellabel = this.labelGradeList.find(s => s.id ==  label_grade);
  this.productstandard_error = '';
  
  if(standardId=='' || label_grade==''){
    //this.productstandard_error = 'Please select the Standard';
    return false;
  }
  
  let entry= this.productStandardList.findIndex(s => s.standard_id ==  standardId);
  let expobject:any=[];
  expobject["standard_id"] = selstandard.id;
  expobject["standard_name"] = selstandard.name;//this.registrationForm.get('expname').value;
  expobject["label_grade"] = sellabel.id;
  expobject["label_grade_name"] = sellabel.name;
  if(entry === -1){
    this.productStandardList.push(expobject);
  }else{
    this.productStandardList[entry] = expobject;
  }
  
  this.form.patchValue({
    composition_standard: '',
    label_grade:''
  });
  this.f.composition_standard.setValidators([]);
  this.f.label_grade.setValidators([]);

  this.f.composition_standard.updateValueAndValidity();
  this.f.label_grade.updateValueAndValidity();

  this.labelGradeList = [];

this.std_with_product_std_error='';
}
editProductStandard(standardId:number){
  let prd= this.productStandardList.find(s => s.standard_id ==  standardId);

  this.getStandardGrade(prd.standard_id);

  this.form.patchValue({
    composition_standard: prd.standard_id,
    label_grade:prd.label_grade
  });
}


productReset(){
  /*
  this.f.product.setValidators([]);
  this.f.wastage.setValidators([]);
  this.f.product_type.setValidators([]);
  this.f.material.setValidators([]);
  this.f.composition_standard.setValidators([]);
  this.f.label_grade.setValidators([]);

  this.form.patchValue({
    product: '',
    wastage:'',
    product_type:'',
    material:'',
    composition_standard: '',
    label_grade:'',
    autoid: '',
    
  });
  */

  let app_id:any = this.form.get('app_id').value;
  this.form.reset();
  this.form.patchValue({
    app_id: app_id,
  });


  this.productStandardList = [];
  this.labelGradeList = [];
  this.productTypeList = [];
  this.productIndex=null;
  this.materialList = [];
  this.productMaterialList = [];
  
  this.productmaterial_error = ''; 
  this.productErrors = '';
  this.wastageErrors = '';
  this.productstandard_error= '';
  this.productstandardgrade_error='';
  
  this.f.product.setValidators([Validators.required]);
  this.f.wastage.setValidators([Validators.required,Validators.pattern('^[0-9]+(\.[0-9]{1,2})?$'),Validators.max(100)]);
  this.f.product_type.setValidators([Validators.required]);
  

  this.f.product.updateValueAndValidity();
  this.f.wastage.updateValueAndValidity();
  this.f.product_type.updateValueAndValidity();
}

showProduct = false;
unitIndex:number;
showCert = false;
showProductFn(){
  
  this.productIndex = null;
  this.productReset();
  if(this.showProduct){
    this.showProduct = false;
  }else{
    this.showProduct = true;
  }
  this.unitIndex = null;
  this.showCert = false;
}
  

  /*
  filterProduct(){
    
    let appstandards = this.selUnitStandardList;//this.standardsChkDb.concat(this.enquiryForm.get('standardsChk').value);
    if(appstandards.length>0){
      return this.productListDetails.filter(x =>  appstandards.includes(""+x.standard_id+""));
    }
    
  }
  */
  unitpdterror:any;
  unitpdtsuccess:any;
  onSubmit(type)
  {

    //this.unitProductList[unit.id]
    let formerror=false;
    this.applicationdata.units.forEach(unit=>{
      let unitentries = this.unitProductList[unit.id];
      if(unitentries.length<=0){
        this.newproducterror[unit.id] = 'true';
        formerror=true;
      }else{
        this.newproducterror[unit.id] = '';
      }
    });
    if(formerror){
       this.unitpdterror = {summary:this.errorSummary.errorSummaryText}
      return false;
    }

    if (1) {
      

 
    this.buttonDisable = true;       
       this.loading['button'] = true;
      let reqobj:any={};
      reqobj['units'] = [];
      this.applicationdata.units.forEach(unit=>{
      let productentries=[];
       
      reqobj['units'].push({unit_id:unit.id,products:this.unitProductList[unit.id]});
    });

      
      
      reqobj['app_id'] = this.app_id;
            
      reqobj['type'] = type;
      reqobj['id'] = this.id;
      
      this.additionservice.updateAppProductData(reqobj)
      .pipe(first())
      .subscribe(res => {

          if(res.status){
            
            this.unitpdtsuccess = {summary:res.message};
            this.buttonDisable = false;           
            setTimeout(() => {
              if(type == 'draft'){
                //this.router.navigateByUrl('/change-scope/product-addition/list'); 
              }else{
                this.router.navigateByUrl('/change-scope/product-addition/view?id='+res.id+'&app_id='+res.app_id); 
              }
              
            }, this.errorSummary.redirectTime);           
          }else{
            this.buttonDisable = false;
            //this.submittedError =1;
            this.unitpdterror = {summary:res};
          }
          this.loading['button'] = false;
         
      },
      error => {
          this.unitpdterror = {summary:error};
          this.loading['button'] = false;
          this.buttonDisable = false;
      });
      

    }
  }

  selectedProductIds:any = [];
  onProductCheckboxChange(id: number, isChecked: boolean) {
    if (isChecked) {
      this.selectedProductIds.push(id);
    } else {
      let index = this.selectedProductIds.findIndex(x => x == id);
      if(index){
        this.selectedProductIds.removeAt(index);
      }
    }

  }
  filterProductStandard(stdId,unitid){
    
    const unitProductIndex = this.unitProductList[unitid].map(x=>x.pdt_index).map(String);
    return this.productListDetails.filter(x =>  stdId==x.standard_id && !unitProductIndex.includes(""+x.pdt_index+"")  );
    
  }
  unitproductremainingstatus=true;
  selProductStandardList:Array<any> = [];
  logsuccess:any;
  curunitid:any='';
  addUnitProductPop(content,unit)
  {	
   
    this.curunitid = unit.id;
    if(this.productListDetails && this.productListDetails.length>0){
      this.productListDetails.forEach(pdtdata=>{
        this.popunitproductlist['input_weight'+pdtdata.pdt_index] = false;
      })
    }

    if(unit.unit_type==1)
    {		
      this.selProductStandardList=unit.standarddetails;
    }else{
      this.selProductStandardList=unit.standarddetails;
    }
    /*if(this.currentunittype==2)
    {		
      this.selProductStandardList=this.selUnitStandardList;
    }else{
      this.selProductStandardList=this.selStandardIds;
    }
    */
    
    this.unitproductremainingstatus=true;
    let productfilters = this.selUnitStandardList[unit.id];
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
  addUnitProductFromPop(unit_id){
    		
    this.productpopuperror='';
      if(this.selectedProductIds.length<=0){
        this.productpopuperror = {summary:"Please select the product"};
        return false;
      }  
    
      this.selectedProductIds.forEach(pdt=>{
        let selunitproduct = this.productListDetails.find(s => s.pdt_index ==  pdt); 
        let entry;
        if(this.unitProductList && this.unitProductList[unit_id]){
          entry= this.unitProductList[unit_id].find(s => s.pdt_index ==  pdt);
        }
        
        if(entry === undefined){
          if(this.unitProductList[unit_id]=== undefined){
            this.unitProductList[unit_id] = [];
          }
          console.log(selunitproduct);
          //this.unitProductList.push(selunitproduct);
          this.unitProductList[unit_id].push({...selunitproduct,addition_type:1});
        }
      });
    
    this.modalss.close();   
  }
}

