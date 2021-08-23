import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewStandardAdditionComponent } from './view-standard-addition.component';

describe('ViewStandardAdditionComponent', () => {
  let component: ViewStandardAdditionComponent;
  let fixture: ComponentFixture<ViewStandardAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewStandardAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewStandardAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
