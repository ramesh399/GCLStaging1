import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditPlanChecklistComponent } from './audit-plan-checklist.component';

describe('AuditPlanChecklistComponent', () => {
  let component: AuditPlanChecklistComponent;
  let fixture: ComponentFixture<AuditPlanChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditPlanChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditPlanChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
