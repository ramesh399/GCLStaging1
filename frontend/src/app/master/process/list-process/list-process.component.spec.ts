import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListProcessComponent } from './list-process.component';

describe('ListProcessComponent', () => {
  let component: ListProcessComponent;
  let fixture: ComponentFixture<ListProcessComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListProcessComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListProcessComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
