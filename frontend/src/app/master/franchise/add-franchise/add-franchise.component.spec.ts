import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddFranchiseComponent } from './add-franchise.component';

describe('AddFranchiseComponent', () => {
  let component: AddFranchiseComponent;
  let fixture: ComponentFixture<AddFranchiseComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddFranchiseComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddFranchiseComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
