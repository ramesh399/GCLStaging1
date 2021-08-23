import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ValidateCertifiedStandardComponent } from './validate-certified-standard.component';

describe('ValidateCertifiedStandardComponent', () => {
  let component: ValidateCertifiedStandardComponent;
  let fixture: ComponentFixture<ValidateCertifiedStandardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ValidateCertifiedStandardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ValidateCertifiedStandardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
