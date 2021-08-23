import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditAuditExecutionChecklistComponent } from './edit-audit-execution-checklist.component';

describe('EditAuditExecutionChecklistComponent', () => {
  let component: EditAuditExecutionChecklistComponent;
  let fixture: ComponentFixture<EditAuditExecutionChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditAuditExecutionChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditAuditExecutionChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
