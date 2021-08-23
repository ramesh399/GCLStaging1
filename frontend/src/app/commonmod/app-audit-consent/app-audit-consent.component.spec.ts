import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AppAuditConsentComponent } from './app-audit-consent.component';

describe('AppAuditConsentComponent', () => {
  let component: AppAuditConsentComponent;
  let fixture: ComponentFixture<AppAuditConsentComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AppAuditConsentComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AppAuditConsentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
