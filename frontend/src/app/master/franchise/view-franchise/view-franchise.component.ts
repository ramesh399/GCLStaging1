import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import {Observable} from 'rxjs';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-view-franchise',
  templateUrl: './view-franchise.component.html',
  styleUrls: ['./view-franchise.component.scss']
})
export class ViewFranchiseComponent implements OnInit {

  constructor(private activatedRoute:ActivatedRoute,private userservice:UserService) { }
  id:any;
  error = '';
  loading = false;
  userdata:User;
  userEntries:any;
  userRoleStatus: any = {};

  ngOnInit() {

    this.id = this.activatedRoute.snapshot.queryParams.id;   
    this.userservice.getUserDetails({'id':this.id}).pipe(first())
    .subscribe(res => {
      this.userdata = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });


    this.userservice.getOSSuser({'id':this.id}).pipe(first())
    .subscribe(res => {
      
      
       res.data.forEach(element => {
         this.userservice.getUserData({
           id: element.id,
           actiontype: 'role'
          }).pipe(first()).subscribe(ir => {
            //console.log(ir.data)
            if(ir.data) {
              ir.data.role_id_approved.forEach(el => {
                if(!this.userRoleStatus[element.id]) {
                  this.userRoleStatus[element.id] = {}
                }
               if(this.userRoleStatus[element.id] && el.franchise_name.includes(this.userdata.osp_number)) 
                this.userRoleStatus[element.id][el.role_name] = el.status;
                
              })
            }
            this.userEntries = res.data;
          })
        });

    },
    error => {
        this.error = error;
        this.loading = false;
    });

  }


  getUserRoleFiltered (roles, id) {
    let res = []
   // console.log(this.userRoleStatus)  
    roles.split(",").forEach(element => {
      console.log(this.userRoleStatus, element, id)
      if(this.userRoleStatus[id]  && this.userRoleStatus[id][element] == "0" ) {
        res.push(element)
      } 
      
    });

    return res.join(",");
  }
}
