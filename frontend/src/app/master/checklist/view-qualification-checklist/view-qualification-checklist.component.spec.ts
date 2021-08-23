import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewQualificationChecklistComponent } from './view-qualification-checklist.component';

describe('ViewQualificationChecklistComponent', () => {
  let component: ViewQualificationChecklistComponent;
  let fixture: ComponentFixture<ViewQualificationChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewQualificationChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewQualificationChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
