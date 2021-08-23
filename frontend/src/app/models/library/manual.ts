export interface Manual {
    id: number;
    title:string;
    version: string;
	date: string;
    description?:string;
    reviewer?:string;
    access?:Array<any>;
    status?: number; 
    status_label?: string; 
    documents?:any;
}