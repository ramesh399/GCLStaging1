import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuditClientinformationService {

  constructor(private http: HttpClient) { }
  
  getQuestions(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-client-information/get-questions`,data);
  }

  getViewQuestions(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-client-information/get-viewquestions`,data);
  }

  getGeneralInformation(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-client-information/get-generalinformation`,data);
  }

  getSupplierInformation(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-supplier-information/get-supplier-information`,data);
  }

  getOptionlist(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-supplier-information/get-option-list`,{});
  }

  addSupplierData(chemicalData){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-supplier-information/create`, chemicalData);    
  }

  changeSufficient(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-supplier-information/change-sufficient`, data);
  }

  changeProductControlsSufficient(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-client-information-process/change-sufficient`, data);
  }

  deleteSupplierData(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-supplier-information/delete-data`, data);
  }

  getProcessDetails(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-client-information-process/get-process`,data);
  }

  addProcessData(chemicalData){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-client-information-process/create`, chemicalData);    
  }

  deleteProcessData(data){
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-client-information-process/delete-data`, data);
  }

  getchecklistAnswer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-client-information/get-checklist-answer`,data);
  }

  saveChecklist(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-client-information/save-checklist`,data);
  }
  
  saveGeneralInfoDetails(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-client-information/save-generalinfo`,data);
  }
  
  GetChecklistviewdetails(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-client-information/get-checklistviewdetails`,data);
  }

  getRemarkData(data):Observable<any>{    
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/get-applicable-data`,data);    
  }

  addRemark(remarkData)
  {
    return this.http.post<any>(`${environment.apiUrl}/audit/audit-plan/add-remark`, remarkData);
  }
 
}
