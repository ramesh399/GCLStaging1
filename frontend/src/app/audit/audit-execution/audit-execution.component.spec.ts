import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditExecutionComponent } from './audit-execution.component';

describe('AuditExecutionComponent', () => {
  let component: AuditExecutionComponent;
  let fixture: ComponentFixture<AuditExecutionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditExecutionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditExecutionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
