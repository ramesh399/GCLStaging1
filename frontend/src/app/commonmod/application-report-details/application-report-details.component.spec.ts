import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ApplicationReportDetailsComponent } from './application-report-details.component';

describe('ApplicationReportDetailsComponent', () => {
  let component: ApplicationReportDetailsComponent;
  let fixture: ComponentFixture<ApplicationReportDetailsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ApplicationReportDetailsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ApplicationReportDetailsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
