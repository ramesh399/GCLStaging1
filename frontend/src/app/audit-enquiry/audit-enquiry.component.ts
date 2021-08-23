import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { CountryService } from '@app/services/country.service';
import { StandardService } from '@app/services/standard.service';

import { EnquiryDetailService } from '@app/services/enquiry-detail.service';

//import { Country } from '@app/services/country';
import { Country } from '@app/models/master/country';
import { State } from '@app/services/state';
import { Standard } from '@app/services/standard';
import { first } from 'rxjs/operators';

import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ReCaptchaV3Service } from 'ng-recaptcha';
import * as $ from 'jquery';

@Component({
  selector: 'app-audit-enquiry',
  templateUrl: './audit-enquiry.component.html',
  styles: ['./audit-enquiry.component.css'],
  providers: [EnquiryDetailService]
})
export class AuditEnquiryComponent implements OnInit {

  countryList:Country[];
  stateList:State[];
  companyStateList:State[];
  standardList:Standard[];
  enquiryForm : FormGroup;
  selectedOrderIds:any = {};
  standardsLength:number=0;
  submitted = false;
  submittedSuccess:number = 0;
  submittedError:number = 0;
  loading = false;
  error ='';
  recaptchaErrors = '';
  recaptchaResponseStatus=true;
	/*
  resolved(captchaResponse: string) {
       	this.recaptchaResponseStatus=true;
		this.recaptchaErrors = '';
  }	
  */

  constructor(private fb:FormBuilder,private countryservice: CountryService,private standards: StandardService, private enquiry:EnquiryDetailService,private errorSummary: ErrorSummaryService,private recaptchaV3Service: ReCaptchaV3Service) { }

  

  private mapToCheckboxArrayGroup(data: string[]): FormArray {
      return this.fb.array(data.map((i) => {
        return this.fb.group({
          name: i,
          selected: false
        });
      }));
  }

  
  year:any='';
  ngOnInit() {

    this.countryservice.getCountry().subscribe(res => {
      this.countryList = res['countries'];
    });
    this.enquiry.getYear().subscribe(res => {
      this.year = res;
    });
    this.standards.getStandard().subscribe(res => {
      //this.standardList = res;
      //let control = <FormArray>this.enquiryForm.get('company.standardsChk');
      this.standardList = res['standards'];
      /*res.forEach(o => {
        const control = new FormControl(); // if first item set to true, else false
        (this.enquiryForm.get('company.standardsChk') as FormArray).push(control);
      });
      */
      
    });

    this.enquiryForm = this.fb.group({

      //firstName:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],
      //lastName:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\]+$")]],
      //email:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.email,Validators.maxLength(255)]],
      //country:['',[Validators.required]],
      //states:['',[Validators.required]],
      //phone_code:[''],
	  //telePhone:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.pattern("^[0-9\-]*$"), Validators.minLength(8), Validators.maxLength(15)]],
      company: this.fb.group({  
        companyName:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
		contactName:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255)]],
        //contactName:['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.maxLength(255),Validators.pattern("^[a-zA-Z \.\'\]+$")]],
        ctelePhone:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.pattern("^[0-9\-\+]*$"), Validators.minLength(8), Validators.maxLength(15)]],
        companyEmail:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.email,Validators.maxLength(255)]],
        //website:[''],
		website:['',[Validators.pattern("^(http[s]?:\\/\\/){0,1}(www\\.){0,1}[a-zA-Z0-9\\.\\-]+\\.[a-zA-Z]{2,5}[\\.]{0,1}$"),this.errorSummary.noWhitespaceValidator]],
        address1:['',[Validators.required,this.errorSummary.noWhitespaceValidator]],
        address2:[''],        	
		city :['',[Validators.required,this.errorSummary.noWhitespaceValidator,Validators.pattern("^[a-zA-Z .]+$"),Validators.maxLength(255)]],
        zipcode:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.pattern("^[a-zA-Z0-9]+$"),Validators.maxLength(15)]],		
        companyCountry:['',[Validators.required]],
		companyStates:['',[Validators.required]],
        cphone_code:[''],
		//noOfSites:['',[this.errorSummary.noWhitespaceValidator,Validators.pattern("^[0-9]*$"),Validators.maxLength(10),Validators.min(1)]],
        // standardsChk: new FormArray([]),
        //standardsChk: this.fb.array(this.standardList.map(s => this.fb.control(false)),[ Validators.required ]),
        // standardsChk: this.fb.array(this.standards.getStandard().subscribe(s => this.fb.control(false)),[ Validators.required ]),
      }),
      standardsChk:  this.fb.array([]),
      //noOfEmployees:[''],
      //noOfSites:['',[this.errorSummary.noWhitespaceValidator,Validators.pattern("^[0-9]*$"),Validators.maxLength(10),Validators.min(1)]],
      //companyOperations:[''],
      //otherInformation:[''],
      agreement:['',[Validators.required]],
      token:['']
  
    });
    
  }

  ngAfterViewInit() {
    setTimeout(() => {
	    $('.grecaptcha-badge').css('visibility', 'visible');
	 }, 500);	
  }

  getStateList(id,arg){
	console.log(arg);
	if(arg==1)
	{
		this.stateList = [];
		this.enquiryForm.patchValue({phone_code: '',states:''});
		  
		if(id){
		  let countrysel = this.countryList.find(s=>s.id==id);
		  this.enquiryForm.patchValue({phone_code: '+ '+countrysel.phonecode});
			this.countryservice.getStates(id).subscribe(res => {
			this.stateList = res['data'];
		  });
		}
	}else{
		this.companyStateList = [];
		this.enquiryForm.patchValue({cphone_code: '',companyStates:''});
		  
		if(id){
		  let countrysel = this.countryList.find(s=>s.id==id);
		  this.enquiryForm.patchValue({cphone_code: '+ '+countrysel.phonecode});
			this.countryservice.getStates(id).subscribe(res => {
			this.companyStateList = res['data'];
		  });
		}
	}	
  } 
  
  
  getPhonecode(id){
    if(id){
      let countrysel = this.countryList.find(s=>s.id==id);
      this.enquiryForm.patchValue({company:{cphone_code: '+ '+countrysel.phonecode}});  
    }else{
      this.enquiryForm.patchValue({company:{cphone_code: ''}});  
    }
  }
  
  ngOnDestroy() {
    // Unsubscribe when the component is destroyed
    //this.countryservice.getStates(id).unsubscribe();

  }

  public executeImportantAction(): any {
    // console.log('sdasd'); return false;
     this.recaptchaV3Service.execute('importantAction')
        .pipe(first())
        .subscribe((token) => {
           this.onSubmit(token);
     });
 }
  get country() {
    return this.enquiryForm.get('country');
  }
  get states() {
    return this.enquiryForm.get('states');
  }
  
  /*
  get telePhone() {
    return this.enquiryForm.get('telePhone');
  }
  */
  
  /*
  get noOfSites() {
    return this.enquiryForm.get('company.noOfSites');
  }
  */
  
  get zipcode() {
    return this.enquiryForm.get('company.zipcode');
  }
  get website() {
    return this.enquiryForm.get('company.website');
  }
  get companyName() {
    return this.enquiryForm.get('company.companyName');
  }
  get companyEmail() {
    return this.enquiryForm.get('company.companyEmail');
  }  
  get ctelePhone() {
    return this.enquiryForm.get('company.ctelePhone');
  }
  get contactName() {
    return this.enquiryForm.get('company.contactName');
  }
  get address1() {
    return this.enquiryForm.get('company.address1');
  }
  get city() {
    return this.enquiryForm.get('company.city');
  }
  get agreement() {
    return this.enquiryForm.get('agreement');
  }
  
  get companyCountry() {
    return this.enquiryForm.get('company.companyCountry');
  }  
  
  get companyStates() {
    return this.enquiryForm.get('company.companyStates');
  }
  
  get f() { return this.enquiryForm.controls; }
  
  onChange(id: number, isChecked: boolean) {
    //const emailFormArray = <FormArray>this.myForm.controls.useremail;
    //const standardsFormArray = <FormArray>this.enquiryForm.get('company.standardsChk');
    const standardsFormArray = <FormArray>this.enquiryForm.get('standardsChk');

    if (isChecked) {
      standardsFormArray.push(new FormControl(id));
    } else {
      let index = standardsFormArray.controls.findIndex(x => x.value == id);
      standardsFormArray.removeAt(index);
    }
    this.standardsLength = this.enquiryForm.get('standardsChk').value.length;
  }


  onSubmit(token){
    
    this.standardsLength = this.enquiryForm.get('standardsChk').value.length;
    this.submitted = true;
    this.submittedError = 0;
	/*
	if(!this.recaptchaResponseStatus)
	{
		this.recaptchaErrors = 'Invalid Captcha';		
	}else{
		this.recaptchaErrors = '';
  }
  */
	
    /*
    this.selectedOrderIds = this.enquiryForm.get('company.standardsChk').value
      .map((v, i) => v ? this.standardList[i].id : null)
      .filter(v => v !== null);
*/
    //this.enquiryForm.get('company.standardsChk')['controls'].value = [...this.selectedOrderIds];
      //this.enquiryForm.get('company.standardsChk').value = [...this.selectedOrderIds];
    //console.log( this.enquiryForm.value); 

    //Object.assign(this.enquiryForm.value, {standardsChkVal: this.selectedOrderIds});
    
    if (this.enquiryForm.valid) {
      //console.log('form submitted');
      if(this.standardsLength<=0){
        return false;
      }
      this.loading = true;
      this.enquiryForm.patchValue({token: token});
	    this.enquiry.addEnquiry(this.enquiryForm.value)
      .pipe(first())
      .subscribe(res => {
        
          if(res.status){
            this.enquiryForm.reset();
            this.submittedSuccess =1;
          }else if(res.status == 0){
            this.submittedError =1;
            this.error = res.msg;
          }else{
            this.submittedError =1;
            this.error = res;
          }
         
      },
      error => {
          this.error = error;
          this.loading = false;
      });
      //console.log('sdfsdfdf');
    } else {
      //console.log('form error');
	    this.submittedError =1;
      this.error = 'Please fill all the mandatory fields (marked with *)';
      setTimeout(() => { this.error=''; }, 2000);
      this.errorSummary.validateAllFormFields(this.enquiryForm);      
    }
  }

}