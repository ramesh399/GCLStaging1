import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { InspectionBodyComponent } from './inspection-body.component';

describe('InspectionBodyComponent', () => {
  let component: InspectionBodyComponent;
  let fixture: ComponentFixture<InspectionBodyComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ InspectionBodyComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(InspectionBodyComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
