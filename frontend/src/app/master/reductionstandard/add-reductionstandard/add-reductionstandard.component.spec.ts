import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddReductionstandardComponent } from './add-reductionstandard.component';

describe('AddReductionstandardComponent', () => {
  let component: AddReductionstandardComponent;
  let fixture: ComponentFixture<AddReductionstandardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddReductionstandardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddReductionstandardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
