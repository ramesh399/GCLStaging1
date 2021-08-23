import { Component, OnInit, Input,SimpleChanges } from '@angular/core';

@Component({
  selector: 'app-popupmodalmessage',
  templateUrl: './popupmodalmessage.component.html',
  styleUrls: ['./popupmodalmessage.component.scss']
})
export class PopupmodalmessageComponent implements OnInit {

  @Input() modal: any;
  @Input() alertInfoMessage: any;
  @Input() alertSuccessMessage: any;
  @Input() alertErrorMessage: any;
  okBtn:any;
  cancelBtn:any;
  constructor() { }

  ngOnInit() {
  }
  commonModalAction(){

  }

}
