import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddItCenterComponent } from './add-it-center.component';

describe('AddItCenterComponent', () => {
  let component: AddItCenterComponent;
  let fixture: ComponentFixture<AddItCenterComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddItCenterComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddItCenterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
