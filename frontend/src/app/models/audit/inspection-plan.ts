export interface InspectionPlan {
    id: number;
    audit_plan_unit_inspection_id: number;
    activity: string;
	inspection: string;
    date: string; 
    start_time: string; 
    end_time: string;
    person_need_to_be_present: string;   
}