import { ElementRef, ViewChild } from "@angular/core";
import { Component, OnInit } from "@angular/core";
import {saveAs} from 'file-saver';
import {
  FormGroup,
  FormBuilder,
  Validators,
  FormControl,
  FormArray,
} from "@angular/forms";
import { ErrorSummaryService } from "@app/helpers/errorsummary.service";
import { Translator } from "@app/models/library/translator";
import { CountryService } from "@app/services/country.service";
import { TranslatorService } from "@app/services/library/translator/translator.service";
import { UserService } from "@app/services/master/user/user.service";
 
import { Observable } from "rxjs";
import { first } from "rxjs/operators";
import { NgbModal } from "@ng-bootstrap/ng-bootstrap";
import { AuthenticationService } from "@app/services";

@Component({
  selector: "app-list-translator",
  templateUrl: "./list-translator.component.html",
  styleUrls: ["./list-translator.component.scss"],
})
export class ListTranslatorComponent implements OnInit {
  title = "Translator";
  viewDetails = false;
  currentId = '';
  currentTranslator: any = {};

  translators$: any;
  @ViewChild('myInput', {static: false})
myInputVariable: ElementRef;
  editStatus: boolean = false;
  transLang: any;
  statusFilter: string = "true";
  statusList: any[] = [
    {
      key: "true",
      value: "Active"
    },
    {
      key: "false",
      value: "InActive"
    },
  ]

  form: FormGroup;
  countryList: any; 
  modalss:any;
  
  translatorFileNames: any[] = [];
  userType: any;
  

  get f() {
    return this.form.controls;
  }

  get filteredTrans () {
 
    return this.translators$.filter(el => {
      return el.status == this.statusFilter || this.statusFilter == ""
    })
  }

  constructor(
    private fb: FormBuilder,
    private countryservice: CountryService,
    public errorSummary: ErrorSummaryService,
    private service: UserService,
    private modalService: NgbModal,
     private authservice:AuthenticationService,
    private transService: TranslatorService
  ) {
      
    transService.translators$.subscribe((res) => {
       
      this.translators$ = res;

    })

    this.authservice.currentUser.subscribe(x => {
      if(x){
        
        
        let user = this.authservice.getDecodeToken();
         
      this.userType= user.decodedToken.roleid;
       // this.userdetails= user.decodedToken;
        
      }else{
       // this.userdecoded=null;
      }
    });
  }

  ngOnInit() {
    this.form = this.fb.group({
      country: ["", [Validators.required]],
      suppliername: [
        "",
        [
          Validators.required,
          
        ],
      ],
      employment: [
        "",
        [Validators.required],
      ],
      language1: [
        "",
        [
          Validators.required
        
        ],
      ],
      email: [
        "",
        [
          Validators.required,
         
          Validators.email
          
        ],
      ],
      phone: [
        "",
        [
          Validators.required,
          this.errorSummary.noWhitespaceValidator,
          Validators.pattern("^[0-9-+]*$"),
          Validators.minLength(8),
          Validators.maxLength(15),
        ],
      ],
      
      language4: [
        "",
        [
          
        ],
      ],
      language3: [
        "",
        [
          
        ],
      ],
      language2: ["", []],
      translatorFileNames: ["", Validators.required],
      status: [null, [Validators.required]],
    });
    this.countryservice.getCountry().pipe(first()).subscribe((res) => {
      this.countryList = res["countries"];
    });
    this.transService.getTranslatorLang().pipe(first()).subscribe(el => {
      this.transLang = el.language;
    })
 
  }
  changeStatus (item) {

    item.status = item.status === "false" ? "true": "false"
    this.editTranslator(item)
    this.onSubmit()

  }
  editTranslator(item) {
     
    item.suppliername = item.surname;
    this.translatorFileNames = item.filename
    item.Status = item.status;
    this.editStatus = true
    this.form.patchValue(item)
    
    this.currentId = item.id;
  }
  showDetails(item) {
    this.currentTranslator = item
    this.viewDetails = true
  }
  resetForm() {
    this.form.reset();
      
   
    this.editStatus = false;
  }

  downloadUploadedFile(index) {

    let file = this.currentTranslator.filename
      
    this.service.downloadTranslator({  id:this.currentTranslator.id, filename: file[index] }).pipe(first()).subscribe(res => {
        let fileextension = this.currentTranslator.filename[index].split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(this.currentTranslator.filename[index]);
      this.modalss.close();
      saveAs(new Blob([res],{type:contenttype}),this.currentTranslator.filename[index]);
    }, err => {
      console.log(err)
    } )
  }

  removeTranslator (item) {

    let formData = new FormData();
    
  //  formData.append("formvalues", JSON.stringify());
    this.service.deleteTranslator({actiontype:"translator", id:item.id, typeaction: "", user_id: '67'}).pipe(first()).subscribe(res => {
       this.transService.customSearch();
    } )

  }

  openmodal(content,arg='') {
    this.modalss = this.modalService.open(content, {size:'xl',ariaLabelledBy: 'modal-basic-title',centered: true});
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
       
      this.translatorFileNames.push(xfile) ;
       this.myInputVariable.nativeElement.value = "";
    
    }
  }

    }
    
}

removeFile(item) {
   
  this.translatorFileNames.splice(item, 1)
}
 
  onSubmit() {
     
    if(this.editStatus)
    this.form.value.id = this.currentId 
     
    let fformData = {
      translator: [this.form.value],
      actiontype: "translator",
    "length": this.translatorFileNames.length,
      id: "67"
    };

    let formData = new FormData();


    for (let i = 0; i <  this.translatorFileNames.length; i++) {

      formData.append("filesaray" + i,  this.translatorFileNames[i])
    }

     
    
    formData.append("formvalues", JSON.stringify(fformData));
    this.service
      .updateUserData(formData)
      .pipe(first())
      .subscribe((res) => {
          if(this.editStatus)
          this.editStatus = false
          this.transService.customSearch();
          this.form.reset();
          this.translatorFileNames = []
          this.myInputVariable.nativeElement.value = "";
          this.form.value.translatorFileNames = ""
           this.currentId = null
          }, (err) => {
            this.transService.customSearch();
            this.form.reset();
            this.translatorFileNames = []
            this.myInputVariable.nativeElement.value = "";
            this.form.value.translatorFileNames = ""
            this.editStatus = false
            this.form.reset()
             this.currentId = null
      });
  }
}
