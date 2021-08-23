import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddStandardreductionComponent } from './add-standardreduction.component';

describe('AddStandardreductionComponent', () => {
  let component: AddStandardreductionComponent;
  let fixture: ComponentFixture<AddStandardreductionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddStandardreductionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddStandardreductionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
