import { Component, OnInit } from '@angular/core';
import { EnquiryDetailService } from '@app/services/enquiry-detail.service';
@Component({
  selector: 'app-footer',
  templateUrl: './footer.component.html',
  styleUrls: ['./footer.component.css']
})
export class FooterComponent implements OnInit {

  constructor(private enquiry:EnquiryDetailService) { }
  year:any='';
  ngOnInit() {
    this.enquiry.getYear().subscribe(res => {
      this.year = res;
    });
  }

}
