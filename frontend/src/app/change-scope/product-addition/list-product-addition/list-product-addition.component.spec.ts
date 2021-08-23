import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListProductAdditionComponent } from './list-product-addition.component';

describe('ListProductAdditionComponent', () => {
  let component: ListProductAdditionComponent;
  let fixture: ComponentFixture<ListProductAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListProductAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListProductAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
