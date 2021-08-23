import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EnquiryHeaderComponent } from './enquiry-header.component';

describe('EnquiryHeaderComponent', () => {
  let component: EnquiryHeaderComponent;
  let fixture: ComponentFixture<EnquiryHeaderComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EnquiryHeaderComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EnquiryHeaderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
