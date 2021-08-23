import { Component, OnInit, Renderer2 } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import * as $ from 'jquery';

@Component({
  selector: 'app-logout',
  templateUrl: './logout.component.html',
  styleUrls: ['./logout.component.scss']
})
export class LogoutComponent implements OnInit {

  constructor(
	private renderer: Renderer2,
	private route: ActivatedRoute,
	private router: Router,
	private errorSummary: ErrorSummaryService
  ) { 
    this.renderer.removeClass(document.body, 'fixed-left');
	this.renderer.addClass(document.body, 'login');
  }

  ngOnInit() {
  }
  
  ngOnDestroy()
  {
	this.renderer.addClass(document.body, 'fixed-left');
	this.renderer.removeClass(document.body, 'login');
  }
  
  setHeight()
  {
	  $('.login').css('min-height', $(window).innerHeight());
  }
  
  ngAfterViewInit() {
    setTimeout(() => {
		$(window).resize(()=>this.setHeight());
	
		this.setHeight()
	}, 500);	
  }

}
