import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddClientInformationQuestionComponent } from './add-client-information-question.component';

describe('AddClientInformationQuestionComponent', () => {
  let component: AddClientInformationQuestionComponent;
  let fixture: ComponentFixture<AddClientInformationQuestionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddClientInformationQuestionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddClientInformationQuestionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
