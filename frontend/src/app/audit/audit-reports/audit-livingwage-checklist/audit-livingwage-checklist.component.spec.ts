import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditLivingwageChecklistComponent } from './audit-livingwage-checklist.component';

describe('AuditLivingwageChecklistComponent', () => {
  let component: AuditLivingwageChecklistComponent;
  let fixture: ComponentFixture<AuditLivingwageChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditLivingwageChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditLivingwageChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
