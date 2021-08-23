import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewOffertemplateComponent } from './view-offertemplate.component';

describe('ViewOffertemplateComponent', () => {
  let component: ViewOffertemplateComponent;
  let fixture: ComponentFixture<ViewOffertemplateComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewOffertemplateComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewOffertemplateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
