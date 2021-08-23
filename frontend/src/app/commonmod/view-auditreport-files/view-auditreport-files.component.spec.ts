import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewAuditreportFilesComponent } from './view-auditreport-files.component';

describe('ViewAuditreportFilesComponent', () => {
  let component: ViewAuditreportFilesComponent;
  let fixture: ComponentFixture<ViewAuditreportFilesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewAuditreportFilesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewAuditreportFilesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
