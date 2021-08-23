import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditStandardComponent } from './audit-standard.component';

describe('AuditStandardComponent', () => {
  let component: AuditStandardComponent;
  let fixture: ComponentFixture<AuditStandardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditStandardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditStandardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
