import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditClientInformationQuestionComponent } from './edit-client-information-question.component';

describe('EditClientInformationQuestionComponent', () => {
  let component: EditClientInformationQuestionComponent;
  let fixture: ComponentFixture<EditClientInformationQuestionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditClientInformationQuestionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditClientInformationQuestionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
