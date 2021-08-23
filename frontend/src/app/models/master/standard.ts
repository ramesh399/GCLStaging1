export interface Standard {
    id: number;
    name: string;
	code: string;
    type: number;
	description: string;
    status?: number;
    required_fields?:any;
}