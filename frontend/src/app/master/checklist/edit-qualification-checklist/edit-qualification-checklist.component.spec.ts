import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditQualificationChecklistComponent } from './edit-qualification-checklist.component';

describe('EditQualificationChecklistComponent', () => {
  let component: EditQualificationChecklistComponent;
  let fixture: ComponentFixture<EditQualificationChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditQualificationChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditQualificationChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
