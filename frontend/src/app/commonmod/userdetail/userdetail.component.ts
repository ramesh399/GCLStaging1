import { Component, OnInit, Input } from '@angular/core';
import { UserService } from '@app/services/master/user/user.service';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { User } from '@app/models/master/user';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-userdetail',
  templateUrl: './userdetail.component.html',
  styleUrls: ['./userdetail.component.scss']
})
export class UserdetailComponent implements OnInit {

  @Input() userdecoded: any;
  @Input() user_id: number;
  @Input() app_id: number;
  @Input() showtype: any;
  error = '';
  loading = false;
  userdata:User;

  constructor(private activatedRoute:ActivatedRoute,private userservice:UserService) { }
  
  

  ngOnInit() {
    this.loading = true;
    let app_id = this.app_id?this.app_id:'';
    let user_id = this.user_id?this.user_id:'';
    
    const data=`app_id=${app_id}&id=${user_id}`;
    this.userservice.getCustomerDetailsByGet(data).pipe(first())
    .subscribe(res => {
      this.userdata = res.data;
      this.loading = false;
    },
    error => {
        this.error = error;
        this.loading = false;
    });
  }

}