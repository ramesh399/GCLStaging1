import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { FormGroup, FormBuilder, Validators, FormControl, FormArray, NgForm, NgControl, Form } from '@angular/forms';
import { GenerateDetailService } from '@app/services/offer/generate-detail.service';
import { Offer } from '@app/models/offer/offer';
import { Observable } from 'rxjs';
import { first } from 'rxjs/operators';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap'; 
import { AuthenticationService } from '@app/services'
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { saveAs } from 'file-saver';
import { Application } from '@app/models/application/application';
import { ApplicationDetailService } from '@app/services/application/list/application-detail.service';
import { environment } from '@environments/environment';
import { DocumentListService } from '@app/services/library/document/document-list.service';
@Component({
  selector: 'app-view-offer',
  templateUrl: './view-offer.component.html',
  styleUrls: ['./view-offer.component.scss']
})
export class ViewOfferComponent implements OnInit {
  documents$: any;
  constructor(public errorSummary:ErrorSummaryService,
    private fb:FormBuilder,
    public service: DocumentListService,
    private activatedRoute:ActivatedRoute,
     
    private generateDetail:GenerateDetailService, private modalService: NgbModal,
    private authservice:AuthenticationService,private applicationDetail:ApplicationDetailService) { 
   this.documents$ = service.documents$;

    }
  id:number;
  offer_id:number;
  error = '';
  success = '';
  loading = false;
  offerdata:Offer;
  confirmText = '';
  buttonDisable='';

  modals:any;
  model: any = {comments:''};
  modaltitle='';
  arg = '';
  comments_error = '';
  panelOpenState = false;
  offerenumstatus = '';
  userdecoded:any;
  is_headquarters:number;
  userType:number;
  userdetails:any;
  offerForm : FormGroup;
  auditreportForm : FormGroup;
  public validDocs = ['pdf','docx','doc','jpeg','jpg','png'];
  applicationdata:Application;

  content_claim_standard_file_status = 0;
  reconciliation_report_file_status = 0;
  chemical_declaration_file_status = 0;
  social_declaration_file_status = 0;
  environmental_declaration_file_status = 0;

  processorAgreementFileList=[];
  applicableforms:any = [];
  apploaded:any=false;
  weburl:any;
  unitmandaydetails : any[] = []

  standard: any;
  status: any;
  ngOnInit() {
    this.weburl = environment.apiUrl;
    this.id = this.activatedRoute.snapshot.queryParams.id;
    this.offer_id = this.activatedRoute.snapshot.queryParams.offer_id;
    this.status = this.activatedRoute.snapshot.queryParams.status;
    this.standard = this.activatedRoute.snapshot.queryParams.std;
	
	  this.applicationDetail.getApplication(this.id).pipe(first())
      .subscribe(res => {
        this.applicationdata = res;     
    },
    error => {
      this.error = error;
      this.loading = false;
    });

     
    
    this.generateDetail.getOffer({id:this.id,offer_id:this.offer_id}).pipe(first())
    .subscribe(res => {
		this.offerdata = res;
		this.getReportDisplayStatus();
	  			    
		this.content_claim_standard_file = this.offerdata.offer.content_claim_standard_file;
		this.reconciliation_report_file = this.offerdata.offer.reconciliation_report_file;
		this.chemical_declaration_file = this.offerdata.offer.chemical_declaration_file;
		this.social_declaration_file = this.offerdata.offer.social_declaration_file;
    this.environmental_declaration_file = this.offerdata.offer.environmental_declaration_file;
    
    this.content_claim_standard_file_status = 1;
    this.reconciliation_report_file_status = 1;
    this.chemical_declaration_file_status = 1;
    this.social_declaration_file_status = 1;
    this.environmental_declaration_file_status = 1;
		
		this.auditreportForm.patchValue({			
			volume_reconciliation_formula:this.offerdata.offer.volume_reconciliation_formula
		});

      //this.processorfile[stdqid]
      //quotation_file
      //this.offerdata
      /*
      if(this.offerdata.offer && this.offerdata.offer.quotation_file){
        this.quotation_file = this.offerdata.offer.quotation_file;
      }

      if(this.offerdata.offer && this.offerdata.offer.processor_files){
        if(this.offerdata.offer.processor_files.length>0){
          this.offerdata.offer.processor_files.forEach(element => {
            this.processorfile[element.unit_id] = {name:element.processor_file};
          });
        }
      }
      */

    },
    error => {
        this.error = error;
        this.loading = false;
    });   

    


    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.is_headquarters = user.decodedToken.is_headquarters;
        this.userdetails= user.decodedToken;
      }
    });

    this.offerForm = this.fb.group({
      quotation_file:[''],
      scheme_rules:['']
    });

    this.auditreportForm = this.fb.group({
      //risk_assessment_file:[''],
      volume_reconciliation_formula:['',[Validators.required,this.errorSummary.noWhitespaceValidator, Validators.maxLength(255)]],
      content_claim_standard_file:[''],
      reconciliation_report_file:[''],
      chemical_declaration_file:[''],
      social_declaration_file:[''],
      environmental_declaration_file:[''],
      //environmental_report_file:[''],
      //chemical_list_file:[''],
    });
  }   

  getAudit (offerdata) {
     
    return offerdata.offer_certification_fee[0].description.replace("Manday", "");
  }
 

  downloaddocFile(fileid='',filetype='',filename='')
  {

     let docs = []
     this.documents$.pipe(first()).subscribe(res => {
        docs = res.filter(el => {
           return (el.franchise_id == fileid)
        })
     })
     docs.forEach(el => {
       this.service.downloadDocumentFile({id: el.id,filetype})
       .subscribe(res => {
         // this.modalss.close();
        let fileextension = el.document.split('.'); 
          let contenttype = this.errorSummary.getContentType(fileextension[0]);
         saveAs(new Blob([res],{type:contenttype}), el.document);
       },
       error => {
         
       });
     })
  }
  getReportDisplayStatus(){
    this.generateDetail.getAuditReportDisplayStatus({app_id:this.id,offer_status:this.offerdata.offer.offer_status}).pipe(first())
    .subscribe(res => {
      this.applicableforms = res;
      this.apploaded=true;
    },
    error => {
        this.error = error;
        this.loading = false;
    });  
  }
  get af() { return this.auditreportForm.controls; } 

  quotationFileError ='';
  RiskAssessmentFileError ='';
  schemeRulesError = '';
  quotation_file = '';
  scheme_rules = '';
  formData:FormData = new FormData();
  quotationfileChange(element) {
    let files = element.target.files;
    this.quotationFileError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("quotation_file", files[0], files[0].name);
      this.quotation_file = files[0].name;
      
    }else{
      this.quotationFileError ='Please upload valid file';
    }
    element.target.value = '';
   
  }

  downloadFile(app_id,offer_id){

    this.generateDetail.downloadOfferFile({app_id,offer_id})
    .subscribe(res => {
        this.modalss.close();
        saveAs(new Blob([res],{type:'application/pdf'}),'offer_'+offer_id+'.pdf');
    },
    error => {
        this.error = error;
        this.modalss.close();
    });
  }
    
  /*	
  risk_assessment_file = '';
  auditreportfileChange(element) {
    let files = element.target.files;
    this.RiskAssessmentFileError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("risk_assessment_file", files[0], files[0].name);
      this.risk_assessment_file = files[0].name;
      
    }else{
      this.RiskAssessmentFileError ='Please upload valid file';
    }
    element.target.value = '';
   
  }
  */

  content_claim_standard_file = '';
  cssFileError ='';
  ccsfileChange(element) {
    let files = element.target.files;
    this.cssFileError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("content_claim_standard_file", files[0], files[0].name);
      this.content_claim_standard_file = files[0].name;
      this.content_claim_standard_file_status = 0;
    }else{
      this.cssFileError ='Please upload valid file';
    }
    element.target.value = '';
   
  }


  reconciliation_report_file = '';
  reconciliationFileError ='';
  ReconciliationfileChange(element) {
    let files = element.target.files;
    this.reconciliationFileError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("reconciliation_report_file", files[0], files[0].name);
      this.reconciliation_report_file = files[0].name;
      this.reconciliation_report_file_status = 0;
      
    }else{
      this.reconciliationFileError ='Please upload valid file';
    }
    element.target.value = '';
   
  }


  chemical_declaration_file = '';
  chemicalFileError ='';
  chemicalfileChange(element) {
    let files = element.target.files;
    this.chemicalFileError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("chemical_declaration_file", files[0], files[0].name);
      this.chemical_declaration_file = files[0].name;
      this.chemical_declaration_file_status = 0;

    }else{
      this.chemicalFileError ='Please upload valid file';
    }
    element.target.value = '';
   
  }


  social_declaration_file = '';
  socialFileError ='';
  socialfileChange(element) {
    let files = element.target.files;
    this.socialFileError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("social_declaration_file", files[0], files[0].name);
      this.social_declaration_file = files[0].name;
      this.social_declaration_file_status = 0;
      
    }else{
      this.socialFileError ='Please upload valid file';
    }
    element.target.value = '';
  }



  environmental_declaration_file = '';
  environmentalFileError ='';
  environmentalfileChange(element) {
    let files = element.target.files;
    this.environmentalFileError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("environmental_declaration_file", files[0], files[0].name);
      this.environmental_declaration_file = files[0].name;
      this.environmental_declaration_file_status = 0;
      
    }else{
      this.environmentalFileError ='Please upload valid file';
    }
    element.target.value = '';
  }

  /*	
  environmental_report_file = '';
  environmentalreportFileError ='';
  environmentalreportfileChange(element) {
    let files = element.target.files;
    this.environmentalreportFileError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("environmental_report_file", files[0], files[0].name);
      this.environmental_report_file = files[0].name;
      
    }else{
      this.environmentalreportFileError ='Please upload valid file';
    }
    element.target.value = '';
  }


  chemical_list_file = '';
  chemicalListFileError ='';
  chemicalListfileChange(element) {
    let files = element.target.files;
    this.chemicalListFileError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("chemical_list_file", files[0], files[0].name);
      this.chemical_list_file = files[0].name;
      
    }else{
      this.chemicalListFileError ='Please upload valid file';
    }
    element.target.value = '';
  }
  */

  schemeFileChange(element) {
    let files = element.target.files;
    this.schemeRulesError ='';
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {

      this.formData.append("scheme_rules", files[0], files[0].name);
      this.scheme_rules = files[0].name;
      
    }else{
      this.schemeRulesError ='Please upload valid file';
    }
    element.target.value = '';
   
  }

  

  
  
  downloadPDFFile()
  {
    let app_id=this.id;
    let offer_id=this.offer_id;

    this.generateDetail.downloadOfferFile({app_id,offer_id})
    .subscribe(res => {
        this.modalss.close();
        saveAs(new Blob([res],{type:'application/pdf'}),'offer_'+app_id+'.pdf');
        // console.log(res);
        //saveFile(res.blob(), 'test.txt');
    },
    error => {
        this.error = error;
        this.modalss.close();
    });
  }
 removeFile(type){
  if(type =='quotation'){
    this.quotation_file = '';
    this.formData.delete('quotation_file');
  }else if(type =='scheme'){
    this.scheme_rules = '';
    this.formData.delete('scheme_rules');
  /*	
  }else if(type =='risk_assessment'){
    this.risk_assessment_file = '';
    this.formData.delete('risk_assessment_file');
  */	
  }else if(type =='ccs_file'){
    this.content_claim_standard_file = '';
    this.formData.delete('content_claim_standard_file');
  }else if(type =='reconciliation_file'){
    this.reconciliation_report_file = '';
    this.formData.delete('reconciliation_report_file');
  }else if(type =='chemical_declaration'){
    this.chemical_declaration_file = '';
    this.formData.delete('chemical_declaration_file');
  }else if(type =='social_declaration'){
    this.social_declaration_file = '';
    this.formData.delete('social_declaration_file');
  }else if(type =='environmental_declaration'){
    this.environmental_declaration_file = '';
    this.formData.delete('environmental_declaration_file');
  /*	
  }else if(type =='environmental_report'){
    this.environmental_report_file = '';
    this.formData.delete('environmental_report_file');
  }else if(type =='chemical_list'){
    this.chemical_list_file = '';
    this.formData.delete('chemical_list_file');
  */	
  }  
 
  }
   checkUserComment(){
    if(this.model.comments.trim() ==''){
      this.comments_error ='true';
    }else{
      this.comments_error ='';
      this.modals.close('Save');
    }
   }
   loadingArr:any=[];
   audit_report_valid:any=false;
   audit_report_message:any = '';
   audit_report_title:any = '';
   open(content,arg='') 
   {
      this.model.comments = '';
      let status = this.offerdata.offerenumstatus['open'];
      let user_type = '';
	  if(arg=='sendtooss')
      {
        this.confirmText = 'Are you sure, do you want to send the offer to OSS ?';
		status = this.offerdata.offerenumstatus['waiting-for-oss-approval'];
	  }else if(arg=='sendtohq'){
        this.confirmText = 'Are you sure, do you want to send the offer to HQ ?';
		status = this.offerdata.offerenumstatus['waiting-for-send-to-customer'];
	  }else if(arg=='reinitiatetooss'){
      this.modaltitle = 'Send Back to OSS';   
      this.confirmText = 'Are you sure, do you want to send back the offer to OSS ?';
		  status = this.offerdata.offerenumstatus['re-initiated-to-oss'];			
	  }else if(arg=='sendcustomer'){
        this.confirmText = 'Are you sure, do you want to send the offer to customer ?';
        status = this.offerdata.offerenumstatus['waiting-for-customer-approval'];
      }else if(arg=='approve'){
        this.confirmText = 'Are you sure, do you want to approve the offer ?';   
        status = this.offerdata.offerenumstatus['customer_approved'];
      }else if(arg=='finalize'){
        this.confirmText = 'Are you sure, do you want to finalize the offer ?';   
        status = this.offerdata.offerenumstatus['finalized'];
      }else if(arg=='reject'){
        this.modaltitle = 'Offer Reject';   
        status = this.offerdata.offerenumstatus['customer_rejected'];
        user_type='customer';
      }else if(arg=='offerapprove'){
        this.modaltitle = 'Quotation Approve';   
        status = this.offerdata.offerenumstatus['finalized'];
        user_type='user';
      }else if(arg=='offerreject'){
        this.modaltitle = 'Quotation Reject';   
        status = this.offerdata.offerenumstatus['rejected'];
        user_type='user';
      }else if(arg == "submitauditreport"){
        // let data:any = {app_id:this.auditPlanData.app_id};
         //this.loadingData['assignreviewer'] = true;
         this.loadingArr['reportvalidation'] = true;
         this.audit_report_valid = false;
         this.audit_report_message = '';
         this.audit_report_title = '';
         
         this.generateDetail.checkAuditReport({app_id:this.id,offer_id:this.offer_id})
         .pipe(first())
         .subscribe(res => {
           this.loadingArr['reportvalidation'] = false;
           this.audit_report_message = res['audit_report_message'];
           this.audit_report_valid = res['audit_report_valid'];
           this.audit_report_title = res['audit_report_title'];
         },
         error => {
           this.loadingArr['reportvalidation'] = false;
             this.error = error;
             
         });
       }
      
      this.arg = arg;
      this.modals = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
      
      this.modals.result.then((result) => {
        //console.log(result);
        //console.log(arg);
        if(arg != "submitauditreport"){
          this.changeStatus({comment:this.model.comments,user_type,status,app_id:this.id,offer_id:this.offer_id});
        }
      }, (reason) => {
        this.comments_error ='';
        this.arg = '';

        this.alertInfoMessage = '';
        this.alertSuccessMessage = '';
        this.alertErrorMessage = '';
        //this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
      });
    }


  changeStatus(data){
    //console.log(data);
    //return false;

    this.loading  = true;
    this.generateDetail.changeStatus(data)
     .pipe(first())
     .subscribe(res => {
           
         if(res.status==1){
            //this.enquirydata.status = res.enquirystatus;
            //this.enquirydata.status_updated_date = res.status_updated_date;
            //this.enquirydata.status_updated_by = res.status_update_by;
           // let data_status = data.status;
            //if(data.status == this.offerdata.offerenumstatus['rejected']){
            //  data_status = this.offerdata.offerenumstatus['waiting-for-customer-approval'];
            //}
            //this.offerdata.offer.offer_status = data_status;
            this.success = res.message;
            setTimeout(() => {
              this.success = '';
              this.offerdata = undefined;
              this.loading = false;
              this.getOfferDetails();
              
            }, this.errorSummary.redirectTime);
          }else if(res.status == 0){
            this.error = res.message;
            this.loading = false;
          }else{
            this.error = res;
            this.loading = false;
          }
          
        
     },
     error => {
         this.error = error;
         this.loading = false;
     });
  }

  downloadUploadedFile(fileid,filename,type){
    this.generateDetail.downloadUploadedFile({offerlist_id:fileid,type})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    
    });
  }

  downloadAuditFiles(filename,type){
    this.generateDetail.downloadAuditFiles({offer_id:this.offer_id,type})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    
    });
  }

  downloadUploadedProcessorFile(offerlist_id,unitid,fileid,filename){
    this.generateDetail.downloadUploadedProcessorFile({offerlist_id:offerlist_id,unit_id:unitid,file_id:fileid})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.errorSummary.getContentType(filename);
      saveAs(new Blob([res],{type:contenttype}),filename);
    
    });
  }

  downloadTemplate(templatetype,stdcode='',filename='')
  {
    this.generateDetail.downloadTemplate({template_type:templatetype,standard_code:stdcode})
    .subscribe(res => {
      this.modalss.close();
    
    
    if(filename=='')
    {
        filename='PCPA02_PROCESSOR_AGREEMENT_(TE).docx';
    }
    
    let contenttype = this.errorSummary.getContentType(filename);
    saveAs(new Blob([res],{type:contenttype}),filename);
    
    });
  }


modalss:any;
openmodal(content,arg='') {
  this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
}

 //formData:FormData = new FormData();
 processorfile = [];
 removeProcessorFile(stdqid){
    this.processorfile[stdqid] = '';
    this.formData.delete("processorfile["+stdqid+"]");
 }
 
  fileChange(element,stdqid) {
    let files = element.target.files;
    
    let fileextension = files[0].name.split('.').pop();
    if(this.errorSummary.checkValidDocs(fileextension))
    {
      this.formData.append("processor_file["+stdqid+"]", files[0], files[0].name);
      //this.company_file = files[0].name;
      this.processorfile[stdqid] = {name:files[0].name};
      this.fileErrList[stdqid]=false;
    }else{
      this.fileErrList[stdqid]=true;
    }
    element.target.value = '';
   
  }


  getOfferDetails(){
    this.generateDetail.getOffer({id:this.id,offer_id:this.offer_id}).pipe(first())
      .subscribe(res => {
        

        this.offerdata = res;
        if(this.offerdata && this.offerdata.offer.offer_status==this.offerdata.offerenumstatus['waiting_for_audit_report']){
          this.getReportDisplayStatus();
        }
        

        this.loading = false;
        if(this.offerdata.offer && this.offerdata.offer.quotation_file){
          this.quotation_file = this.offerdata.offer.quotation_file;
        }

        if(this.offerdata.offer && this.offerdata.offer.processor_files){
          if(this.offerdata.offer.processor_files.length>0){
            this.offerdata.offer.processor_files.forEach(element => {
              this.processorfile[element.unit_id] = {name:element.processor_file};
            });
          }
        }

      },
      error => {
          this.error = error;
          this.loading = false;
      }); 
  }
 fileErrList= [];
 onSubmit(actiontype){
  
  if(actiontype=='save')
  {
    this.quotationFileError ='';
    this.schemeRulesError ='';
    if(this.quotation_file ==''){
      this.quotationFileError ='Please upload Quotation file';
    }
    /*
    if(this.scheme_rules ==''){
      this.schemeRulesError ='Please upload Scheme Rules file';
    }
    */
    
    let formerror = false;
    this.offerdata.units.forEach(element => {
        let unitid = element.id;
        if(element.unit_type!=1 && element.unit_type!=2){
          if(this.processorfile[unitid]===undefined || this.processorfile[unitid]==null || this.processorfile[unitid] == ''){
            this.fileErrList[unitid]=true;
            formerror=true;
          }else{
            var index = this.fileErrList.indexOf(unitid);
            if (index == -1) {
              this.fileErrList[unitid]=false;
            }
          }
        }
    });
    
    
    if(this.quotation_file !='' && !formerror){
    
		let status = this.offerdata.offerenumstatus['customer_approved'];
      
		let formvalue = {status:status,app_id:this.id,offer_id:this.offer_id};
		this.formData.append('formvalues',JSON.stringify(formvalue));

		this.loading  = true;
		this.generateDetail.changeStatus(this.formData)
		.pipe(first())
		.subscribe(res => {
			if(res.status==1){
			  //this.offerdata.offer.offer_status = status;
			  this.success = res.message;
			  setTimeout(() => {
				this.offerdata = undefined;
				this.success = '';
				this.getOfferDetails();
			  }, this.errorSummary.redirectTime);
			}else if(res.status == 0){
			  this.error = res.message;
			}else{
			  this.error = res;
			}		  
		},
		error => {
			this.error = error;
			this.loading = false;
		});
    }
  }
  else
  {
	/*  
    this.RiskAssessmentFileError ='';
    if(this.risk_assessment_file ==''){
		this.RiskAssessmentFileError ='Please upload Risk Assessment file';
    }
	*/
    if(actiontype != 'audit_report_approve'){
      this.reconciliationFileError ='';
      if(this.reconciliation_report_file ==''){
        this.reconciliationFileError ='Please upload Reconciliation report file';
      }
  
      this.cssFileError ='';
      if(this.offerdata.showCCS && this.content_claim_standard_file ==''){
        this.cssFileError ='Please upload CCS file';
      }
  
      this.chemicalFileError ='';
      if(this.offerdata.showChemicalDeclaration && this.chemical_declaration_file ==''){
        this.chemicalFileError ='Please upload Chemical Declaration file';
      }
  
      this.socialFileError ='';
      if(this.offerdata.showSocialDeclaration && this.social_declaration_file ==''){
        this.socialFileError ='Please upload Social Declaration file';
      }
  
      this.environmentalFileError ='';
      if(this.offerdata.showEnvironmentalDeclaration && this.environmental_declaration_file ==''){
        this.environmentalFileError ='Please upload Environmental Declaration file';
      }
    }
    
	
	/*
    this.environmentalreportFileError ='';
    if(this.offerdata.showEnvironmentalReport && this.environmental_report_file ==''){
		this.environmentalreportFileError ='Please upload Environmental Report file';
    }

    this.chemicalListFileError ='';
    if(this.offerdata.showChemicalList &&  this.chemical_list_file ==''){
		this.chemicalListFileError ='Please upload Chemical List file';
    }   
	*/
	
    //if( this.RiskAssessmentFileError =='' && this.reconciliationFileError =='' && this.cssFileError =='' && this.chemicalFileError =='' && this.socialFileError =='' && this.environmentalFileError =='' && this.environmentalreportFileError =='' && this.chemicalListFileError =='')
    //if( this.auditreportForm.valid && this.RiskAssessmentFileError =='' && this.reconciliationFileError =='' && this.cssFileError =='' && this.chemicalFileError =='' && this.socialFileError =='' && this.environmentalFileError =='' && this.environmentalreportFileError =='' && this.chemicalListFileError =='')
	//  
  
    //if(1)
		
	if(actiontype == 'audit_report_approve' || (this.auditreportForm.valid && this.reconciliationFileError =='' && this.cssFileError =='' && this.chemicalFileError =='' && this.socialFileError =='' && this.environmentalFileError =='') )
    {
      //let status = this.offerdata.offerenumstatus['waiting_for_audit_report'];
      let volume_formula = this.auditreportForm.get('volume_reconciliation_formula').value;
      //,volume_reconciliation_formula:volume_formula

      
      let formvalue = {offer_id:this.offer_id,actiontype,volume_reconciliation_formula:volume_formula};
      this.formData.append('formvalues',JSON.stringify(formvalue));

      this.loading  = true;
      this.generateDetail.uploadAuditReport(this.formData)
      .pipe(first())
      .subscribe(res => {
            if(res.status==1){
              this.loading = false;
              //this.offerdata.offer.offer_status = status;
              this.alertSuccessMessage = res.message;
              setTimeout(() => {
                this.offerdata = undefined;
                this.alertSuccessMessage = '';
                this.getOfferDetails();
                this.modals.close();
                
              }, this.errorSummary.redirectTime);
            }else if(res.status == 0){
              this.alertErrorMessage = res.message;
              this.loading = false;
            }else{
              this.alertErrorMessage = res;
              this.loading = false;
            }
          
          
      },
      error => {
          this.alertErrorMessage = error;
          this.loading = false;
      });
    }
    else
    {
      this.errorSummary.validateAllFormFields(this.auditreportForm); 
    }

  }

 }
  alertInfoMessage:any = '';
  alertSuccessMessage:any = '';
  alertErrorMessage:any = '';

  upload_report_status = true;
  generalinfo_status = false;
  checklist_status = false;
  supplierinfo_status = false;
  unittab:any = [];
  changeOfferTab(arg,unitid:any='')
  {
	this.upload_report_status=false;
	this.generalinfo_status=false;
    this.supplierinfo_status=false;
    this.checklist_status=false;
	  this.offerdata.units.forEach(x=>{
      this.unittab[x.id]=false;
    })
	
	if(arg=='uploadreport'){
		this.upload_report_status=true;
	}else if(arg=='generalinfo'){
		  this.generalinfo_status=true;
	}else if(arg=='supplierinfo'){
      this.supplierinfo_status=true;
    }else if(arg=='checklist'){
      this.checklist_status=true;
    }else if(arg=='unit'){
      this.unittab[unitid]=true;
    }
  }
}