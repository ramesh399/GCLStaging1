import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListSubTopicComponent } from './list-sub-topic.component';

describe('ListSubTopicComponent', () => {
  let component: ListSubTopicComponent;
  let fixture: ComponentFixture<ListSubTopicComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListSubTopicComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListSubTopicComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
