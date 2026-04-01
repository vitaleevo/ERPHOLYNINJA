export interface Patient {
  id: number;
  clinic_id: number;
  name: string;
  email: string | null;
  phone: string;
  nif: string | null;
  bi_number: string | null;
  birth_date: string | null;
  gender: 'male' | 'female' | 'other' | null;
  address: string | null;
  allergies: string | null;
  medical_history: string | null;
  emergency_contact: string | null;
  emergency_phone: string | null;
  insurance_id: number | null;
  insurance_number: string | null;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

export interface Appointment {
  id: number;
  clinic_id: number;
  patient_id: number;
  doctor_id: number;
  specialty_id: number | null;
  scheduled_at: string;
  duration_minutes: number;
  status: 'scheduled' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled' | 'no_show';
  notes: string | null;
  room: string | null;
  patient?: Patient;
  doctor?: User;
  specialty?: Specialty;
  created_at: string;
  updated_at: string;
}

export interface User {
  id: number;
  clinic_id: number;
  name: string;
  email: string;
  role: 'admin' | 'doctor' | 'receptionist' | 'pharmacist' | 'accountant';
  specialty_id: number | null;
  phone: string | null;
  avatar: string | null;
  is_active: boolean;
  specialty?: Specialty;
}

export interface Specialty {
  id: number;
  clinic_id: number;
  name: string;
  description: string | null;
  is_active: boolean;
}

export interface Consultation {
  id: number;
  clinic_id: number;
  appointment_id: number | null;
  patient_id: number;
  doctor_id: number;
  started_at: string;
  ended_at: string | null;
  chief_complaint: string | null;
  symptoms: string | null;
  diagnosis: string | null;
  observations: string | null;
  status: 'in_progress' | 'completed';
  patient?: Patient;
  doctor?: User;
}

export interface PrescriptionItem {
  id: number;
  prescription_id: number;
  medication: string;
  dosage: string;
  frequency: string;
  duration_days: number | null;
  instructions: string | null;
}

export interface Prescription {
  id: number;
  clinic_id: number;
  consultation_id: number | null;
  patient_id: number;
  doctor_id: number;
  notes: string | null;
  is_digital_signature: boolean;
  patient?: Patient;
  doctor?: User;
  items: PrescriptionItem[];
  created_at: string;
}

export interface Insurance {
  id: number;
  clinic_id: number;
  name: string;
  code: string | null;
  description: string | null;
  is_active: boolean;
}

export interface ApiResponse<T> {
  data: T;
  message?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}
