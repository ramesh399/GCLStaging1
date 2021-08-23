export interface Relation {
    id: number;
    name: string;
    type_name:string;
    type_name_id:number;
    wastage:string;
	code: string;
    description: string;
    status?: number;
    rel_declaration_company?:string;
    rel_declaration_contract?:any;
    rel_declaration_contract_name?:any;
    rel_declaration_interest?:string;
    rel_declaration_start_year?:any;
    rel_declaration_end_year?:any;

}