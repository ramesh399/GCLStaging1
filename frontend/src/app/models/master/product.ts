export interface Product {
    id: number;
    name: string;
	code: string;
    description: string;
    product_type_id:number;
    material_composition:string;
    productStandardList:Array<any>;
    status?: number;
    wastage?: string;
    productMaterialList:Array<any>;
}