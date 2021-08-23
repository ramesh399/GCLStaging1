import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditProductAdditionComponent } from './edit-product-addition.component';

describe('EditProductAdditionComponent', () => {
  let component: EditProductAdditionComponent;
  let fixture: ComponentFixture<EditProductAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditProductAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditProductAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
