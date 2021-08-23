import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewSubTopicComponent } from './view-sub-topic.component';

describe('ViewSubTopicComponent', () => {
  let component: ViewSubTopicComponent;
  let fixture: ComponentFixture<ViewSubTopicComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewSubTopicComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewSubTopicComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
