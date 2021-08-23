import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CertificateGenerationListComponent } from './certificate-generation-list.component';

describe('CertificateGenerationListComponent', () => {
  let component: CertificateGenerationListComponent;
  let fixture: ComponentFixture<CertificateGenerationListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CertificateGenerationListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CertificateGenerationListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
