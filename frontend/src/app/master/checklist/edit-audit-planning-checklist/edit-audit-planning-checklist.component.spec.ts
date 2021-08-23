import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditAuditPlanningChecklistComponent } from './edit-audit-planning-checklist.component';

describe('EditAuditPlanningChecklistComponent', () => {
  let component: EditAuditPlanningChecklistComponent;
  let fixture: ComponentFixture<EditAuditPlanningChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditAuditPlanningChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditAuditPlanningChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
