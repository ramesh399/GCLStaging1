export interface Gislog {
    id: number;
    title: string;
    description: string;
	type?: string;
	gis_file?: string;
	received_date?: string;
    status?: string;    
    created_by?:any;
    created_at?:any;
}