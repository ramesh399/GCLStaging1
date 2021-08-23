import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewBrandGroupComponent } from './view-brand-group.component';

describe('ViewBrandGroupComponent', () => {
  let component: ViewBrandGroupComponent;
  let fixture: ComponentFixture<ViewBrandGroupComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewBrandGroupComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewBrandGroupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
