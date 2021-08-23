import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { environment } from '@environments/environment';
import {User} from '@app/models/master/user';


@Injectable({
  providedIn: 'root'
})
export class UserService {
	public validDocs = ['pdf','docx','doc','jpeg','jpg','png','xls','xlsx','ppt','pptx'];
	public technicalvalidDocs = ['pdf','docx','doc','jpeg','jpg','png','xls','xlsx'];
	public docsContentType = {
		'pdf':'application/pdf',
		'docx':'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'doc':'application/msword',
		'txt':'text/plain',
		'png' : 'image/png',
		'jpeg' : 'image/jpeg',
		'jpg' : 'image/jpeg',
		'xls' : 'application/vnd.ms-excel',
		'xlsx' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',		
		'ppt' : 'application/vnd.ms-powerpoint',
		'pptx' : 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
	};



  constructor(private http: HttpClient) { }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  }
  
  getAll() {
    return this.http.get<User[]>(`${environment.apiUrl}/master/users/get-users`);
  }

  getAllUser(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-users`,data);
  } 

  getCustomer(data:any={}): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-customers`,data);
  } 

  getAppApprover(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-approver`,data);
  } 
  getAppReviewer(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-reviewer`,data);
  }

  

  
  getUser(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/fetch-user`,data);
  }  

  getUserStdRoles(userid): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/fetch-userstdrole`,{userid});
  }

  addData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/create-user`, data);
  }
  
  sendForApproval(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/sendforapproval`, data);
  }

  sendToApproveAndReject(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/approve-and-reject`, data);
  }

  updateUserData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/update-user`, formData);
  }

  updateStdFile(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/update-stdfiles`, formData);
  }

  updateBgroupFile(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/update-bgroupfiles`, formData);
  }

  updateBgroupApprovalDate(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/update-bgroupdate`, formData);
  }
  
  getUserData(data): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-user-data`, data);
  }
  
  deleteTranslator(data): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/master/user/delete-user-data`, data);
  }
  downloadTranslator(data) {
     return this.http.post(`${environment.apiUrl}/master/user/downloadtranslatorfile`,data,
      {responseType:'arraybuffer'}
    );
    
  }

  checkUserName(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/check-user-role`, formData);
  }

  checkRoleExists(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/check-user-role-exists`, formData);
  }
  
  
  forgotPasswordData(data){
    return this.http.post<any>(`${environment.apiUrl}/site/password-reset`, data);
  }
  
  resetPasswordData(data){
    return this.http.post<any>(`${environment.apiUrl}/site/reset-password`, data);
  }
  
  changeUsernamePasswordData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/change-username-password`, data);
  }
  
  addFranchise(data){
    return this.http.post<any>(`${environment.apiUrl}/master/franchise/create`, data);
  }
  
  updateFranchiseData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/franchise/update`, formData,this.httpOptions);
  }

  updateBrandUserData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/brands/update`, formData,this.httpOptions);
  }

  updateBrandGroupUserData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/brand-group/update`, formData,this.httpOptions);
  }
  
  getUserDetails(id): Observable<any>{
	return this.http.post<any>(`${environment.apiUrl}/master/franchise/fetch-user`,{id});
  }

  getBrandUserDetails(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/brands/fetch-user`,{id});
  }

  getBrandGroupUserDetails(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/brand-group/fetch-user`,{id});
  }

  getOSSuser(id): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/franchise/oss-users`,id);
  }

  getProfileDetails(): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/view-profile`,{});
    }
  
  addCustomer(data){
    return this.http.post<any>(`${environment.apiUrl}/master/customer/create`, data);
  }
  
  updateCustomerData(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/customer/update`, formData,this.httpOptions);
  }
  
  getCustomerDetails(id): Observable<any>{
	return this.http.post<any>(`${environment.apiUrl}/master/customer/fetch-user`,{id});
  }
  
  getuserBusinessSectors(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/userbusinesssectors`, data);
  }
  getUserBusinessSectorGroups(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/userbusinesssectorgroups`, data);
  }

  changePasswordData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/change-password`, data);
  }
  
  getUserInformation(id): Observable<any>{
	return this.http.post<any>(`${environment.apiUrl}/master/user/fetch-user`,{id});
  }
  downloadFile(data){
    return this.http.post(`${environment.apiUrl}/master/user/userfile`,data,
      {responseType:'arraybuffer'}
    );
  }
  downloadUserFile(data){
    return this.http.post(`${environment.apiUrl}/master/user/personnelfile`,data,
      {responseType:'arraybuffer'}
    );
  }
  downloadAcademicFile(data){
    return this.http.post(`${environment.apiUrl}/master/user/academicfile`,data,
      {responseType:'arraybuffer'}
    );
  }
  downloadStandardFile(data){
    return this.http.post(`${environment.apiUrl}/master/user/standardfile`,data,
      {responseType:'arraybuffer'}
    );
  }
  downloadBgroupFile(data){
    return this.http.post(`${environment.apiUrl}/master/user/bgroupfile`,data,
      {responseType:'arraybuffer'}
    );
  }

  downloadteBgroupFile(data){
    return this.http.post(`${environment.apiUrl}/master/user/tebgroupfile`,data,
      {responseType:'arraybuffer'}
    );
  }

  downloaddocumentFile(data){
    return this.http.post(`${environment.apiUrl}/master/user/documentfile`,data,
      {responseType:'arraybuffer'}
    );
  }

  getBusinessSectors(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-businesssector`, data);
  }

  getBusinessSectorsGroup(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-businesssectorcode`, data);
  }

  getBusinessSectorsGroupApproved(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-businesssectorcodeapproved`, data);
  }

  getBusinessSectorGroupsbystds(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-business-sector-groups-by-standard`, data);
  }
  getBusinessSectorsbystds(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-business-sectors-by-standard`, data);
  }

  getTeRoles(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/get-te-roles`, data);
  }

  deleteUserData(data){
    return this.http.post<any>(`${environment.apiUrl}/master/user/delete-user-data`, data);
  }
  
  checkUniqueUserName(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/check-username`, formData);
  }
  
  userRoleApproval(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/user-role-approval`, formData);
  }

  changeCredential(formData): Observable<any>{
    return this.http.post<any>(`${environment.apiUrl}/master/user/change-credentials`, formData);
  }

  getStandardRights(){
    return this.http.get<any>(`${environment.apiUrl}/master/franchise/get-standardrights`);
  }
  getTcFeeRights(){
    return this.http.get<any>(`${environment.apiUrl}/master/franchise/get-tc-fee-rights`);
  }
  getCustomerDetailsByGet(data): Observable<any>{
    return this.http.get<any>(`${environment.apiUrl}/master/customer/fetch-user?${data}`);
  }

  
}