import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Offer} from '@app/models/offer/offer';
import {Application} from '@app/models/application/application';



@Injectable({
  providedIn: 'root'
})
export class GenerateDetailService {
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
  
  getAuditFiles(data): Observable<Offer>{
    return this.http.post<Offer>(`${environment.apiUrl}/offer/generate-offer/get-auditfiles`,data);
  }
  getOffer(data): Observable<Offer>{
    return this.http.post<Offer>(`${environment.apiUrl}/offer/generate-offer/view-offer`,data);
  }

  getOfferByGet(data): Observable<Offer>{
    return this.http.get<Offer>(`${environment.apiUrl}/offer/generate-offer/view-offer?${data}`);
  }

  getApplication(id): Observable<Application>{
    return this.http.post<Application>(`${environment.apiUrl}/offer/generate-offer/view`,{id});
  }
  addOffer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/offer/generate-offer/generate`,data);
  }
  
  updateOffer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/offer/generate-offer/update-offer`,data);
  }

  changeStatus(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/offer/generate-offer/change-status`,data);
  }

  uploadAuditReport(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/offer/generate-offer/upload-audit-report`,data);
  }

  downloadOfferFile(data){
    return this.http.post(`${environment.apiUrl}/offer/generate-offer/create`,data,
      {responseType:'arraybuffer'}
    );
  }

  downloadUploadedFile(data){
    return this.http.post(`${environment.apiUrl}/offer/generate-offer/customer-approvalfile`,data,
      {responseType:'arraybuffer'}
    );
  }

  downloadAuditFiles(data){
    return this.http.post(`${environment.apiUrl}/offer/generate-offer/download-audit-file`,data,
      {responseType:'arraybuffer'}
    );
  }
  
  downloadUploadedProcessorFile(data){
    return this.http.post(`${environment.apiUrl}/offer/generate-offer/customer-processor-approvalfile`,data,
      {responseType:'arraybuffer'}
    );
  }
  
  downloadTemplate(data){
    return this.http.post(`${environment.apiUrl}/offer/generate-offer/templatefile`,data,
      {responseType:'arraybuffer'}
    );
  }
  
  checkAuditReport(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/offer/generate-offer/validate-audit-report`,data);
  }
  
  getAuditReportDisplayStatus(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/offer/generate-offer/getreportlist`,data);
  }

}
