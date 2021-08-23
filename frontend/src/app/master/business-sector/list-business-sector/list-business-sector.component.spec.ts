import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListBusinessSectorComponent } from './list-business-sector.component';

describe('ListBusinessSectorComponent', () => {
  let component: ListBusinessSectorComponent;
  let fixture: ComponentFixture<ListBusinessSectorComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListBusinessSectorComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListBusinessSectorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
