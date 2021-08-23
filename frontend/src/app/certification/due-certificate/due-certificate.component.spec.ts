import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DueCertificateComponent } from './due-certificate.component';

describe('DueCertificateComponent', () => {
  let component: DueCertificateComponent;
  let fixture: ComponentFixture<DueCertificateComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DueCertificateComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DueCertificateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
