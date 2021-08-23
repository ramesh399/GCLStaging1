import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditInterviewEmployeeComponent } from './audit-interview-employee.component';

describe('AuditInterviewEmployeeComponent', () => {
  let component: AuditInterviewEmployeeComponent;
  let fixture: ComponentFixture<AuditInterviewEmployeeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditInterviewEmployeeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditInterviewEmployeeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
