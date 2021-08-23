import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListMandaycostComponent } from './list-mandaycost.component';

describe('ListMandaycostComponent', () => {
  let component: ListMandaycostComponent;
  let fixture: ComponentFixture<ListMandaycostComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListMandaycostComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListMandaycostComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
