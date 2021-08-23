import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { ProductTypeService } from '@app/services/master/producttype/producttype.service';
import { ProductService } from '@app/services/master/product/product.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';
import { Product } from '@app/models/master/product';
import { IssueService } from '@app/services/itcenter/issue.service';
import { AuthenticationService } from '@app/services';


@Component({
  selector: 'app-add-it-center',
  templateUrl: './add-it-center.component.html',
  styleUrls: ['./add-it-center.component.scss']
})
export class AddItCenterComponent implements OnInit {
  
  title = 'Add Issue Description';
  btnLabel = 'Save';
 // productList:Product[];
  
  availabeCat = [

    { "id":1,"name":"Master Management"},
    {"id":2,"name":"Enquiry Management"},
    { "id":3,"name":"Application Management"},
    {"id":4,"name":"Quotation Management"},
    {"id":5,"name":" Invoice Management"},
    {"id":6,"name":"Audit Management"},
    {"id":7,"name":"Unannounced Audit"}, 
    {"id":8,"name":"Certification Management"},
    {"id":9,"name":"TC Management"} ];  
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
  userdetails: any;
  issueFileNames: any = [];
  @ViewChild('myInput', {static: false})
  myInputVariable: ElementRef;

  
  constructor(public authservice: AuthenticationService, private router: Router,private fb:FormBuilder,
    public service: IssueService, private errorSummary: ErrorSummaryService) { }

  ngOnInit() {

  
     this.authservice.currentUser.subscribe(x => {
      if(x){
         
        let user = this.authservice.getDecodeToken();
     
        this.userdetails= user.decodedToken.displayname;
        
      }
    }); 
    let date = Date.now().toString()


	this.form = this.fb.group({
      issue_id:['',[Validators.required]],      
      name:[date,[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255), Validators.pattern("^[a-zA-Z0-9 \'\-+%/&,().-]+$")]],
	    description:['',[this.errorSummary.noWhitespaceValidator]], 	  
	    status:['',[Validators.required, this.errorSummary.noWhitespaceValidator]], 	  
	    from: ['',[Validators.required, this.errorSummary.noWhitespaceValidator]], 
	    contact:['',[Validators.required, this.errorSummary.noWhitespaceValidator]], 	  
	    priority:['',[Validators.required, this.errorSummary.noWhitespaceValidator]], 	  
	   	issueFile: ["", [Validators.required]] 
    });

    if(this.service.editData) {
      console.log(this.service.editData)
      this.form.patchValue({
        issue_id: this.service.editData.issue_type,
        name: this.service.editData.ticket,
        description: this.service.editData.description,
        status: this.service.editData.status,
        from: this.service.editData.created_from,
        contact: this.service.editData.created_name,
        priority: this.service.editData.priority,
        issueFile: [],
      })
    }
	
  }

  get f() { return this.form.controls; }
  
  onSubmit(){
    let date =  new Date();
 
    let data = {
      id: this.service.editData ? this.service.editData.id: "",
      ticket: this.form.value.name,
      issue_type: this.form.value.issue_id,
      description: this.form.value.description,
      status: this.form.value.status,
      created_date: date.getDay() + "-" +date.getMonth() + "-" + date.getFullYear(),
      created_name: this.userdetails,
      created_from: this.form.value.from,
      contact: this.form.value.contact,
      priority:this.form.value.priority,
      file: this.service.editData ? " " : this.issueFileNames.length == 0? "": this.issueFileNames,
      questionone: "test",
      questiontwo: "test",
      downtimestart: "test",
      downtimeend: "test",
      
  }
  // this.form.value.date = new Date()
  // this.form.value.by = this.userdetails
  // this.service.list.push(this.form.value)

      let fformData = {
      issue: [data],
     
    };

    let formData = new FormData();


    for (let i = 0; i <  this.issueFileNames.length; i++) {

      formData.append("filesaray" + i,  this.issueFileNames[i])
    } 
    if(this.service.editData) {
        formData.append("filesaray" + 0,this.issueFileNames )
    }

     
    
    formData.append("formvalues", JSON.stringify(fformData));

    this.service.createIssue(formData).pipe(first()).subscribe(res => {
        if(res.status === 1) {
          this.service.editData  = null
            	 setTimeout(()=>this.router.navigate(["/itcenter/list-it-center"]));
           
        }
    })
	  
	  
  }

  fileChange(element) {
    let files = element.target.files;
    for(let xfile of files) {
      let fileextension = xfile.name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
		let file = xfile;
		let reader = new FileReader();
		reader.readAsDataURL(file);
		
		reader.onloadend = ()=>{
     		const bits:any = reader.result; // readyState will be 2
			
			/*
			let ob = {
				name:file.name,
				data:bits
			};
			*/			
			
      //this.formData.append("questionfile["+stdqid+"]", bits, files[0].name);
      // {name:file.name,file:bits,type:1}
        
      this.issueFileNames.push(xfile) ;
      this.myInputVariable.nativeElement.value = "";
    
    }
  }

    }
    
}



 

}
