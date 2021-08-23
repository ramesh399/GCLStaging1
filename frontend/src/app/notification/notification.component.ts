import { Component, OnInit } from '@angular/core';
import { NotificationService } from '@app/services/notification.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { ActivatedRoute,Params,Router } from '@angular/router';
import { first } from 'rxjs/operators';

import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-notification',
  templateUrl: './notification.component.html',
  styleUrls: ['./notification.component.scss']
})
export class NotificationComponent implements OnInit {

  title = 'Notification';  
  loading = false;
  error:any;
  success:any;
  submittedError = false;
  notificationData:any;

  constructor(private http: HttpClient,private router: Router,private notificationService:NotificationService,private errorSummary: ErrorSummaryService) { }

  ngOnInit() {

    this.notificationService.getUserData().pipe(first())
    .subscribe(res => {
    this.notificationData = res['data'];
    },
    error => {
        this.error = error;
        this.loading = false;
    });	  
  }

}
