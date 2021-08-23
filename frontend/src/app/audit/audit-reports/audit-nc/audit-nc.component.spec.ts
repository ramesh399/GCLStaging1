import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditNcComponent } from './audit-nc.component';

describe('AuditNcComponent', () => {
  let component: AuditNcComponent;
  let fixture: ComponentFixture<AuditNcComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditNcComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditNcComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
