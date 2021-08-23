import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditClientinformationSupplierComponent } from './audit-clientinformation-supplier.component';

describe('AuditClientinformationSupplierComponent', () => {
  let component: AuditClientinformationSupplierComponent;
  let fixture: ComponentFixture<AuditClientinformationSupplierComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditClientinformationSupplierComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditClientinformationSupplierComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
