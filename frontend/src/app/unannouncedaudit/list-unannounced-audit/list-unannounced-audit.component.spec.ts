import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListUnannouncedAuditComponent } from './list-unannounced-audit.component';

describe('ListUnannouncedAuditComponent', () => {
  let component: ListUnannouncedAuditComponent;
  let fixture: ComponentFixture<ListUnannouncedAuditComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListUnannouncedAuditComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListUnannouncedAuditComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
