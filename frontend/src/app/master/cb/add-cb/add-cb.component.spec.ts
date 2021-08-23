import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddCbComponent } from './add-cb.component';

describe('AddCbComponent', () => {
  let component: AddCbComponent;
  let fixture: ComponentFixture<AddCbComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddCbComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddCbComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
