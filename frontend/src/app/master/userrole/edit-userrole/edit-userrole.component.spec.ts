import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditUserroleComponent } from './edit-userrole.component';

describe('EditUserroleComponent', () => {
  let component: EditUserroleComponent;
  let fixture: ComponentFixture<EditUserroleComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditUserroleComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditUserroleComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
