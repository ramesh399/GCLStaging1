import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditClientinformationProductcontrolsComponent } from './audit-clientinformation-productcontrols.component';

describe('AuditClientinformationProductcontrolsComponent', () => {
  let component: AuditClientinformationProductcontrolsComponent;
  let fixture: ComponentFixture<AuditClientinformationProductcontrolsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditClientinformationProductcontrolsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditClientinformationProductcontrolsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
