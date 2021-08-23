import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ProgramAuditReportComponent } from './program-audit-report.component';

describe('ProgramAuditReportComponent', () => {
  let component: ProgramAuditReportComponent;
  let fixture: ComponentFixture<ProgramAuditReportComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ProgramAuditReportComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ProgramAuditReportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
