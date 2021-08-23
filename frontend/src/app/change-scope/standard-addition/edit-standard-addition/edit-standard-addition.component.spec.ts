import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditStandardAdditionComponent } from './edit-standard-addition.component';

describe('EditStandardAdditionComponent', () => {
  let component: EditStandardAdditionComponent;
  let fixture: ComponentFixture<EditStandardAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditStandardAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditStandardAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
