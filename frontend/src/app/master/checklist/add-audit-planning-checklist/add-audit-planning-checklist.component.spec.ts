import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddAuditPlanningChecklistComponent } from './add-audit-planning-checklist.component';

describe('AddAuditPlanningChecklistComponent', () => {
  let component: AddAuditPlanningChecklistComponent;
  let fixture: ComponentFixture<AddAuditPlanningChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddAuditPlanningChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddAuditPlanningChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
