import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {Application} from '@app/models/application/application';



@Injectable({
  providedIn: 'root'
})
export class InvoiceReportService {
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
  
  
  getData(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/report/invoice-report/index`,data);
  }

  downloadFile(data){
    return this.http.post(`${environment.apiUrl}/report/invoice-report/index`,data,
      {responseType:'arraybuffer'}
    );
  }

  getStatusList(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/invoice/invoice/get-filter-option`,{});
  } 
  

}
