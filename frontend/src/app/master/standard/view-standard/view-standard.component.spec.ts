import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewStandardComponent } from './view-standard.component';

describe('ViewStandardComponent', () => {
  let component: ViewStandardComponent;
  let fixture: ComponentFixture<ViewStandardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewStandardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewStandardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
