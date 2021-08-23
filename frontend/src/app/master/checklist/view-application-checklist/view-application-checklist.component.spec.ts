import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewApplicationChecklistComponent } from './view-application-checklist.component';

describe('ViewApplicationChecklistComponent', () => {
  let component: ViewApplicationChecklistComponent;
  let fixture: ComponentFixture<ViewApplicationChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewApplicationChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewApplicationChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
