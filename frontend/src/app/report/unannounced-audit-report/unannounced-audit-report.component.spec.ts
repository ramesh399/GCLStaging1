import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UnannouncedAuditReportComponent } from './unannounced-audit-report.component';

describe('UnannouncedAuditReportComponent', () => {
  let component: UnannouncedAuditReportComponent;
  let fixture: ComponentFixture<UnannouncedAuditReportComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UnannouncedAuditReportComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UnannouncedAuditReportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
