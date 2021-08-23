import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CertificationDetailsReportComponent } from './certification-details-report.component';

describe('CertificationDetailsReportComponent', () => {
  let component: CertificationDetailsReportComponent;
  let fixture: ComponentFixture<CertificationDetailsReportComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CertificationDetailsReportComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CertificationDetailsReportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
