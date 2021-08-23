export interface UnitFindings {
    id: number;
    audit_plan_unit_execution_id: number;
    user_id: number;
	answer: string;
    finding: string; 
    severity: number; 
    finding_type: number;
    question: string;   
    question_id: number;
    file: string; 
    duedate?:any;
    status?:any;
    auditorStatus?:any;
    auditorComment?:any;
    auditorRevieweddate?:any;
}