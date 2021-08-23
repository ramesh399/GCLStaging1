import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListAuditExecutionChecklistComponent } from './list-audit-execution-checklist.component';

describe('ListAuditExecutionChecklistComponent', () => {
  let component: ListAuditExecutionChecklistComponent;
  let fixture: ComponentFixture<ListAuditExecutionChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListAuditExecutionChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListAuditExecutionChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
