import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewClientlogoChecklistCustomerComponent } from './view-clientlogo-checklist-customer.component';

describe('ViewClientlogoChecklistCustomerComponent', () => {
  let component: ViewClientlogoChecklistCustomerComponent;
  let fixture: ComponentFixture<ViewClientlogoChecklistCustomerComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewClientlogoChecklistCustomerComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewClientlogoChecklistCustomerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
