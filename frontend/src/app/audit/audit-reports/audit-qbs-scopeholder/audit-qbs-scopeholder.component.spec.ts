import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditQbsScopeholderComponent } from './audit-qbs-scopeholder.component';

describe('AuditQbsScopeholderComponent', () => {
  let component: AuditQbsScopeholderComponent;
  let fixture: ComponentFixture<AuditQbsScopeholderComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditQbsScopeholderComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditQbsScopeholderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
