import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ApplicationProductDetailListComponent } from './application-product-detail-list.component';

describe('ApplicationProductDetailListComponent', () => {
  let component: ApplicationProductDetailListComponent;
  let fixture: ComponentFixture<ApplicationProductDetailListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ApplicationProductDetailListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ApplicationProductDetailListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
