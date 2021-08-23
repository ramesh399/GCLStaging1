import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ChangeUsernamePasswordComponent } from './change-username-password.component';

describe('ChangeUsernamePasswordComponent', () => {
  let component: ChangeUsernamePasswordComponent;
  let fixture: ComponentFixture<ChangeUsernamePasswordComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ChangeUsernamePasswordComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ChangeUsernamePasswordComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
