import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditFindingsRemediationComponent } from './audit-findings-remediation.component';

describe('AuditFindingsRemediationComponent', () => {
  let component: AuditFindingsRemediationComponent;
  let fixture: ComponentFixture<AuditFindingsRemediationComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditFindingsRemediationComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditFindingsRemediationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
