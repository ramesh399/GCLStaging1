import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListStateComponent } from './list-state.component';

describe('ListStateComponent', () => {
  let component: ListStateComponent;
  let fixture: ComponentFixture<ListStateComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListStateComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListStateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
