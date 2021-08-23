import { Component, OnInit } from '@angular/core';
import * as $ from 'jquery';
import { AuthenticationService,UserService } from '@app/services';
import { User, Role } from '@app/models';
import { first,tap } from 'rxjs/operators';

@Component({
  selector: 'app-leftmenu',
  templateUrl: './leftmenu.component.html',
  styleUrls: ['./leftmenu.component.css']
})
export class LeftmenuComponent implements OnInit {

  userType:number;
  userdetails:any;
  menuStatus:any;
  role_name:any;
  resource_access:any;
  leftMenuStatusArr:any = {'showdownloadmenu':1};
  isOnline:any=1;
  initloadedfromoffline:any=0;
  constructor(public authservice: AuthenticationService, public userService:UserService) { }

  ngOnInit() {
    /*
    if(navigator.onLine){
      this.isOnline = 1;
    }else{
      this.isOnline = 0;
    }
    addEventListener('offline',(e)=>{
      this.isOnline = 0;
    });
    addEventListener('online',(e)=>{
      this.isOnline = 1;
    });
    */
    this.authservice.currentUser.subscribe(x => {
      if(x){
        let user = this.authservice.getDecodeToken();
        this.userType= user.decodedToken.user_type;
        this.userdetails= user.decodedToken;
        this.role_name = this.userdetails.role_name.toLowerCase();
        this.resource_access = this.userdetails.resource_access;
      }
    });

    this.authservice.userOnlineStatus.subscribe(x => {
      
      if(x === 1 && this.initloadedfromoffline){ //Online
        this.initloadedfromoffline = 0;
        setTimeout(() => { this.reinitiateLeftMenuData(); });
      }else if(x === 0){
        this.initloadedfromoffline = 1;
      }
    });
    /*
    this.userService.getMenuStatus()
    .pipe(first())
    .subscribe(res => {
      this.menuStatus = res;
    })
    */
    this.userService.getLeftMenuOptions()
    .pipe(first())
    .subscribe(res => {
      this.leftMenuStatusArr = res;
    })
   
  }
  checkRole(rolename,user_type=[],check_roles=0){

    
    if(this.userdetails.resource_access == 1){
      return true;
    }
    if(check_roles){
      if(user_type.includes(this.userType) && this.userdetails.rules.includes(rolename)){
        return true;
      }
    }else{
      if(user_type.includes(this.userType)){
        return true;
      }
    }
    if(this.userdetails.rules.includes(rolename)){
      return true;
    }
    return false;
  }
  checkOnlyUserType(user_type){
    if(user_type.includes(this.userType)){
      return true;
    }
    return false;
  }
  checkRoleArray(rolename=[],user_type=[],check_roles=0){

    
    if(this.userdetails.resource_access == 1){
      return true;
    }
    if(check_roles){
      if(user_type.includes(this.userType) && this.userdetails.rules.includes(rolename)){
        return true;
      }
    }else{
      if(user_type.includes(this.userType)){
        return true;
      }
    }

    if(rolename.length>0){
      let rval = false;
      rolename.forEach(role => {
        if(this.userdetails.rules.includes(role)){
          rval = true;
        }
      });
      return rval;
    }
    

    return false;
  }

  checkUserRole(rolename,user_types){
    
    
    if(this.userdetails.resource_access == 1){
      return true;
    }
    if( this.userdetails.user_type==2 && user_types.includes(this.userdetails.user_type)){
      return true;
    }
    if( this.userdetails.user_type==3){
      return true;
    }
    if( this.userdetails.user_type==1 && this.userdetails.rules.includes(rolename)){
      return true;
    }
    if( this.userdetails.user_type==3 && this.userdetails.role!=0  && this.userdetails.rules.includes(rolename)){
      return true;
    }
     
    return false;
  }

  reinitiateLeftMenuData(){
    let menuItemClick = function (e) {
      if (!$('#wrapper').hasClass('enlarged')) {
        if ($(this).parent().hasClass('has_sub')) {

        }
        if (!$(this).hasClass('subdrop')) {
          // hide any open menus and remove all other classes
          $('ul', $(this).parents('ul:first')).slideUp(350);
          $('a', $(this).parents('ul:first')).removeClass('subdrop');
          $('#sidebar-menu .pull-right i').removeClass('md-remove').addClass('md-add');

          // open our new menu and add the open class
          $(this).next('ul').slideDown(350);
          $(this).addClass('subdrop');
          $('.pull-right i', $(this).parents('.has_sub:last')).removeClass('md-add').addClass('md-remove');
          $('.pull-right i', $(this).siblings('ul')).removeClass('md-remove').addClass('md-add');
        } else if ($(this).hasClass('subdrop')) {
          $(this).removeClass('subdrop');
          $(this).next('ul').slideUp(350);
          $('.pull-right i', $(this).parent()).removeClass('md-remove').addClass('md-add');
        }
      }
    }

    let $menuItem = $('#sidebar-menu a');
    let ua = navigator.userAgent,
      event = (ua.match(/iP/i)) ? 'touchstart' : 'click';
    $menuItem.on(event, menuItemClick);

    // NAVIGATION HIGHLIGHT & OPEN PARENT
    $('#sidebar-menu ul li.has_sub a.active').parents('li:last').children('a:first').addClass('active').trigger('click');
  }


  ngAfterViewInit() {

    setTimeout(() => {
      this.reinitiateLeftMenuData();
      /*
      let menuItemClick = function (e) {
        if (!$('#wrapper').hasClass('enlarged')) {
          if ($(this).parent().hasClass('has_sub')) {

          }
          if (!$(this).hasClass('subdrop')) {
            // hide any open menus and remove all other classes
            $('ul', $(this).parents('ul:first')).slideUp(350);
            $('a', $(this).parents('ul:first')).removeClass('subdrop');
            $('#sidebar-menu .pull-right i').removeClass('md-remove').addClass('md-add');

            // open our new menu and add the open class
            $(this).next('ul').slideDown(350);
            $(this).addClass('subdrop');
            $('.pull-right i', $(this).parents('.has_sub:last')).removeClass('md-add').addClass('md-remove');
            $('.pull-right i', $(this).siblings('ul')).removeClass('md-remove').addClass('md-add');
          } else if ($(this).hasClass('subdrop')) {
            $(this).removeClass('subdrop');
            $(this).next('ul').slideUp(350);
            $('.pull-right i', $(this).parent()).removeClass('md-remove').addClass('md-add');
          }
        }
      }

      let $menuItem = $('#sidebar-menu a');
      let ua = navigator.userAgent,
        event = (ua.match(/iP/i)) ? 'touchstart' : 'click';
      $menuItem.on(event, menuItemClick);

      // NAVIGATION HIGHLIGHT & OPEN PARENT
      $('#sidebar-menu ul li.has_sub a.active').parents('li:last').children('a:first').addClass('active').trigger('click');
      */








      /*
      var Sidemenu = function() {
        this.$body = $("body"),
        this.$openLeftBtn = $(".open-left"),
        this.$menuItem = $("#sidebar-menu a")
      };
      Sidemenu.prototype.openLeftBar = function() {
        $("#wrapper").toggleClass("enlarged");
        $("#wrapper").addClass("forced");

        if($("#wrapper").hasClass("enlarged") && $("body").hasClass("fixed-left")) {
          $("body").removeClass("fixed-left").addClass("fixed-left-void");
        } else if(!$("#wrapper").hasClass("enlarged") && $("body").hasClass("fixed-left-void")) {
          $("body").removeClass("fixed-left-void").addClass("fixed-left");
        }
        
        if($("#wrapper").hasClass("enlarged")) {
          $(".left ul").removeAttr("style");
        } else {
          $(".subdrop").siblings("ul:first").show();
        }
        
        toggle_slimscroll(".slimscrollleft");
        $("body").trigger("resize");
      }

    

      //init sidemenu
      Sidemenu.prototype.init = function() {
        var $this  = this;

        var ua = navigator.userAgent,
          event = (ua.match(/iP/i)) ? "touchstart" : "click";
        
        //bind on click
        this.$openLeftBtn.on(event, function(e) {
          e.stopPropagation();
          $this.openLeftBar();
        });

        // LEFT SIDE MAIN NAVIGATION
        $this.$menuItem.on(event, $this.menuItemClick);

        // NAVIGATION HIGHLIGHT & OPEN PARENT
        $("#sidebar-menu ul li.has_sub a.active").parents("li:last").children("a:first").addClass("active").trigger("click");
      }

      //init Sidemenu
      $.Sidemenu = new Sidemenu, $.Sidemenu.Constructor = Sidemenu;


      $.Sidemenu.init();
*/




      
      }, 500);


      /*
      var w,h,dw,dh;
      var changeptype = function(){
          w = $(window).width();
          h = $(window).height();
          dw = $(document).width();
          dh = $(document).height();

          if($.browser.mobile === true){
              $("body").addClass("mobile").removeClass("fixed-left");
          }

          if(!$("#wrapper").hasClass("forced")){
            if(w > 1024){
              $("body").removeClass("smallscreen").addClass("widescreen");
                $("#wrapper").removeClass("enlarged");
            }else{
              $("body").removeClass("widescreen").addClass("smallscreen");
              $("#wrapper").addClass("enlarged");
              $(".left ul").removeAttr("style");
            }
            if($("#wrapper").hasClass("enlarged") && $("body").hasClass("fixed-left")){
              $("body").removeClass("fixed-left").addClass("fixed-left-void");
            }else if(!$("#wrapper").hasClass("enlarged") && $("body").hasClass("fixed-left-void")){
              $("body").removeClass("fixed-left-void").addClass("fixed-left");
            }

        }
        toggle_slimscroll(".slimscrollleft");
      }


      function toggle_slimscroll(item){
          if($("#wrapper").hasClass("enlarged")){
            $(item).css("overflow","inherit").parent().css("overflow","inherit");
            $(item). siblings(".slimScrollBar").css("visibility","hidden");
          }else{
            $(item).css("overflow","hidden").parent().css("overflow","hidden");
            $(item). siblings(".slimScrollBar").css("visibility","visible");
          }
      }
      */

  }
}
