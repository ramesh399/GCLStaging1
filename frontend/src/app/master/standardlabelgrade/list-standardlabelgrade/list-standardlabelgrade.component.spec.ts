import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListStandardlabelgradeComponent } from './list-standardlabelgrade.component';

describe('ListStandardlabelgradeComponent', () => {
  let component: ListStandardlabelgradeComponent;
  let fixture: ComponentFixture<ListStandardlabelgradeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListStandardlabelgradeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListStandardlabelgradeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
