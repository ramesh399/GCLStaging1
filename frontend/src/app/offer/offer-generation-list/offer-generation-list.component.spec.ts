import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OfferGenerationListComponent } from './offer-generation-list.component';

describe('OfferGenerationListComponent', () => {
  let component: OfferGenerationListComponent;
  let fixture: ComponentFixture<OfferGenerationListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OfferGenerationListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OfferGenerationListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
