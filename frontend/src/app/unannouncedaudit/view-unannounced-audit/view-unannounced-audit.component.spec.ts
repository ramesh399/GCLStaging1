import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewUnannouncedAuditComponent } from './view-unannounced-audit.component';

describe('ViewUnannouncedAuditComponent', () => {
  let component: ViewUnannouncedAuditComponent;
  let fixture: ComponentFixture<ViewUnannouncedAuditComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewUnannouncedAuditComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewUnannouncedAuditComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
