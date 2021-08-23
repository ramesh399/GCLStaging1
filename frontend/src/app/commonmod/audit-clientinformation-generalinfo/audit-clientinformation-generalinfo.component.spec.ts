import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditClientinformationGeneralinfoComponent } from './audit-clientinformation-generalinfo.component';

describe('AuditClientinformationGeneralinfoComponent', () => {
  let component: AuditClientinformationGeneralinfoComponent;
  let fixture: ComponentFixture<AuditClientinformationGeneralinfoComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditClientinformationGeneralinfoComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditClientinformationGeneralinfoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
