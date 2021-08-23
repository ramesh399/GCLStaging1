import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewAuditPlanningChecklistComponent } from './view-audit-planning-checklist.component';

describe('ViewAuditPlanningChecklistComponent', () => {
  let component: ViewAuditPlanningChecklistComponent;
  let fixture: ComponentFixture<ViewAuditPlanningChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewAuditPlanningChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewAuditPlanningChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
