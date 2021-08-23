import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class FindingsCorrectiveActionService {

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
  
  getFindingDetails(findings_id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-findings-remediation/get-finding`,{id:findings_id});
  }

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-findings-remediation/create`, data);
  }

  downloadEvidenceFile(data){
    return this.http.post(`${environment.apiUrl}/audit/audit-findings-remediation/evidencefile`,data,
      {responseType:'arraybuffer'}
    );
  }
 
  
}
