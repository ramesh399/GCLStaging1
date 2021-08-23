export interface Generatelist {
    id: number;
    company_name: string;
	code: string;
    zipcode: string;
    city: string;
    app_status?:number;
    status?: number;

    created_at?: string;
    preferred_partner_id_name?: string;
    address?: string;
    country_id_name?: string;
    state_id_name?: string;
    salutation_name?: string;
    title?: string;
    first_name?: string;
    last_name?: string;
    job_title?: string;

    telephone?: string;
    email_address?: string;
    standards?: string;
    products?: string;
    units?: string;
    applicationreviews?: Array<any>;
    


}