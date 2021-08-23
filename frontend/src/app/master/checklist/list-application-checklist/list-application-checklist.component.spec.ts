import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListApplicationChecklistComponent } from './list-application-checklist.component';

describe('ListApplicationChecklistComponent', () => {
  let component: ListApplicationChecklistComponent;
  let fixture: ComponentFixture<ListApplicationChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListApplicationChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListApplicationChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
