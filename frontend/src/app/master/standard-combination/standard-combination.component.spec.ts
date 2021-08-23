import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { StandardCombinationComponent } from './standard-combination.component';

describe('StandardCombinationComponent', () => {
  let component: StandardCombinationComponent;
  let fixture: ComponentFixture<StandardCombinationComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ StandardCombinationComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(StandardCombinationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
