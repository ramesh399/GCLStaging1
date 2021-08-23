import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewReductionstandardComponent } from './view-reductionstandard.component';

describe('ViewReductionstandardComponent', () => {
  let component: ViewReductionstandardComponent;
  let fixture: ComponentFixture<ViewReductionstandardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewReductionstandardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewReductionstandardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
