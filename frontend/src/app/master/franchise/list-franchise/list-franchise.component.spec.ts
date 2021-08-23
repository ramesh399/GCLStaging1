import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListFranchiseComponent } from './list-franchise.component';

describe('ListFranchiseComponent', () => {
  let component: ListFranchiseComponent;
  let fixture: ComponentFixture<ListFranchiseComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListFranchiseComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListFranchiseComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
