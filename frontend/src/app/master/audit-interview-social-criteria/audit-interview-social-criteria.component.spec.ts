import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditInterviewSocialCriteriaComponent } from './audit-interview-social-criteria.component';

describe('AuditInterviewSocialCriteriaComponent', () => {
  let component: AuditInterviewSocialCriteriaComponent;
  let fixture: ComponentFixture<AuditInterviewSocialCriteriaComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditInterviewSocialCriteriaComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditInterviewSocialCriteriaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
