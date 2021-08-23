import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListItCenterComponent } from './list-it-center.component';

describe('ListItCenterComponent', () => {
  let component: ListItCenterComponent;
  let fixture: ComponentFixture<ListItCenterComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListItCenterComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListItCenterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
