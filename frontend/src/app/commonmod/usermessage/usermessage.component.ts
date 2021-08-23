import { Component, OnInit, Input,SimpleChanges } from '@angular/core';

@Component({
  selector: 'app-usermessage',
  templateUrl: './usermessage.component.html',
  styleUrls: ['./usermessage.component.scss']
})
export class UsermessageComponent implements OnInit {
  @Input() success: any;
  @Input() error: any;
  
  constructor() {  }

  ngOnInit() {}

  ngOnChanges(changes: SimpleChanges) {
    //console.log(changes['error']);
    //console.log(changes['success']);
    if((changes['error']!== undefined && changes['error'].firstChange==false) || (changes['success']!== undefined && changes['success'].firstChange==false)){
      setTimeout(() => {
        this.success='';
        this.error='';
      }, 2000);
    }
  }
}
