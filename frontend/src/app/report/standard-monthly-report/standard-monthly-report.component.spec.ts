import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { StandardMonthlyReportComponent } from './standard-monthly-report.component';

describe('StandardMonthlyReportComponent', () => {
  let component: StandardMonthlyReportComponent;
  let fixture: ComponentFixture<StandardMonthlyReportComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ StandardMonthlyReportComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(StandardMonthlyReportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
