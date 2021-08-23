import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewChangeAddressComponent } from './view-change-address.component';

describe('ViewChangeAddressComponent', () => {
  let component: ViewChangeAddressComponent;
  let fixture: ComponentFixture<ViewChangeAddressComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewChangeAddressComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewChangeAddressComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
