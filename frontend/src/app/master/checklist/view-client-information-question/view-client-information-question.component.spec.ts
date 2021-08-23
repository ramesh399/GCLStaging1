import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewClientInformationQuestionComponent } from './view-client-information-question.component';

describe('ViewClientInformationQuestionComponent', () => {
  let component: ViewClientInformationQuestionComponent;
  let fixture: ComponentFixture<ViewClientInformationQuestionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewClientInformationQuestionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewClientInformationQuestionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
