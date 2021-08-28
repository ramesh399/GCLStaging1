export interface User {
    id: number;
    first_name: string;
	last_name: string;
    email: string;
    telephone: string;
    country_id:number;
    state_id:number;
    date_of_birth:string;
    user_type:number;
    company_name:string;
    contact_name:string;
    company_telephone:string;
    company_email:string;
    company_website:string;
    company_address1:string;
    company_address2:string;
    company_city:string;
    company_zipcode:string;
    company_country_id:string;
    company_state_id:string;
    number_of_employees:string;
    number_of_sites:string;
    description:string;
    other_information:string;
    country_name:string;
    state_name:string;
    status?: number;
    company_country?: string;
    company_state?:string;
    userData:any;
    customerData:any;
    franchiseData:any;
    osp_number?:any;
    osp_details?:any;
    customer_number?:any;
    created_by?:any;
    created_at?:any;
    headquarters?:any;
    
    payment_details?:any;
    
}