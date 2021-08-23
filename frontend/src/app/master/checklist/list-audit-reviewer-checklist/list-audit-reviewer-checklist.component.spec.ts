import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListAuditReviewerChecklistComponent } from './list-audit-reviewer-checklist.component';

describe('ListAuditReviewerChecklistComponent', () => {
  let component: ListAuditReviewerChecklistComponent;
  let fixture: ComponentFixture<ListAuditReviewerChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListAuditReviewerChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListAuditReviewerChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
