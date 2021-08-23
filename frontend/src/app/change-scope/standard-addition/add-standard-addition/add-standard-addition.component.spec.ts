import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddStandardAdditionComponent } from './add-standard-addition.component';

describe('AddStandardAdditionComponent', () => {
  let component: AddStandardAdditionComponent;
  let fixture: ComponentFixture<AddStandardAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddStandardAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddStandardAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
