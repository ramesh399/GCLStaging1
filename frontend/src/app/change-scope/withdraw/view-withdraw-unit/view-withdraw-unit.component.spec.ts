import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewWithdrawUnitComponent } from './view-withdraw-unit.component';

describe('ViewWithdrawUnitComponent', () => {
  let component: ViewWithdrawUnitComponent;
  let fixture: ComponentFixture<ViewWithdrawUnitComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewWithdrawUnitComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewWithdrawUnitComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
