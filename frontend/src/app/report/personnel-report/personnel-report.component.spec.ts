import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PersonnelReportComponent } from './personnel-report.component';

describe('PersonnelReportComponent', () => {
  let component: PersonnelReportComponent;
  let fixture: ComponentFixture<PersonnelReportComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PersonnelReportComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PersonnelReportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
