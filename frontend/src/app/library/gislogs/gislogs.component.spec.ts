import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { GislogsComponent } from './gislogs.component';

describe('GislogsComponent', () => {
  let component: GislogsComponent;
  let fixture: ComponentFixture<GislogsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ GislogsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(GislogsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
