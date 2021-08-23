import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListStandardComponent } from './list-standard.component';

describe('ListStandardComponent', () => {
  let component: ListStandardComponent;
  let fixture: ComponentFixture<ListStandardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListStandardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListStandardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
