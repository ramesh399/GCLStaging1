import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddChangeAddressComponent } from './add-change-address.component';

describe('AddChangeAddressComponent', () => {
  let component: AddChangeAddressComponent;
  let fixture: ComponentFixture<AddChangeAddressComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddChangeAddressComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddChangeAddressComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
