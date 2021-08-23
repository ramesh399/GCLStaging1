import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { InvoiceGenerateComponent } from './invoice-generate.component';

describe('InvoiceGenerateComponent', () => {
  let component: InvoiceGenerateComponent;
  let fixture: ComponentFixture<InvoiceGenerateComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ InvoiceGenerateComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(InvoiceGenerateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
