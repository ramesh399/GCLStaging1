import {DecimalPipe} from '@angular/common';
import { ActivatedRoute ,Params, Router } from '@angular/router';
import {Directive, Component, QueryList, ViewChildren } from '@angular/core';
import {Observable} from 'rxjs';

import { Enquiry } from '@app/models/enquiry';

import {EnquiryService} from '@app/services/enquiry.service';
import {NgbdSortableHeader, SortEvent,PaginationList,commontxt} from '@app/helpers/sortable.directive';
import { AuthenticationService } from '@app/services/authentication.service';
import { Standard } from '@app/services/standard';
import { StandardService } from '@app/services/standard.service';
import { Country } from '@app/services/country';
import { CountryService } from '@app/services/country.service';
import { UserService } from '@app/services/master/user/user.service';
import { User } from '@app/models/master/user';
import { first } from 'rxjs/operators';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';

@Component({
  selector: 'app-enquiry-list',
  templateUrl: './enquiry-list.component.html',
  styleUrls: ['./enquiry-list.component.css'],
  providers: [EnquiryService, DecimalPipe]
})

export class EnquiryListComponent {
  

  enquiries$: Observable<Enquiry[]>;
  total$: Observable<number>;
  type:number;
  //sno:number;
  paginationList = PaginationList;
  commontxt = commontxt;
  userType:number;
  userdetails:any;
  userdecoded:any;
  title='';
  standardList:Standard[];
  countryList:Country[];
  franchiseList:User[];
  error:any;
  success:any;

  @ViewChildren(NgbdSortableHeader) headers: QueryList<NgbdSortableHeader>;

  constructor(private activatedRoute:ActivatedRoute,public service: EnquiryService, private router: Router, private authservice:AuthenticationService,private standardservice: StandardService,private countryservice: CountryService,private userservice: UserService) {
    this.enquiries$ = service.enquiries$;
    this.total$ = service.total$;
	
	this.router.routeReuseStrategy.shouldReuseRoute = () => false;
	
    this.type = this.activatedRoute.snapshot.queryParams.type;
	
	this.standardservice.getStandard().subscribe(res => {
		this.standardList = res['standards'];     
    });
	
	this.countryservice.getCountry().subscribe(res => {	
      this.countryList = res['countries'];
    });
	
	if(this.type==1 || this.type==2)
	{
		this.userservice.getAllUser({type:3}).pipe(first())
		.subscribe(res => {
		  this.franchiseList = res.users;
		},
		error => {
			this.error = {summary:error};
		});
	}


    this.authservice.currentUser.subscribe(x => {
      if(x){
        
         
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        
      }else{
        this.userdecoded=null;
      }
    });
	
	if(this.type==1 && this.userType==3)
	{
		this.title='Assigned Enquiries';
	}else if(this.type==1 && this.userType==1){
		this.title='Forwarded List';
	}else if(this.type==2){
		this.title='Discard List';
	}else{
		this.title='Received List';
	}
    //console.log
  }

  

  onSort({column, direction}: SortEvent) {
    // resetting other headers
    //console.log('sdfsdfdsf');
    this.headers.forEach(header => {
      if (header.sortable !== column) {
        header.direction = '';
      }
    });

    this.service.sortColumn = column;
    this.service.sortDirection = direction;
  }
  
  getSelectedValue(val)
  {
    return this.standardList.find(x=> x.id==val).code;    
  }
  
  getSelectedCountryValue(val)
  {
    return this.countryList.find(x=> x.id==val).name;    
  }
  
  getSelectedFranchiseValue(val)
  {
    return this.franchiseList.find(x=> x.id==val).osp_details;    
  }



  //implements OnInit
  //currentUser: User;
  /*
  constructor(private enquiry: EnquiryService) {

    

  }
  
  ngOnInit() {
    this.enquiry.getEnquiry().subscribe(enquiries => {
      console.log(enquiries);
    });
  }
  
*/

  /*
  ngOnInit() {
    console.log(this.currentUser.role);

    const source$ = range(0, 10);

    source$.pipe(
      filter(x => x % 2 === 0),
      map(x => x + x),
      scan((acc, x) => acc + x, 0)
    )
    .subscribe(x => console.log(x))
      
    console.log(this.decodedToken);
    console.log(this.expirationDate);
    console.log(this.isExpired);
    
  }
  */

}
