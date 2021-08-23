import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { GenerateOfferComponent } from './generate-offer.component';

describe('GenerateOfferComponent', () => {
  let component: GenerateOfferComponent;
  let fixture: ComponentFixture<GenerateOfferComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ GenerateOfferComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(GenerateOfferComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
