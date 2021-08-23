import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditReviewerChecklistComponent } from './audit-reviewer-checklist.component';

describe('AuditReviewerChecklistComponent', () => {
  let component: AuditReviewerChecklistComponent;
  let fixture: ComponentFixture<AuditReviewerChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditReviewerChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditReviewerChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
