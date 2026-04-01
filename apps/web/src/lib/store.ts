import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import type { User, Patient, Appointment } from '@/types';

interface AppState {
  clinicId: string | null;
  user: User | null;
  patients: Patient[];
  appointments: Appointment[];
  setClinicId: (id: string) => void;
  setUser: (user: User | null) => void;
  setPatients: (patients: Patient[]) => void;
  setAppointments: (appointments: Appointment[]) => void;
  logout: () => void;
}

export const useStore = create<AppState>()(
  persist(
    (set) => ({
      clinicId: null,
      user: null,
      patients: [],
      appointments: [],
      setClinicId: (id) => set({ clinicId: id }),
      setUser: (user) => set({ user }),
      setPatients: (patients) => set({ patients }),
      setAppointments: (appointments) => set({ appointments }),
      logout: () => set({ clinicId: null, user: null, patients: [], appointments: [] }),
    }),
    {
      name: 'medangola-storage',
    }
  )
);
