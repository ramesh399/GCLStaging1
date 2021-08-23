import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListProducttypeComponent } from './list-producttype.component';

describe('ListProducttypeComponent', () => {
  let component: ListProducttypeComponent;
  let fixture: ComponentFixture<ListProducttypeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListProducttypeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListProducttypeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
