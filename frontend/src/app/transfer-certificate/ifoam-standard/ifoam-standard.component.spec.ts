import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { IfoamStandardComponent } from './ifoam-standard.component';

describe('IfoamStandardComponent', () => {
  let component: IfoamStandardComponent;
  let fixture: ComponentFixture<IfoamStandardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ IfoamStandardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(IfoamStandardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
