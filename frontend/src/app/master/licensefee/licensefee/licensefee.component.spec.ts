import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { LicensefeeComponent } from './licensefee.component';

describe('LicensefeeComponent', () => {
  let component: LicensefeeComponent;
  let fixture: ComponentFixture<LicensefeeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ LicensefeeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(LicensefeeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
