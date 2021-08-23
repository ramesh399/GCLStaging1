import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListBrandGroupComponent } from './list-brand-group.component';

describe('ListBrandGroupComponent', () => {
  let component: ListBrandGroupComponent;
  let fixture: ComponentFixture<ListBrandGroupComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListBrandGroupComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListBrandGroupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
