import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListBusinessSectorGroupComponent } from './list-business-sector-group.component';

describe('ListBusinessSectorGroupComponent', () => {
  let component: ListBusinessSectorGroupComponent;
  let fixture: ComponentFixture<ListBusinessSectorGroupComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListBusinessSectorGroupComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListBusinessSectorGroupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
