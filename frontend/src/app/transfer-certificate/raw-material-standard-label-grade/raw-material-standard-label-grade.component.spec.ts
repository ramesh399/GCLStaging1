import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RawMaterialStandardLabelGradeComponent } from './raw-material-standard-label-grade.component';

describe('RawMaterialStandardLabelGradeComponent', () => {
  let component: RawMaterialStandardLabelGradeComponent;
  let fixture: ComponentFixture<RawMaterialStandardLabelGradeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RawMaterialStandardLabelGradeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RawMaterialStandardLabelGradeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
