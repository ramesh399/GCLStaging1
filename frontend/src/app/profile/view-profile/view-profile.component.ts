import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import {Observable} from 'rxjs';
import { first } from 'rxjs/operators';
import {saveAs} from 'file-saver';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-view-profile',
  templateUrl: './view-profile.component.html',
  styleUrls: ['./view-profile.component.scss']
})
export class ViewProfileComponent implements OnInit {

  constructor( private modalService: NgbModal, private activatedRoute:ActivatedRoute,private userService:UserService) { }
  id:any;
  error = '';
  loading = false;
  data:User;
  
  
  ngOnInit() {

    this.id = this.activatedRoute.snapshot.queryParams.id;   
    this.userService.getProfileDetails().pipe(first())
    .subscribe(res => {
      this.data = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });

     
  }

  modalss:any;
  open(content,arg='') {
    this.modalss = this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title',centered: true});
  }

  downloadFile(fileid,filename){
    this.userService.downloadFile({id:fileid})
    .subscribe(res => {
      this.modalss.close();
      let fileextension = filename.split('.').pop(); 
      let contenttype = this.userService.docsContentType[fileextension];
      saveAs(new Blob([res],{type:contenttype}),filename);
    });
  }

}
