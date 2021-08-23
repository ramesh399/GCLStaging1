import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { StandardReductionMaximumComponent } from './standard-reduction-maximum.component';

describe('StandardReductionMaximumComponent', () => {
  let component: StandardReductionMaximumComponent;
  let fixture: ComponentFixture<StandardReductionMaximumComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ StandardReductionMaximumComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(StandardReductionMaximumComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
