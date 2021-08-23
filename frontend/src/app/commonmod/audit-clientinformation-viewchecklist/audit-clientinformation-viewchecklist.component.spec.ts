import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditClientinformationViewchecklistComponent } from './audit-clientinformation-viewchecklist.component';

describe('AuditClientinformationViewchecklistComponent', () => {
  let component: AuditClientinformationViewchecklistComponent;
  let fixture: ComponentFixture<AuditClientinformationViewchecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditClientinformationViewchecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditClientinformationViewchecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
