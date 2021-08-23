import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddQualificationChecklistComponent } from './add-qualification-checklist.component';

describe('AddQualificationChecklistComponent', () => {
  let component: AddQualificationChecklistComponent;
  let fixture: ComponentFixture<AddQualificationChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddQualificationChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddQualificationChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
