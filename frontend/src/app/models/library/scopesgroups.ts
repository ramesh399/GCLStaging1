export interface ScopesGroups {
    id: number;
    standard_id: number;
    business_group_id:number;
    business_group_code_id:number;
    standard_id_label: string;
    business_group_id_label:string;
    business_group_code_id_label:string;
    scope:number;
    risk:string;
    description:string;
    accrediation:string;
    process:string;
    controls:string;
    status?: number; 
    status_label?: string; 
}