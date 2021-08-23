import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditExecutionSeverityComponent } from './audit-execution-severity.component';

describe('AuditExecutionSeverityComponent', () => {
  let component: AuditExecutionSeverityComponent;
  let fixture: ComponentFixture<AuditExecutionSeverityComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditExecutionSeverityComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditExecutionSeverityComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
