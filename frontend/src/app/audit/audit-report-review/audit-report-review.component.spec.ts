import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditReportReviewComponent } from './audit-report-review.component';

describe('AuditReportReviewComponent', () => {
  let component: AuditReportReviewComponent;
  let fixture: ComponentFixture<AuditReportReviewComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditReportReviewComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditReportReviewComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
