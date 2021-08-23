import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListUserroleComponent } from './list-userrole.component';

describe('ListUserroleComponent', () => {
  let component: ListUserroleComponent;
  let fixture: ComponentFixture<ListUserroleComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListUserroleComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListUserroleComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
