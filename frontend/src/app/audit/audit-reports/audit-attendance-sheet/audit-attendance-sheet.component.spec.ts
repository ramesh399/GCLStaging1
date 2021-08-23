import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditAttendanceSheetComponent } from './audit-attendance-sheet.component';

describe('AuditAttendanceSheetComponent', () => {
  let component: AuditAttendanceSheetComponent;
  let fixture: ComponentFixture<AuditAttendanceSheetComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditAttendanceSheetComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditAttendanceSheetComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
