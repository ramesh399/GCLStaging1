import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ProductadditiondetailComponent } from './productadditiondetail.component';

describe('ProductadditiondetailComponent', () => {
  let component: ProductadditiondetailComponent;
  let fixture: ComponentFixture<ProductadditiondetailComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ProductadditiondetailComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ProductadditiondetailComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
