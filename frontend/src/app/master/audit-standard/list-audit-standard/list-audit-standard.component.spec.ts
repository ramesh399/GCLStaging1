import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListAuditStandardComponent } from './list-audit-standard.component';

describe('ListAuditStandardComponent', () => {
  let component: ListAuditStandardComponent;
  let fixture: ComponentFixture<ListAuditStandardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListAuditStandardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListAuditStandardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
