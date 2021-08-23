import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddAuditExecutionChecklistComponent } from './add-audit-execution-checklist.component';

describe('AddAuditExecutionChecklistComponent', () => {
  let component: AddAuditExecutionChecklistComponent;
  let fixture: ComponentFixture<AddAuditExecutionChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddAuditExecutionChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddAuditExecutionChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
