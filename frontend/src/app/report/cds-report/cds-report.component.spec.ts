import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CdsReportComponent } from './cds-report.component';

describe('CdsReportComponent', () => {
  let component: CdsReportComponent;
  let fixture: ComponentFixture<CdsReportComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CdsReportComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CdsReportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
