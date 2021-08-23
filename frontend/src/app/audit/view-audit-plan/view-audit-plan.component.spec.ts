import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewAuditPlanComponent } from './view-audit-plan.component';

describe('ViewAuditPlanComponent', () => {
  let component: ViewAuditPlanComponent;
  let fixture: ComponentFixture<ViewAuditPlanComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewAuditPlanComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewAuditPlanComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
