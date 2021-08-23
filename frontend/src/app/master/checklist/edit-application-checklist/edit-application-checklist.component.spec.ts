import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditApplicationChecklistComponent } from './edit-application-checklist.component';

describe('EditApplicationChecklistComponent', () => {
  let component: EditApplicationChecklistComponent;
  let fixture: ComponentFixture<EditApplicationChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditApplicationChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditApplicationChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
