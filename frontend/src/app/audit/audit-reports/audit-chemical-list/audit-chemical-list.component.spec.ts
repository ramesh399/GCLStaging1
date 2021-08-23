import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuditChemicalListComponent } from './audit-chemical-list.component';

describe('AuditChemicalListComponent', () => {
  let component: AuditChemicalListComponent;
  let fixture: ComponentFixture<AuditChemicalListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuditChemicalListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuditChemicalListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
