import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RequestWithdrawUnitComponent } from './request-withdraw-unit.component';

describe('RequestWithdrawUnitComponent', () => {
  let component: RequestWithdrawUnitComponent;
  let fixture: ComponentFixture<RequestWithdrawUnitComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RequestWithdrawUnitComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RequestWithdrawUnitComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
