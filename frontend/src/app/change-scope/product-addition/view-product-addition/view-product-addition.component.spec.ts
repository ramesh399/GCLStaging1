import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewProductAdditionComponent } from './view-product-addition.component';

describe('ViewProductAdditionComponent', () => {
  let component: ViewProductAdditionComponent;
  let fixture: ComponentFixture<ViewProductAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewProductAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewProductAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
