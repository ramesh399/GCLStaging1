import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewUnitAdditionComponent } from './view-unit-addition.component';

describe('ViewUnitAdditionComponent', () => {
  let component: ViewUnitAdditionComponent;
  let fixture: ComponentFixture<ViewUnitAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewUnitAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewUnitAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
