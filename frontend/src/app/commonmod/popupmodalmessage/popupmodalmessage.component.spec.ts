import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PopupmodalmessageComponent } from './popupmodalmessage.component';

describe('PopupmodalmessageComponent', () => {
  let component: PopupmodalmessageComponent;
  let fixture: ComponentFixture<PopupmodalmessageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PopupmodalmessageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PopupmodalmessageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
