import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TcRoyaltyFeeComponent } from './tc-royalty-fee.component';

describe('TcRoyaltyFeeComponent', () => {
  let component: TcRoyaltyFeeComponent;
  let fixture: ComponentFixture<TcRoyaltyFeeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TcRoyaltyFeeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TcRoyaltyFeeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
