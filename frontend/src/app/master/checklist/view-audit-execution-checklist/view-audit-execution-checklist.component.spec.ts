import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewAuditExecutionChecklistComponent } from './view-audit-execution-checklist.component';

describe('ViewAuditExecutionChecklistComponent', () => {
  let component: ViewAuditExecutionChecklistComponent;
  let fixture: ComponentFixture<ViewAuditExecutionChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewAuditExecutionChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewAuditExecutionChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
