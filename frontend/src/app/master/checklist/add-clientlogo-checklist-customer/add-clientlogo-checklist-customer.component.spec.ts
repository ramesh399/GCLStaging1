import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddClientlogoChecklistCustomerComponent } from './add-clientlogo-checklist-customer.component';

describe('AddClientlogoChecklistCustomerComponent', () => {
  let component: AddClientlogoChecklistCustomerComponent;
  let fixture: ComponentFixture<AddClientlogoChecklistCustomerComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddClientlogoChecklistCustomerComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddClientlogoChecklistCustomerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
