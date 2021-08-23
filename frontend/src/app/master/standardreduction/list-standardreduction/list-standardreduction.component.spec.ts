import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListStandardreductionComponent } from './list-standardreduction.component';

describe('ListStandardreductionComponent', () => {
  let component: ListStandardreductionComponent;
  let fixture: ComponentFixture<ListStandardreductionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListStandardreductionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListStandardreductionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
