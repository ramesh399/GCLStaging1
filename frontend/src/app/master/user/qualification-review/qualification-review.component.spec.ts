import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { QualificationReviewComponent } from './qualification-review.component';

describe('QualificationReviewComponent', () => {
  let component: QualificationReviewComponent;
  let fixture: ComponentFixture<QualificationReviewComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ QualificationReviewComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(QualificationReviewComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
