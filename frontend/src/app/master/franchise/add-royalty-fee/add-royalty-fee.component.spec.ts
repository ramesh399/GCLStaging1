import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddRoyaltyFeeComponent } from './add-royalty-fee.component';

describe('AddRoyaltyFeeComponent', () => {
  let component: AddRoyaltyFeeComponent;
  let fixture: ComponentFixture<AddRoyaltyFeeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddRoyaltyFeeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddRoyaltyFeeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
