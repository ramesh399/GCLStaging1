import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewFranchiseComponent } from './view-franchise.component';

describe('ViewFranchiseComponent', () => {
  let component: ViewFranchiseComponent;
  let fixture: ComponentFixture<ViewFranchiseComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewFranchiseComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewFranchiseComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
