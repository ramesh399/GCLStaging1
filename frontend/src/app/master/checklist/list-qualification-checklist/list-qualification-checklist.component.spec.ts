import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListQualificationChecklistComponent } from './list-qualification-checklist.component';

describe('ListQualificationChecklistComponent', () => {
  let component: ListQualificationChecklistComponent;
  let fixture: ComponentFixture<ListQualificationChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListQualificationChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListQualificationChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
