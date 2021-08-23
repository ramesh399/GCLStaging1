import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListAuditPlanningChecklistComponent } from './list-audit-planning-checklist.component';

describe('ListAuditPlanningChecklistComponent', () => {
  let component: ListAuditPlanningChecklistComponent;
  let fixture: ComponentFixture<ListAuditPlanningChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListAuditPlanningChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListAuditPlanningChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
