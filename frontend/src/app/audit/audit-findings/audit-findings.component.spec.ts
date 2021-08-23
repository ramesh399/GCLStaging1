import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditFindingsComponent } from './audit-findings.component';

describe('AuditFindingsComponent', () => {
  let component: AuditFindingsComponent;
  let fixture: ComponentFixture<AuditFindingsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditFindingsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditFindingsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
