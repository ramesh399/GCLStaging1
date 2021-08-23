export interface CustomerClientlogoChecklist {
    id: number;
    name: string;
    code: string;
    file_upload_required: number;
    interpretation?:string;
    status?: number;
    standard?:any;
    standard_label?:any;
    file_upload_required_label?:any;

    
}