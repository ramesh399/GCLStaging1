import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListUnitAdditionComponent } from './list-unit-addition.component';

describe('ListUnitAdditionComponent', () => {
  let component: ListUnitAdditionComponent;
  let fixture: ComponentFixture<ListUnitAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListUnitAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListUnitAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
