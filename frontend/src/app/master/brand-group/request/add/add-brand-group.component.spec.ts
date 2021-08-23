import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddBrandGroupComponent } from './add-brand-group.component';

describe('AddBrandGroupComponent', () => {
  let component: AddBrandGroupComponent;
  let fixture: ComponentFixture<AddBrandGroupComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddBrandGroupComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddBrandGroupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
