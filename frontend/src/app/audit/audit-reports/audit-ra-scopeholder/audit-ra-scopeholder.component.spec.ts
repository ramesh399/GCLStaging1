import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditRaScopeholderComponent } from './audit-ra-scopeholder.component';

describe('AuditRaScopeholderComponent', () => {
  let component: AuditRaScopeholderComponent;
  let fixture: ComponentFixture<AuditRaScopeholderComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditRaScopeholderComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditRaScopeholderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
