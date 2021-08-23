import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditReductionstandardComponent } from './edit-reductionstandard.component';

describe('EditReductionstandardComponent', () => {
  let component: EditReductionstandardComponent;
  let fixture: ComponentFixture<EditReductionstandardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditReductionstandardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditReductionstandardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
