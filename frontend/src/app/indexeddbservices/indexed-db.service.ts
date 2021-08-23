import { Injectable } from '@angular/core';
import { openDB, DBSchema, IDBPDatabase } from 'idb';

@Injectable({
  providedIn: 'root'
})
export class IndexedDBService {

  private db: IDBPDatabase<MyDB>;
  constructor() { 
    this.connectToDb();
  }
  async connectToDb(){
   
    this.db = await openDB<MyDB>('gcl-db', 1, {
      upgrade(db) {
         
        db.createObjectStore('audit_checklist');
        db.createObjectStore('audit_answers');
        db.createObjectStore('local_storage');
      },
    });
  }
 
  /*
  async connectToDb(){
    this.db = await openDB<MyDB>('gcl-db', 1, {
      upgrade(db) {
        db.createObjectStore('audit_checklist');
        db.createObjectStore('audit_answers');
        db.createObjectStore('local_storage');
      },
    });
  }
  */
  
  addChecklistAnswer(value:any,key:any){
    return this.db.put('audit_answers', value, key);
  }
  getChecklistAnswer(key:any=''){
    return this.db.get('audit_answers', 'checklist_answer_'+key);
  }
  getUpdateError(key:any=''){
    if(this.db){
      return this.db.get('audit_answers', 'update_error_'+key);
    }else{
      this.connectToDb();
      return this.db.get('audit_answers', 'update_error_'+key);
    }
    
  }
  deleteChecklistAnswer(key:any=''){
    return this.db.delete('audit_answers', 'checklist_answer_'+key);
  }

  deleteChecklistAnswerQuestions(ansval,qids:any=[]){
    
      if(qids.length>0){
        qids.map(qid=>{
          const qindex = ansval.questions.findIndex(xq=>xq.question_id==qid);
          if(qindex !== -1){
            ansval.questions.splice(qindex,1);
          }
        });
      }
      const  subArr = ansval.sub_topic_id.split(',');
      
      let newsubArr:any = [];
      subArr.forEach(subid=>{
        let chksub = ansval.questions.findIndex(f=>f.sub_topic_id==subid);
        if(chksub !== -1){
          newsubArr.push(subid);
        }
      });
      ansval.sub_topic_id = newsubArr.join(',');
      
      
      
      //ansval.questions.filter(f=>f.sub_topic_id==)
      return ansval;
    //return this.db.delete('audit_answers', 'checklist_answer_'+key);
  }

  deleteUpdateError(key:any=''){
    return this.db.delete('audit_answers', 'update_error_'+key);
  }



  
  deleteUser(key:string){
    return this.db.delete('audit_answers', key);
  }

  getData(key:string) {
    return this.db.get('audit_answers', key);
  }

  clearData(key:string) {
    return this.db.clear('audit_answers');
  }

  keysData() {
    return this.db.getAllKeys('audit_answers');
  }

  addImage(imgData:any,imgstorename=''){
    if(imgstorename ==''){
      imgstorename = 'imgData';
    }
    return this.db.put('audit_answers', imgData, imgstorename);
  }




  addChecklist(name:any){
    return this.db.put('audit_checklist', name, 'name');
  }

  deleteChecklist(key:string){
    return this.db.delete('audit_checklist', key);
  }

  getChecklist(key:string) {
    return this.db.get('audit_checklist', key);
  }
  
  
  //Local Storage 
  addLocalStorage(value:any){
    return this.db.put('local_storage', value, 'token');
  }

  deleteLocalStorage(){
    return this.db.delete('local_storage', 'token');
  }

  getLocalStorage() {
    return this.db.get('local_storage', 'token');
  }
}

interface MyDB extends DBSchema {
  'audit_checklist':{
    key:string;
    value:any;
  },
  'audit_answers':{
    key:string;
    value:any;
  },
  'local_storage':{
    key:string;
    value:any;
  },
}