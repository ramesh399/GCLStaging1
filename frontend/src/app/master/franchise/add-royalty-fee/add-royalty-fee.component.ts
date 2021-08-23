import { Component, OnInit } from '@angular/core';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { AuthenticationService } from '@app/services';

@Component({
  selector: 'app-add-royalty-fee',
  templateUrl: './add-royalty-fee.component.html',
  styleUrls: ['./add-royalty-fee.component.scss']
})
export class AddRoyaltyFeeComponent implements OnInit {

  standard_status=false;
  tc_status=false; 
  success:any;
  userType:number;
  userdetails:any;
  userdecoded:any;
  found:boolean;
  tcfound:boolean;
  constructor(private authservice:AuthenticationService,private errorSummary: ErrorSummaryService) { 
  
  }
  

  ngOnInit()
  {
    this.authservice.currentUser.subscribe(x => {
      if(x){
        
         
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        //this.userdetails.rules.includes('add_royalty_fee')
        
        if(this.userdetails.resource_access !=1){
          this.found = this.userdetails.rules.some(r=> ['add_royalty_fee','edit_royalty_fee','view_royalty_fee','delete_royalty_fee'].indexOf(r) >= 0);
          this.tcfound = this.userdetails.rules.some(r=> ['add_tc_fee','edit_tc_fee','view_tc_fee','delete_tc_fee'].indexOf(r) >= 0);
          if(this.found){
            this.standard_status = true;
          }else if(this.tcfound){
            this.tc_status = true;
          }
        }else if(this.userdetails.resource_access ==1){
          this.found = true;
          this.tcfound = true;
          this.standard_status = true;
        }
        
      }else{
        this.userdecoded=null;
      }
    });
  }

  changeAuditExecutionTab(arg)
  {
    this.standard_status=false;
    this.tc_status=false;
	  this.success = '';
	  if(arg=='standard'){
		   this.standard_status=true;
	  }else if(arg=='tc'){
      this.tc_status=true;
    }
  }
}
