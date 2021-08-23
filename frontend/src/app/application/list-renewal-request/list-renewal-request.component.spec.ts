import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListRenewalRequestComponent } from './list-renewal-request.component';

describe('ListRenewalRequestComponent', () => {
  let component: ListRenewalRequestComponent;
  let fixture: ComponentFixture<ListRenewalRequestComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListRenewalRequestComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListRenewalRequestComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
