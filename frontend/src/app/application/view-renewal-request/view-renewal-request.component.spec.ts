import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewRenewalRequestComponent } from './view-renewal-request.component';

describe('ViewRenewalRequestComponent', () => {
  let component: ViewRenewalRequestComponent;
  let fixture: ComponentFixture<ViewRenewalRequestComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewRenewalRequestComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewRenewalRequestComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
