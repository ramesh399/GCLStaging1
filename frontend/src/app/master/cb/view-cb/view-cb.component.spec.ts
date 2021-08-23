import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewCbComponent } from './view-cb.component';

describe('ViewCbComponent', () => {
  let component: ViewCbComponent;
  let fixture: ComponentFixture<ViewCbComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewCbComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewCbComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
