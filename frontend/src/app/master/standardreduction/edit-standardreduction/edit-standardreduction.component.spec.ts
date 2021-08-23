import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditStandardreductionComponent } from './edit-standardreduction.component';

describe('EditStandardreductionComponent', () => {
  let component: EditStandardreductionComponent;
  let fixture: ComponentFixture<EditStandardreductionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditStandardreductionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditStandardreductionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
