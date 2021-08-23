import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListChangeAddressComponent } from './list-change-address.component';

describe('ListChangeAddressComponent', () => {
  let component: ListChangeAddressComponent;
  let fixture: ComponentFixture<ListChangeAddressComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListChangeAddressComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListChangeAddressComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
