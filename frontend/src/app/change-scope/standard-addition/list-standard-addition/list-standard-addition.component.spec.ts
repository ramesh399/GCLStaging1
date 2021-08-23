import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListStandardAdditionComponent } from './list-standard-addition.component';

describe('ListStandardAdditionComponent', () => {
  let component: ListStandardAdditionComponent;
  let fixture: ComponentFixture<ListStandardAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListStandardAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListStandardAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
