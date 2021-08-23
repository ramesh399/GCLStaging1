import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditInterviewViewchecklistComponent } from './audit-interview-viewchecklist.component';

describe('AuditInterviewViewchecklistComponent', () => {
  let component: AuditInterviewViewchecklistComponent;
  let fixture: ComponentFixture<AuditInterviewViewchecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditInterviewViewchecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditInterviewViewchecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
