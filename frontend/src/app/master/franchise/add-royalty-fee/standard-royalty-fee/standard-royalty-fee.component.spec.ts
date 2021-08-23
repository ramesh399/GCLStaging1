import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { StandardRoyaltyFeeComponent } from './standard-royalty-fee.component';

describe('StandardRoyaltyFeeComponent', () => {
  let component: StandardRoyaltyFeeComponent;
  let fixture: ComponentFixture<StandardRoyaltyFeeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ StandardRoyaltyFeeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(StandardRoyaltyFeeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
