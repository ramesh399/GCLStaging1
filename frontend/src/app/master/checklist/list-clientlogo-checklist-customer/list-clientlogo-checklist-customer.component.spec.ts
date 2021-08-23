import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListClientlogoChecklistCustomerComponent } from './list-clientlogo-checklist-customer.component';

describe('ListClientlogoChecklistCustomerComponent', () => {
  let component: ListClientlogoChecklistCustomerComponent;
  let fixture: ComponentFixture<ListClientlogoChecklistCustomerComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListClientlogoChecklistCustomerComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListClientlogoChecklistCustomerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
