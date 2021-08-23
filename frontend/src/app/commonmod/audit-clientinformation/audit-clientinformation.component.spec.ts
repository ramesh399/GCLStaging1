import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditClientinformationComponent } from './audit-clientinformation.component';

describe('AuditClientinformationComponent', () => {
  let component: AuditClientinformationComponent;
  let fixture: ComponentFixture<AuditClientinformationComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditClientinformationComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditClientinformationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
