import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { InspectionPlanComponent } from './inspection-plan.component';

describe('InspectionPlanComponent', () => {
  let component: InspectionPlanComponent;
  let fixture: ComponentFixture<InspectionPlanComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ InspectionPlanComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(InspectionPlanComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
