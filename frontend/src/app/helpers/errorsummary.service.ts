import { Injectable } from '@angular/core';
import { Observable, throwError, timer } from 'rxjs';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray, AbstractControl } from '@angular/forms';
import { NgbModal, ModalDismissReasons, NgbModalOptions } from '@ng-bootstrap/ng-bootstrap';

@Injectable({
  providedIn: 'root'
})

export class ErrorSummaryService {

  errorSummaryText = 'Please fill all the mandatory fields (marked with *)';
  setTimeoutTime = 2500;
  redirectTime = 2500;
  errormessageTimeoutTime = 5000
  pageLimit = 50;
  //subscription:any='';
  
    
  public modalOptions:NgbModalOptions = {
	backdrop:'static',
	backdropClass:'customBackdrop'
  }
	
	public statusList = {'active':0,'inactive':1};
	public resourceAccess = {'all':1,'custom':2};
		
	  public validDocs = ['pdf','docx','doc','jpeg','jpg','png','xls','xlsx','ppt','pptx'];

	  public imgvalidDocs = [ 'jpeg','jpg','png'];

	   public allvalidDocs = ['pdf','docx','doc','jpeg','jpg','png','xls','xlsx'];
	  
	  public docsContentType = {'pdf':'application/pdf','docx':'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
	  ,'doc':'application/msword'
	  ,'txt':'text/plain'
	  ,'png' : 'image/png'
	  ,'jpeg' : 'image/jpeg'
	  ,'jpg' : 'image/jpeg'
	  ,'xls' : 'application/vnd.ms-excel'
  	  ,'xlsx' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	  'ppt' : 'application/vnd.ms-powerpoint',
	  'pptx' : 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
	};
	
	checkValidDocs(extension,validDocs=this.validDocs){
		if(extension){
			extension = extension.toLowerCase();
			if(validDocs.includes(extension))
	    	{
	    		return true;
	    	}else{
	    		return false;
	    	}
    	}else{
    		return false;
    	}
	}

	getContentType(filename){
		let extension = filename.split('.').pop(); 
		if(extension){
			extension = extension.toLowerCase();
			return this.docsContentType[extension];
    	}
	}
  
  constructor() { }
	
	displayDateFormat(arg)
  {
		var monthNames = [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ];

		var date = new Date(arg);
		const day = ('0' + arg.getDate()).slice(-2);
		const month = ('0' + (arg.getMonth() + 1)).slice(-2);;
		const year = arg.getFullYear();
		let monthname = monthNames[parseInt(month) - 1];
		// Return the format as per your requirement
		//return monthname+' '+day+', '+year;
		// Return the format as per your requirement
		return `${monthname} ${day}, ${year}`;


    /*var date = new Date(arg);
    let day =('0' +date.getDate()).slice(-2);
    let month = ('0' + (date.getMonth() + 1)).slice(-2);
    let year = date.getFullYear();
	
		return month+'/'+day+'/'+year;
		*/
    //return '' + year + '-' + (month<=9 ? '0' + month : month) + '-' + (day <= 9 ? '0' + day : day);
	}
	editDateFormat(arg)
  {
		//let datearr = arg.split("/");
		//datearr[2],(datearr[0]-1),datearr[1]`
		let datearr = Date.parse(arg);
    return new Date(datearr);
    
	 /* let day =('0' +date.getDate()).slice(-2);
    let month = ('0' + (date.getMonth() + 1)).slice(-2);
    let year = date.getFullYear();
	
		return month+'/'+day+'/'+year; */
    //return '' + year + '-' + (month<=9 ? '0' + month : month) + '-' + (day <= 9 ? '0' + day : day);
	}
	
  getErrorSummary(message,obj,formContent): any{
    let errMsg=[];
		let err = message;
		if(err)
		{
			if(typeof err === 'object' && err !== null)
			{
				for(let key in err) 
				{
					let val = err[key];
					
					let consolidatedErr = val.join('');
					//console.log(consolidatedErr);
					if(consolidatedErr)
					{
						errMsg.push('<li>'+consolidatedErr+'</li>');
						formContent.controls[key].setErrors({'incorrect': true});
						obj[key+'Errors']= consolidatedErr;	
					}	
				}
			}else{
				errMsg.push('<li>'+err+'</li>');				
			}				
		}
		return '<div class="errorSummary">Please fix the following input errors:<ul>'+errMsg.join('')+'</ul>';
  }
  
  //let subscription;
  noWhitespaceValidator(control: FormControl,ctrl: AbstractControl) {
    //console.log(control.value.length);
	
	/*
	let resultContentWithoutTrim = (control.value || '');

	let wsRegex = /(\s+)/; // Change this line
	let resultContent = (control.value || '').replace(wsRegex, ""); // Change this line

	if(control.value.length===0)
	{
		return '';
	}else if(resultContentWithoutTrim!='' && resultContent!='' && resultContentWithoutTrim!=resultContent){
		return { 'whitespace': true };	
	}
	*/
	
	/*
	const isWhitespace = (control.value || '').trim().length === 0;
	const isValid = !isWhitespace;
	
	if((control.value || '').length===0)
	{
		return '';
	}
		
    return isValid ? null : { 'whitespace': true };
	*/
	
	/*	
	this.subscription = timer(4000).subscribe(t => {
		ctrl.setValue(ctrl.value.toString().trim())
		this.subscription.unsubscribe();
	});
	
	if(this.subscription!='')
	{		
		this.subscription.unsubscribe();
	}
	*/
	
	//subscription.unsubscribe();
	
	/*
	if (ctrl && ctrl.value) 
	{				
		timer(4000).subscribe(x => { ctrl.setValue(ctrl.value.toString().trim()) })
	}
	*/	
	
	if(control.value === null)
	{
		return '';
	}
	
	//control.patchValue(control.value.replace(/^\s+|\s+$/gm,''));
	
	//control.reset({ value: control.value.replace(/^\s+|\s+$/gm,'') });
	
	const valueNoWhiteSpace = control.value.toString().trim();
    const isValid = valueNoWhiteSpace === control.value;
    return isValid ? null : { whitespace: true };
  }
  
  cannotContainSpaceValidator(control: FormControl) {
    
	if(control.value === null)
	{
		return '';
	}
	
	if((control.value as string).indexOf(' ') >= 0){
		return {cannotContainSpace: true}
	}
	return null;
  }
  
  
  
  validateAllFormFields(formGroup: FormGroup) {         //{1}
    Object.keys(formGroup.controls).forEach(field => {  //{2}
      const control = formGroup.get(field);             //{3}
      if (control instanceof FormControl) {             //{4}
        control.markAsTouched({ onlySelf: true });
      } else if (control instanceof FormGroup) {        //{5}
        this.validateAllFormFields(control);            //{6}
      }
    });
  }
  
  removeSpaces(control: AbstractControl) {
	//if (control && control.value && !control.value.replace(/\s/g, '').length) {
	if (control && control.value) {
		//setTimeout(function(control){ control.setValue(control.value.toString().trim()) }, 10);	
	
		//await timer(1000).pipe(take(1)).toPromise();
		control.setValue(control.value.toString().trim());		
		//timer(5000).subscribe(x => { control.setValue(control.value.toString().trim()) })
	}
	return null;
  }

}
