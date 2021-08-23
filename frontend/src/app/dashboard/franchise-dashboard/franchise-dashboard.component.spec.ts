import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { FranchiseDashboardComponent } from './franchise-dashboard.component';

describe('FranchiseDashboardComponent', () => {
  let component: FranchiseDashboardComponent;
  let fixture: ComponentFixture<FranchiseDashboardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ FranchiseDashboardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FranchiseDashboardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
