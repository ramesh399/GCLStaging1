import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RequestProductAdditionComponent } from './request-product-addition.component';

describe('RequestProductAdditionComponent', () => {
  let component: RequestProductAdditionComponent;
  let fixture: ComponentFixture<RequestProductAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RequestProductAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RequestProductAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
