import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditAuditReviewerChecklistComponent } from './edit-audit-reviewer-checklist.component';

describe('EditAuditReviewerChecklistComponent', () => {
  let component: EditAuditReviewerChecklistComponent;
  let fixture: ComponentFixture<EditAuditReviewerChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditAuditReviewerChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditAuditReviewerChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
