import { Component, OnInit } from '@angular/core';
import { TreeviewItem, TreeviewConfig } from '../../../../lib';
import { UserRoleService } from '@app/services/master/userrole/userrole.service';
import { ErrorSummaryService } from '@app/helpers/errorsummary.service';
import { FormGroup, FormBuilder, Validators, FormControl,FormArray } from '@angular/forms';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';

@Component({
  selector: 'app-add-userrole',
  templateUrl: './add-userrole.component.html',
  styleUrls: ['./add-userrole.component.scss'],
  providers: [
    UserRoleService
  ]
})
export class AddUserroleComponent implements OnInit {
  form : FormGroup;
  loading:any=[];
  buttonDisable = false;
  error:any;
  submittedError = false;
  actionsError = '';
  success:any;
  title = 'Add User Role';
  btnLabel = 'Save';
  role_nameErrors = '';
  roletypes:any=[];

  dropdownEnabled = true;
    items: TreeviewItem[];
    values: number[];
    config = TreeviewConfig.create({
        hasAllCheckBox: false,
        hasFilter: false,
        hasCollapseExpand: true,
        decoupleChildFromParent: false,
        maxHeight: 700
    });

    buttonClasses = [
        'btn-outline-primary',
        'btn-outline-secondary',
        'btn-outline-success',
        'btn-outline-danger',
        'btn-outline-warning',
        'btn-outline-info',
        'btn-outline-light',
        'btn-outline-dark'
    ];
    buttonClass = this.buttonClasses[0];

    constructor(
        private router: Router,
        private fb:FormBuilder,
        private service: UserRoleService,private errorSummary: ErrorSummaryService
    ) { }

    ngOnInit() {
        this.service.getUserPrivileges().subscribe(res=>{
          this.items = res;
        });

        this.loading['roles'] = true;
        this.service.getUserRoleTypes().subscribe(res=>{
          this.roletypes = res.userroles;
          this.loading['roles'] = false;
        });
        //  this.items = this.service.getUserPrivileges();
        this.form = this.fb.group({
          role_name:['',[Validators.required, this.errorSummary.noWhitespaceValidator, Validators.maxLength(255),Validators.pattern("^[a-zA-Z \'\-]+$")]],
          resource_access:['',[Validators.required]],
          privilege_id:[''],
          enable_oss:['']
        });
    }
    get f() { return this.form.controls; }
    
    onSubmit(){
      
      //console.log(this.form.value);
      this.form.value.privilege_id = [];
      //this.form.value.resource_access = 2;
      
      if(this.form.value.resource_access==2 && this.values.length<=0){
        this.actionsError = 'Please select the actions';
        return false;
      }
      if(this.form.value.resource_access==2){
        this.form.value.privilege_id = this.values;
      }
      
     // console.log(this.form.value);
      //return false;
      if (this.form.valid) {
        
       // console.log(this.form.value);
        this.loading['button'] = true;
       

        this.service.addUserRole(this.form.value)
        .pipe(
          first()        
        )
        .subscribe(res => {
          //console.log(res);
            if(res.status){
              this.success = {summary:res.message};
              this.buttonDisable = true;
              setTimeout(()=>this.router.navigate(['/master/userrole/list']),this.errorSummary.redirectTime);
            }else if(res.status == 0){
              this.error = {summary:this.errorSummary.getErrorSummary(res.message,this,this.form)};			  
            }else{			      
              this.error = {summary:res};
            }
            this.loading['button'] = false;
           
        },
        error => {
            this.error = {summary:error};
            this.loading['button'] = false;
        });
        //console.log('sdfsdfdf');
      } else {
        this.error = {summary:this.errorSummary.errorSummaryText};
        this.errorSummary.validateAllFormFields(this.form); 
        
      }
    }

}
