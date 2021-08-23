import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewBusinessSectorGroupComponent } from './view-business-sector-group.component';

describe('ViewBusinessSectorGroupComponent', () => {
  let component: ViewBusinessSectorGroupComponent;
  let fixture: ComponentFixture<ViewBusinessSectorGroupComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewBusinessSectorGroupComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewBusinessSectorGroupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
