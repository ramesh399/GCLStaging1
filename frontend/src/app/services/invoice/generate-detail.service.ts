import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Invoice} from '@app/models/invoice/invoice';
import {Application} from '@app/models/application/application';



@Injectable({
  providedIn: 'root'
})
export class GenerateDetailService {

  constructor(private http: HttpClient) { }
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }

  
  getOffer(data): Observable<Invoice>{
    return this.http.post<Invoice>(`${environment.apiUrl}/invoice/invoice/view-offer`,data);
  }
  
  getInvoice(data): Observable<Invoice>{
    return this.http.post<Invoice>(`${environment.apiUrl}/invoice/invoice/view-invoice`,data);
  }
  
  updatePayment(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/invoice/invoice/payment-update`,data);
  }
  getApplication(id): Observable<Application>{
    return this.http.post<Application>(`${environment.apiUrl}/invoice/generate-offer/view`,{id});
  }
  addOffer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/invoice/invoice/generate`,data);
  }

  changeStatus(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/invoice/generate-offer/change-status`,data);
  }

  approveApplication(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/invoice/invoice/changeinvoicestatus`,data);
  }
  
  getAdditionInvoice(data): Observable<Invoice>{
    return this.http.post<Invoice>(`${environment.apiUrl}/invoice/invoice/get-additional-invoice`,data);
  }
  
  getOSSAdditionalInvoice(data): Observable<Invoice>{
    return this.http.post<Invoice>(`${environment.apiUrl}/invoice/invoice/oss-additional-invoice`,data);
  }

  downloadFile(data){
    return this.http.post(`${environment.apiUrl}/invoice/generate-offer/create`,data,
      {responseType:'arraybuffer'}
    );
  }
	
}
