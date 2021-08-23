import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ReviewerPerformanceReportComponent } from './reviewer-performance-report.component';

describe('ReviewerPerformanceReportComponent', () => {
  let component: ReviewerPerformanceReportComponent;
  let fixture: ComponentFixture<ReviewerPerformanceReportComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ReviewerPerformanceReportComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ReviewerPerformanceReportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
