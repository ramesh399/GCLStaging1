export interface AuditExecutionChecklist {
    id: number;
    name: string;
	interpretation?: string;
    expected_evidence?:string;
    file_upload_required:number;
    positive_finding_default_comment:string;
    negative_finding_default_comment:string;  
    standard?:any;
    bsector_label?:any;
    bsectorgroup_label?:any;
    process_label?:any;
    sub_topic?:any;
    severity_label?:any;
    postiveComment?:any;
    negativeComment?:any;
    file_upload_required_label?:any;
    findings_name?:any;
}