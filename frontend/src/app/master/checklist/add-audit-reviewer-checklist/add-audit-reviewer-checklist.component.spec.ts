import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddAuditReviewerChecklistComponent } from './add-audit-reviewer-checklist.component';

describe('AddAuditReviewerChecklistComponent', () => {
  let component: AddAuditReviewerChecklistComponent;
  let fixture: ComponentFixture<AddAuditReviewerChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddAuditReviewerChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddAuditReviewerChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
