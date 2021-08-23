export interface AuditReport {
    id: number;
    note: string;
	document: string;
    franchise_id:number;
    franchise_id_label: string;
    reviewer:number;
    access_id:number;
    status?: number; 
}