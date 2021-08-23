import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListRenewalAuditComponent } from './list-renewal-audit.component';

describe('ListRenewalAuditComponent', () => {
  let component: ListRenewalAuditComponent;
  let fixture: ComponentFixture<ListRenewalAuditComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListRenewalAuditComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListRenewalAuditComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
