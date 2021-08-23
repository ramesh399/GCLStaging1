export interface Legislation {
    id: number;
    title: string;
	description: string;
    franchise_id:number;
    relevant_to_id:number;
    update_method_id:number;
    status?: number; 
}