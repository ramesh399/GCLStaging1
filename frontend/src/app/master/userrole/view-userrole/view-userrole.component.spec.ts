import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewUserroleComponent } from './view-userrole.component';

describe('ViewUserroleComponent', () => {
  let component: ViewUserroleComponent;
  let fixture: ComponentFixture<ViewUserroleComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewUserroleComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewUserroleComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
