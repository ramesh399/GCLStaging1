import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { InspectiontimeComponent } from './inspectiontime.component';

describe('InspectiontimeComponent', () => {
  let component: InspectiontimeComponent;
  let fixture: ComponentFixture<InspectiontimeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ InspectiontimeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(InspectiontimeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
