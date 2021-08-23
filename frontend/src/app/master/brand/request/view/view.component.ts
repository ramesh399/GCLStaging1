import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { UserService } from '@app/services/master/user/user.service';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-view',
  templateUrl: './view.component.html',
  styleUrls: ['./view.component.scss']
})
export class ViewComponent implements OnInit {
  id: any;
  userdata: any;
  error: any;
  loading: boolean;
  

  constructor(private activatedRoute:ActivatedRoute,private userservice:UserService) { }

  ngOnInit() {
    this.id = this.activatedRoute.snapshot.queryParams.id;   
    this.userservice.getBrandUserDetails({'id':this.id}).pipe(first())
    .subscribe(res => {
      this.userdata = res.data;
    },
    error => {
        this.error = error;
        this.loading = false;
    });
  }

}
