export interface ApplicationChecklist {
    id: number;
    name: string;
	code: string;
    guidance?:string;
    answer:Array<any>;
    status?: number;
    answerList:Array<any>;
    answer_label?:string;  
    file_upload_required?: number;
    file_upload_required_label?: any;
}