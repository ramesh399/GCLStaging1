import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditSubTopicComponent } from './edit-sub-topic.component';

describe('EditSubTopicComponent', () => {
  let component: EditSubTopicComponent;
  let fixture: ComponentFixture<EditSubTopicComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditSubTopicComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditSubTopicComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
