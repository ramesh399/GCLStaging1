import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditSamplingComponent } from './audit-sampling.component';

describe('AuditSamplingComponent', () => {
  let component: AuditSamplingComponent;
  let fixture: ComponentFixture<AuditSamplingComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditSamplingComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditSamplingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
