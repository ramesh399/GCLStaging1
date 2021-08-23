import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import {Observable} from 'rxjs';
import { first } from 'rxjs/operators';


@Component({
  selector: 'app-view-customer',
  templateUrl: './view-customer.component.html',
  styleUrls: ['./view-customer.component.scss']
})
export class ViewCustomerComponent implements OnInit {

  constructor(private activatedRoute:ActivatedRoute,private userservice:UserService) { }
  id:any;
  error = '';
  loading = false;
  userdata:User;

  ngOnInit() {

    this.id = this.activatedRoute.snapshot.queryParams.id;   
    this.userservice.getCustomerDetails({'id':this.id}).pipe(first())
    .subscribe(res => {
      this.userdata = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });

     
  }

}
