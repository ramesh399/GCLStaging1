import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import { QualificationChecklist } from '@app/models/master/qualification-checklist';


@Injectable({
  providedIn: 'root'
})
export class UserqualificationChecklistService {

  public validDocs = ['pdf','docx','doc','jpeg','jpg','png'];
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
  
  getUserBusinessSector(standard_ids): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user-qualification-checklist/usersector`,standard_ids);
  }

  getBusinessSectorGroup(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user-qualification-checklist/usersectorgroup`,data);
  }
  getQualificationChecklist(userdetail): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user-qualification-checklist/view`,userdetail);
  }
  
  getQualificationView(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user-qualification-checklist/qualification-view`,{id});
  }
  
  getQualificationAnswerData(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user-qualification-checklist/qualification-view`,data);
  }

  getQualificationHistoryData(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user-qualification-checklist/review-history`,{id});
  }

  updateData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/qualification-checklist/update`, formData,this.httpOptions);
  }
  
  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user-qualification-checklist/create`, data);
  }
  approveData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user-qualification-checklist/approve`, data);
  }
  downloadFile(data){
    return this.http.post(`${environment.apiUrl}/master/user-qualification-checklist/checklistfile`,data,
      {responseType:'arraybuffer'}
    );
  }
}
