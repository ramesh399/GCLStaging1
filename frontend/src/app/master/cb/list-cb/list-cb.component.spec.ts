import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListCbComponent } from './list-cb.component';

describe('ListCbComponent', () => {
  let component: ListCbComponent;
  let fixture: ComponentFixture<ListCbComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListCbComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListCbComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
