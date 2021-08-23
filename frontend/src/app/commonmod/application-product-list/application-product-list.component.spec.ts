import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ApplicationProductListComponent } from './application-product-list.component';

describe('ApplicationProductListComponent', () => {
  let component: ApplicationProductListComponent;
  let fixture: ComponentFixture<ApplicationProductListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ApplicationProductListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ApplicationProductListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
