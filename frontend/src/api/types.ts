// Shared TypeScript types for API entities and lookups.

export interface LookupItem {
  value: number;
  name: string;
}
export interface GstSlab {
  id: number;
  name: string;
  percentage: number;
}
export interface TermItem {
  id: number;
  name: string;
  months: number;
}

export interface Lookups {
  months: LookupItem[];
  years: LookupItem[];
  states: LookupItem[];
  gstSlabs: GstSlab[];
  subscriptionTerms: TermItem[];
  billingTerms: TermItem[];
  cloudCategories: LookupItem[];
  adjustmentTypes: LookupItem[];
  creditNoteTypes: LookupItem[];
  debitNoteTypes: LookupItem[];
  unitMeasures: LookupItem[];
  discoveryStatuses: LookupItem[];
  contractYears: LookupItem[];
  contractMonths: LookupItem[];
  billingProgress: LookupItem[];
}

export interface Product {
  id: number;
  name: string;
  oem: number;
  category: number;
  sub_category: number;
  oem_name?: string;
  category_name?: string;
  sub_category_name?: string;
  created_on: string;
}
export interface Named {
  id: number;
  name: string;
  created_on?: string;
}
export interface User {
  id: number;
  full_name: string;
  email: string;
  username: string;
  user_type: number;
  created_on: string;
}
export interface PurchaseHeader {
  id: number;
  name: string;
  project_id: number;
  project_name?: string;
  created_on: string;
}
export interface Project {
  id: number;
  hash: string;
  name: string;
  city: string;
  state: number;
  state_name?: string;
  distributor?: number;
  distributor_name?: string;
  tender_ref_no: string;
  start_date: string;
  product_category: number;
  description: string;
  created_by: number;
  created_by_name?: string;
  created_on: string;
}
export interface ProjectHeader {
  id: number;
  project_id: number;
  name: string;
  quantity: number;
  amount: number;
  description: string;
}
export interface ProjectItem {
  id: number;
  project_id: number;
  header_id: number;
  header_name?: string;
  product: number;
  product_name?: string;
  product_type: number;
  distributor: number;
  distributor_name?: string;
  deployment_start: string;
  deployment_end: string | null;
  unit_measure: number;
  unit_measure_name?: string;
  unit_price: number;
  quantity: number;
  deployed_product: number;
  deployed_product_name?: string;
  discovery_status: number;
  discovery_name?: string;
  purchase_header_id: number;
  purchase_header_name?: string;
  description: string;
  resource_id: string;
  model: string;
  status: number;
}
export interface Invoice {
  id: number;
  project_id: number;
  project_name?: string;
  number: string;
  amount: number;
  gst_slab: number;
  gst_name?: string;
  invoice_date: string;
  description: string;
  type: number;
  bill_id?: number | null;
  created_on: string;
}
export interface Bill {
  id: number;
  hash: string;
  project_id: number;
  month: number;
  year: number;
  ri_discount: number;
  payg_discount: number;
  status: number;
  purchase_header_status: number;
  progress: number;
  month_name?: string;
}
export interface MonthOption {
  month: number;
  year: number;
  name: string;
  label: string;
}
