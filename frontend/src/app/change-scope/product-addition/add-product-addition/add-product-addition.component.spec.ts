import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddProductAdditionComponent } from './add-product-addition.component';

describe('AddProductAdditionComponent', () => {
  let component: AddProductAdditionComponent;
  let fixture: ComponentFixture<AddProductAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddProductAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddProductAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
