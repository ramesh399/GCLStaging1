import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddSubTopicComponent } from './add-sub-topic.component';

describe('AddSubTopicComponent', () => {
  let component: AddSubTopicComponent;
  let fixture: ComponentFixture<AddSubTopicComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddSubTopicComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddSubTopicComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
