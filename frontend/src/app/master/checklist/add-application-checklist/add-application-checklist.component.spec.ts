import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddApplicationChecklistComponent } from './add-application-checklist.component';

describe('AddApplicationChecklistComponent', () => {
  let component: AddApplicationChecklistComponent;
  let fixture: ComponentFixture<AddApplicationChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddApplicationChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddApplicationChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
