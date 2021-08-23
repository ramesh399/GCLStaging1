import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { NcReportComponent } from './nc-report.component';

describe('NcReportComponent', () => {
  let component: NcReportComponent;
  let fixture: ComponentFixture<NcReportComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ NcReportComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(NcReportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
