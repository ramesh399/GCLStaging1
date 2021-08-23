import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditChangeAddressComponent } from './edit-change-address.component';

describe('EditChangeAddressComponent', () => {
  let component: EditChangeAddressComponent;
  let fixture: ComponentFixture<EditChangeAddressComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditChangeAddressComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditChangeAddressComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
