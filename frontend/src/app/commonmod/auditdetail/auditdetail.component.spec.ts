import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditdetailComponent } from './auditdetail.component';

describe('AuditdetailComponent', () => {
  let component: AuditdetailComponent;
  let fixture: ComponentFixture<AuditdetailComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditdetailComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditdetailComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
