import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditLivingwageViewchecklistComponent } from './audit-livingwage-viewchecklist.component';

describe('AuditLivingwageViewchecklistComponent', () => {
  let component: AuditLivingwageViewchecklistComponent;
  let fixture: ComponentFixture<AuditLivingwageViewchecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditLivingwageViewchecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditLivingwageViewchecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
