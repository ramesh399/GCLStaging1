import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ProductAdditionProductEditComponent } from './product-addition-product-edit.component';

describe('ProductAdditionProductEditComponent', () => {
  let component: ProductAdditionProductEditComponent;
  let fixture: ComponentFixture<ProductAdditionProductEditComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ProductAdditionProductEditComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ProductAdditionProductEditComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
