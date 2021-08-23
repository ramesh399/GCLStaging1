import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewStandardreductionComponent } from './view-standardreduction.component';

describe('ViewStandardreductionComponent', () => {
  let component: ViewStandardreductionComponent;
  let fixture: ComponentFixture<ViewStandardreductionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewStandardreductionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewStandardreductionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
