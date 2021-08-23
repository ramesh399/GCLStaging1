export interface Checklist {
    id: number;
    name: string;
	code: string;
    guidance: string;
	category?: number;
    status?: number;
}