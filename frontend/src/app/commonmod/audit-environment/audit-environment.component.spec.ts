import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditEnvironmentComponent } from './audit-environment.component';

describe('AuditEnvironmentComponent', () => {
  let component: AuditEnvironmentComponent;
  let fixture: ComponentFixture<AuditEnvironmentComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditEnvironmentComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditEnvironmentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
