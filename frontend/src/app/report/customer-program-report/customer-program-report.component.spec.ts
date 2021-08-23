import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CustomerProgramReportComponent } from './customer-program-report.component';

describe('CustomerProgramReportComponent', () => {
  let component: CustomerProgramReportComponent;
  let fixture: ComponentFixture<CustomerProgramReportComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CustomerProgramReportComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CustomerProgramReportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
