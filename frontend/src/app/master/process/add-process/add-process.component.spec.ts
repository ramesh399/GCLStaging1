import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddProcessComponent } from './add-process.component';

describe('AddProcessComponent', () => {
  let component: AddProcessComponent;
  let fixture: ComponentFixture<AddProcessComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddProcessComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddProcessComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
