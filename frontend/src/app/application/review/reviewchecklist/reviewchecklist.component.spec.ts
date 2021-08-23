import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ReviewchecklistComponent } from './reviewchecklist.component';

describe('ReviewchecklistComponent', () => {
  let component: ReviewchecklistComponent;
  let fixture: ComponentFixture<ReviewchecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ReviewchecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ReviewchecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
