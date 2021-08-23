import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListClientInformationQuestionComponent } from './list-client-information-question.component';

describe('ListClientInformationQuestionComponent', () => {
  let component: ListClientInformationQuestionComponent;
  let fixture: ComponentFixture<ListClientInformationQuestionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListClientInformationQuestionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListClientInformationQuestionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
