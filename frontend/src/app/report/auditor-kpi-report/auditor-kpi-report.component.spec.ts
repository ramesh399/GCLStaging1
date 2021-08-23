import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditorKpiReportComponent } from './auditor-kpi-report.component';

describe('AuditorKpiReportComponent', () => {
  let component: AuditorKpiReportComponent;
  let fixture: ComponentFixture<AuditorKpiReportComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditorKpiReportComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditorKpiReportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
