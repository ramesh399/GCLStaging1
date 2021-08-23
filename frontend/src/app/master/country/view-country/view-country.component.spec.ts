import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewCountryComponent } from './view-country.component';

describe('ViewCountryComponent', () => {
  let component: ViewCountryComponent;
  let fixture: ComponentFixture<ViewCountryComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewCountryComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewCountryComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
