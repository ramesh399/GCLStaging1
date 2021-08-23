import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditReviewerRemediationComponent } from './audit-reviewer-remediation.component';

describe('AuditReviewerRemediationComponent', () => {
  let component: AuditReviewerRemediationComponent;
  let fixture: ComponentFixture<AuditReviewerRemediationComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditReviewerRemediationComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditReviewerRemediationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
