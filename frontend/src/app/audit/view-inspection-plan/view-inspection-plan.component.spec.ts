import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewInspectionPlanComponent } from './view-inspection-plan.component';

describe('ViewInspectionPlanComponent', () => {
  let component: ViewInspectionPlanComponent;
  let fixture: ComponentFixture<ViewInspectionPlanComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewInspectionPlanComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewInspectionPlanComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
