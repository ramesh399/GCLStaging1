import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TcReportComponent } from './tc-report.component';

describe('TcReportComponent', () => {
  let component: TcReportComponent;
  let fixture: ComponentFixture<TcReportComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TcReportComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TcReportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
