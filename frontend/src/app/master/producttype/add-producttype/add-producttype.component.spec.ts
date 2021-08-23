import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddProducttypeComponent } from './add-producttype.component';

describe('AddProducttypeComponent', () => {
  let component: AddProducttypeComponent;
  let fixture: ComponentFixture<AddProducttypeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddProducttypeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddProducttypeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
