import { Component, OnInit } from '@angular/core';
import { AuthenticationService } from '@app/services';
import { Router } from '@angular/router';
import {NgbModal, ModalDismissReasons, NgbModalOptions} from '@ng-bootstrap/ng-bootstrap';

import * as $ from 'jquery';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css']
})
export class HeaderComponent implements OnInit {
  displaynameVal ='';
  nameWithRole='';
  logoURL ='/enquiry/list';
  
  title = 'ng-bootstrap-modal-demo';
  closeResult: string;
  modalOptions:NgbModalOptions;
  userdetails:any;
  isOnline:any=1;

  constructor(public authservice:AuthenticationService,private router:Router,private modalService: NgbModal) { 
    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.displaynameVal = user.decodedToken.displayname;
        let userType = user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
		
		if(userType == 1){
			this.nameWithRole=user.decodedToken.company_name+' - '+user.decodedToken.role_name+' ( User )';
		}else if(userType == 2){
			this.nameWithRole=user.decodedToken.company_name+' ( Customer )';
		}else{
			this.nameWithRole=user.decodedToken.company_name+' ( OSS )';
		}	
		
		if(userType == 1){
			this.logoURL='/user/dashboard';
		}else if(userType == 2){
			this.logoURL='/customer/dashboard';	
		}else{
			this.logoURL='/enquiry/list';
		}	
      }
    });
	
	this.modalOptions = {
      backdrop:'static',
      backdropClass:'customBackdrop'
    }
  }
  initloadedfromoffline:any=0;
  ngOnInit() {
    
	  let $openLeftBtn = $('.open-left');
    let $menuItem = $('#sidebar-menu a');
    let ua = navigator.userAgent,
      event = (ua.match(/iP/i)) ? 'touchstart' : 'click';
    //bind on click
    $openLeftBtn.on(event, function (e) {
      e.stopPropagation();
      openLeftBar();
    });
    
    function openLeftBar() {
      $('#wrapper').toggleClass('enlarged');
      $('#wrapper').addClass('forced');

      if ($('#wrapper').hasClass('enlarged') && $('body').hasClass('fixed-left')) {
        $('body').removeClass('fixed-left').addClass('fixed-left-void');
      } else if (!$('#wrapper').hasClass('enlarged') && $('body').hasClass('fixed-left-void')) {
        $('body').removeClass('fixed-left-void').addClass('fixed-left');
      }

      if ($('#wrapper').hasClass('enlarged')) {
        $('.left ul').removeAttr('style');
      } else {
        $('.subdrop').siblings('ul:first').show();
      }
      toggle_slimscroll('.slimscrollleft');
      $('body').trigger('resize');
    }
    function toggle_slimscroll(item) {
      if ($('#wrapper').hasClass('enlarged')) {
        $(item).css('overflow', 'inherit').parent().css('overflow', 'inherit');
        $(item).siblings('.slimScrollBar').css('visibility', 'hidden');
      } else {
        $(item).css('overflow', 'hidden').parent().css('overflow', 'hidden');
        $(item).siblings('.slimScrollBar').css('visibility', 'visible');
      }
    }
    this.authservice.userOnlineStatus.subscribe(x => {
      //console.log(x);
      if(x == 1 && this.initloadedfromoffline){ //Online
        setTimeout(() => { this.reinitiateDropDownData(); });
      }else if(x === 0){
        this.initloadedfromoffline = 1;
      }
    });
  } 
  
  open(content) {
    this.modalService.open(content, this.modalOptions).result.then((result) => {
      this.closeResult = `Closed with: ${result}`;
    }, (reason) => {
      this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
    });
  }
  
  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return  `with: ${reason}`;
    }
  }
  
  get displayName(){
    return this.displaynameVal;
  }
  reinitiateDropDownData(){
    
    var $userdp=$(".user-dp a.dropdown-toggle");
    var $notipad=$(".noti-pad a.dropdown-toggle");

    $userdp.click(function(ev) {
      $notipad.next().hide();
      $(this).next().toggle();
      if($(this).next().css('display')=='none')
      {
        $('.material-icons').html('keyboard_arrow_down');
      }else{
        $('.material-icons').html('keyboard_arrow_up');
      }		
      return false;
    });

    $('body').click(function(){
      //$(".user-dp a.dropdown-toggle").trigger('click');
      $notipad.next().hide();
      $userdp.next().hide();
      $('.material-icons').html('keyboard_arrow_down');
    });

    $notipad.click(function(ev) {
      $userdp.next().hide();
      $(this).next().toggle();
      return false;
    });

  
    $("div.dropdown-menu a").click(function(ev) {
      $(".dropdown-menu-right").hide();
      $('.material-icons').html('keyboard_arrow_up');
      return false;   
    });
   
  }
  ngAfterViewInit() {
    setTimeout(() => {
      this.reinitiateDropDownData();
        /*console.log('Test');
	   
	      var $userdp=$(".user-dp a.dropdown-toggle");
		    var $notipad=$(".noti-pad a.dropdown-toggle");
		
        $userdp.click(function(ev) {
          $notipad.next().hide();
          $(this).next().toggle();
          if($(this).next().css('display')=='none')
          {
            $('.material-icons').html('keyboard_arrow_down');
          }else{
            $('.material-icons').html('keyboard_arrow_up');
          }		
          return false;
        });
		
        $('body').click(function(){
          //$(".user-dp a.dropdown-toggle").trigger('click');
          $notipad.next().hide();
          $userdp.next().hide();
          $('.material-icons').html('keyboard_arrow_down');
        });
		
		    $notipad.click(function(ev) {
          $userdp.next().hide();
          $(this).next().toggle();
          return false;
        });
		
		  
        $("div.dropdown-menu a").click(function(ev) {
          $(".dropdown-menu-right").hide();
			    $('.material-icons').html('keyboard_arrow_up');
			    return false;   
		    });
		*/
		
		/*
		$(document).ready(function() {
          $("a.dropdown-toggle").click(function(ev) {
              $("a.dropdown-toggle").dropdown("toggle");
              return false;
          });
          $("ul.dropdown-menu a").click(function(ev) {
              $("a.dropdown-toggle").dropdown("toggle");
              return false;
          });
		});
		
		
		 $(function () {

            var $in, $out, $duration;
            $('.dropdown-menu')
                .on('shown.bs.dropdown',
                    function (e) {
						console.log('Hey Raj');
                        if ($(this).attr('data-animation')) {
                            let $animations = [];
                            const $animation = $(this).data('animation');
                            $animations = $animation.split(',');
                            $in = 'animated ' + $animations[0];
                            $out = 'animated ' + $animations[1];
                            $duration = '';
                            if (!$animations[2]) {
                                $duration = 500;
                            } else {
                                $duration = $animations[2];
                            }
                            $(this).find('.dropdown-menu').removeClass($out);
                            $(this).find('.dropdown-menu').addClass($in);
                        }
                    });
					
					 $('.dropdown')
                .on('hide.bs.dropdown',
                    function (e) {
                        if ($(this).attr('data-animation')) {
                            e.preventDefault();
                            const $this = $(this);
                            const $targetControl = $this.find('.dropdown-menu');

                            $targetControl.addClass($out);
                            setTimeout(function () {
                                $this.removeClass('open');
                            },
                            $duration);
                        }
                    });
					
					console.log('Test');
        });
		*/

    }, 500);
  }


  logout() {
    this.authservice.logout();
    //this.router.navigate(['/logout']);
	this.router.navigate(['/login']);
  }

}
