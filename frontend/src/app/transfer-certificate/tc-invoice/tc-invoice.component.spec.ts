import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TcInvoiceComponent } from './tc-invoice.component';

describe('TcInvoiceComponent', () => {
  let component: TcInvoiceComponent;
  let fixture: ComponentFixture<TcInvoiceComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TcInvoiceComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TcInvoiceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
