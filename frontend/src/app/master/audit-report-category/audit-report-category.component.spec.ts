import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditReportCategoryComponent } from './audit-report-category.component';

describe('AuditReportCategoryComponent', () => {
  let component: AuditReportCategoryComponent;
  let fixture: ComponentFixture<AuditReportCategoryComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditReportCategoryComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditReportCategoryComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
