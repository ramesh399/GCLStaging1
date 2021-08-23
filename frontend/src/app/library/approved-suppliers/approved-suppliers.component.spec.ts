import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ApprovedSuppliersComponent } from './approved-suppliers.component';

describe('ApprovedSuppliersComponent', () => {
  let component: ApprovedSuppliersComponent;
  let fixture: ComponentFixture<ApprovedSuppliersComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ApprovedSuppliersComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ApprovedSuppliersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
