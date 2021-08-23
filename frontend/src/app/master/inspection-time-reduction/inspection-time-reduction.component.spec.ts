import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { InspectionTimeReductionComponent } from './inspection-time-reduction.component';

describe('InspectionTimeReductionComponent', () => {
  let component: InspectionTimeReductionComponent;
  let fixture: ComponentFixture<InspectionTimeReductionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ InspectionTimeReductionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(InspectionTimeReductionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
