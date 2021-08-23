import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditClientinformationChecklistComponent } from './audit-clientinformation-checklist.component';

describe('AuditClientinformationChecklistComponent', () => {
  let component: AuditClientinformationChecklistComponent;
  let fixture: ComponentFixture<AuditClientinformationChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditClientinformationChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditClientinformationChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
