import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewAuditReviewerChecklistComponent } from './view-audit-reviewer-checklist.component';

describe('ViewAuditReviewerChecklistComponent', () => {
  let component: ViewAuditReviewerChecklistComponent;
  let fixture: ComponentFixture<ViewAuditReviewerChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewAuditReviewerChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewAuditReviewerChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
