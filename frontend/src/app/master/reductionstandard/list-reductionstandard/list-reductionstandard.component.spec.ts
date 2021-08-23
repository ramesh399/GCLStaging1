import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListReductionstandardComponent } from './list-reductionstandard.component';

describe('ListReductionstandardComponent', () => {
  let component: ListReductionstandardComponent;
  let fixture: ComponentFixture<ListReductionstandardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListReductionstandardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListReductionstandardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
