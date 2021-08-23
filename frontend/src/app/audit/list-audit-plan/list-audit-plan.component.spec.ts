import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListAuditPlanComponent } from './list-audit-plan.component';

describe('ListAuditPlanComponent', () => {
  let component: ListAuditPlanComponent;
  let fixture: ComponentFixture<ListAuditPlanComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListAuditPlanComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListAuditPlanComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
