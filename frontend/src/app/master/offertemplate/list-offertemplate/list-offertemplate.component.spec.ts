import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListOffertemplateComponent } from './list-offertemplate.component';

describe('ListOffertemplateComponent', () => {
  let component: ListOffertemplateComponent;
  let fixture: ComponentFixture<ListOffertemplateComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListOffertemplateComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListOffertemplateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
