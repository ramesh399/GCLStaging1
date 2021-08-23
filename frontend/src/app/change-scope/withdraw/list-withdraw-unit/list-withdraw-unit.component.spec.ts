import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListWithdrawUnitComponent } from './list-withdraw-unit.component';

describe('ListWithdrawUnitComponent', () => {
  let component: ListWithdrawUnitComponent;
  let fixture: ComponentFixture<ListWithdrawUnitComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListWithdrawUnitComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListWithdrawUnitComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
