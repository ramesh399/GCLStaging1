import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Enquiry} from '@app/models/enquiry';



@Injectable({
  providedIn: 'root'
})
export class EnquiryDetailService {

  public validDocs = ['pdf','docx','doc','jpeg','jpg','png'];
  public unitType = {'1':'Scope Holder','2':'Facility','3':'Subcontractor'};
  public docsContentType = {'pdf':'application/pdf','docx':'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
  ,'doc':'application/msword'
  ,'txt':'text/plain'
  ,'png' : 'image/png'
  ,'jpeg' : 'image/jpeg'
  ,'jpg' : 'image/jpeg'
  };


  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getEnquiry(id): Observable<Enquiry>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<Enquiry>(`${environment.apiUrl}/enquiry/view`,{id});
  }
	
	addCustomer(data): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<any>(`${environment.apiUrl}/master/customer/create-customer`,data);
  }
  archiveEnquiry(id): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<any>(`${environment.apiUrl}/master/user/enquiry-archive`,{id});
  }
  addExistingCustomer(data): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<any>(`${environment.apiUrl}/master/user/assign-enquiry-existing-customer`,data);
  }


  

  assignReviewer(data): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);
    return this.http.post<any>(`${environment.apiUrl}/application/review/assign`,data);
  }

  assignApprover(data): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<any>(`${environment.apiUrl}/application/approval/assign`,data);
  }

  approveApplication(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/approval/create`,data);
  }
  
  submitAppForReview(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/apps/submitforreview`,data);
  }
  osprejectApp(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/apps/ospreject`,data);
  }
  

  addEnquiry(enquiryData){
    //return [{id:1, name:'USA'},{id:2, name:'India'}];
    //let enquiryDatas = Object.assign({}, enquiryData);
    return this.http.post<any>(`${environment.apiUrl}/request/enquiry`, enquiryData);
    //, JSON.stringify(data)
  }
  
  addApplication(data): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<any>(`${environment.apiUrl}/application/apps/create`,data);
  }
  updateApplication(data): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<any>(`${environment.apiUrl}/application/apps/update`,data);
  }

  getEnquiryDetails(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/apps/view`,{id});
  }

  getProductDetails(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/apps/get-product-details`,id);
  }

  getEnquiryDetailsData(data): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<any>(`${environment.apiUrl}/application/apps/view`,data);
  }

  getApplicationchecklist(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/apps/applicationchecklist`,data);
  }
  getSectorgpUserList(data): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<any>(`${environment.apiUrl}/application/apps/businessectorusers`,data);
  }

  getEnquiryData(enquiry_id): Observable<any>{
    //let params = new HttpParams();
    //params = params.append('id', id);

    return this.http.post<any>(`${environment.apiUrl}/application/apps/latest-enquiry`,{enquiry_id});
  }



  downloadFile(data){
    return this.http.post(`${environment.apiUrl}/application/apps/certificationfile`,data,
      {responseType:'arraybuffer'}
    );
  }

  downloadChecklistFile(data){
    return this.http.post(`${environment.apiUrl}/application/apps/checklistfile`,data,
      {responseType:'arraybuffer'}
    );
  }

  downloadcertificateFile(data){
    return this.http.post(`${environment.apiUrl}/application/apps/certifiedfile`,data,
      {responseType:'arraybuffer'}
    );
  }

  downloadCompanyFile(data){
    return this.http.post(`${environment.apiUrl}/application/apps/applicationfile`,data,
      {responseType:'arraybuffer'}
    );
  }

  
  getBSectorStandardWise(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/application/apps/businessectorstandardwise`,data);
  }

  getYear(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/site/getyear`,{});
  }
  
  updateProductMaterialComposition(data): Observable<any>{    
    return this.http.post<any>(`${environment.apiUrl}/application/apps/updateproductmaterial`,data);
  }
}
