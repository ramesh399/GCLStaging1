import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListTranslatorComponent } from './list-translator.component';

describe('ListTranslatorComponent', () => {
  let component: ListTranslatorComponent;
  let fixture: ComponentFixture<ListTranslatorComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListTranslatorComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListTranslatorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
