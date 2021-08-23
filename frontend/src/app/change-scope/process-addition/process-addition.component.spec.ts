import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ProcessAdditionComponent } from './process-addition.component';

describe('ProcessAdditionComponent', () => {
  let component: ProcessAdditionComponent;
  let fixture: ComponentFixture<ProcessAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ProcessAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ProcessAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
