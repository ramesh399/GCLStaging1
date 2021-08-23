import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditClientlogoChecklistCustomerComponent } from './edit-clientlogo-checklist-customer.component';

describe('EditClientlogoChecklistCustomerComponent', () => {
  let component: EditClientlogoChecklistCustomerComponent;
  let fixture: ComponentFixture<EditClientlogoChecklistCustomerComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditClientlogoChecklistCustomerComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditClientlogoChecklistCustomerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
