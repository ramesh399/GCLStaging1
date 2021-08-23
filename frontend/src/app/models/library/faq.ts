export interface Faq {
    id: number;
    question: string;
	answer: string;
    franchise_id?:Array<any>;
    status?: number; 
}