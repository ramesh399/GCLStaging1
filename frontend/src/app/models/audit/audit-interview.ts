export interface AuditReportInterview {
    id: number;
    audit_id: number;
    process_id: number;
    process_name: string;
    number_of_male: number;
	number_of_female: number;
    number_of_transgender:number;
    total_employees:number; 
    summary:any;
}