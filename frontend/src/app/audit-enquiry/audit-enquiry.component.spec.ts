import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditEnquiryComponent } from './audit-enquiry.component';

describe('AuditEnquiryComponent', () => {
  let component: AuditEnquiryComponent;
  let fixture: ComponentFixture<AuditEnquiryComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditEnquiryComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditEnquiryComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
