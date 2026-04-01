'use client';

import React from 'react';
import { 
  ChevronRight, 
  Share2, 
  MoreHorizontal, 
  Info, 
  Calendar, 
  Users, 
  GraduationCap, 
  FileText,
  Copy,
  Plus,
  ArrowRight,
  UserPlus,
  CheckSquare,
  Settings
} from 'lucide-react';
import { cn } from '../../lib/utils';

const stats = [
  { label: 'Avg. Time', value: '3.25h' },
  { label: 'Avg. Score', value: '4.4' },
  { label: 'Participance', value: '132' },
];

const team = [
  { name: 'Sam Wilson', role: 'Team Lead', tag: 'Mentor', avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Sam' },
  { name: 'Emily Carter', role: 'UX Analyst', tag: 'Teacher', avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Emily' },
  { name: 'Jake Thompson', role: 'Senior UX Researcher', tag: 'Teacher', avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Jake' },
  { name: 'Monica Cooper', role: 'Product Manager', tag: 'Admin', avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Monica' },
];

const participants = [
  { name: 'Oliver Cranston', role: 'Middle UX Designer', progress: 12, avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Oliver' },
  { name: 'Sara Green', role: 'UX Researcher', progress: 76, avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Sara' },
  { name: 'Sam Wilson', role: 'Middle UX Designer', progress: 32, avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Sam2' },
];

export default function DashboardPage() {
  return (
    <div className="flex flex-col gap-8 w-full max-w-[1400px] mx-auto animate-fade-in">
      
      {/* Top Controls & Breadcrumbs */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2 text-xs font-medium text-slate-400">
          <span>Education</span>
          <ChevronRight className="w-4 h-4" />
          <span>Courses</span>
          <ChevronRight className="w-4 h-4" />
          <span className="text-slate-900 font-semibold uppercase tracking-wider">UX Evaluation: Enhancing User Experience</span>
        </div>
        <div className="flex items-center gap-3">
          <button className="flex items-center gap-2 px-4 py-2 border border-slate-200 rounded-xl bg-white text-sm font-semibold hover:bg-slate-50 transition-colors shadow-sm">
            <Share2 className="w-4 h-4" />
            Share
          </button>
          <button className="p-2 border border-slate-200 rounded-xl bg-white text-slate-400 hover:text-slate-900 transition-colors shadow-sm">
            <MoreHorizontal className="w-4 h-4" />
          </button>
        </div>
      </div>

      <div className="grid grid-cols-12 gap-8 items-start">
        {/* Left Column (8 cols) */}
        <div className="col-span-8 flex flex-col gap-6">
          
          {/* Main Card Header Gradient */}
          <div className="relative overflow-hidden bg-gradient-to-br from-[#1d4ed8] via-[#06b6d4] to-[#0ea5e9] rounded-[2rem] p-8 text-white shadow-2xl shadow-blue-500/10">
            <div className="relative z-10">
              <h2 className="text-2xl font-bold mb-4">UX Evaluation: Enhancing User Experience</h2>
              <div className="flex flex-wrap gap-2 mb-6">
                {['Usability Design', 'Lectures', 'Quizes', 'Certification'].map(item => (
                  <span key={item} className="px-3 py-1 bg-white/20 backdrop-blur rounded-full text-[10px] font-medium border border-white/10 uppercase tracking-tighter">
                    {item}
                  </span>
                ))}
              </div>
              <div className="flex items-center gap-6 text-sm text-white/90">
                <div className="flex items-center gap-2">
                   <div className="w-2 h-2 bg-blue-300 rounded-full" />
                   <span>English</span>
                </div>
                <div className="flex items-center gap-2">
                   <div className="w-2 h-2 bg-green-300 rounded-full" />
                   <span>4.8</span>
                </div>
              </div>
            </div>
            
            {/* Action Pills Right side Header */}
            <div className="absolute right-8 top-1/2 -translate-y-1/2 hidden md:flex flex-col gap-3">
              {['User Flow Clarity', 'Usability Metrics', 'Testing & Feedback', 'Actionable Improvements'].map(item => (
                <div key={item} className="px-4 py-2 bg-white/15 backdrop-blur-md rounded-2xl border border-white/10 text-xs text-white/90 font-medium whitespace-nowrap hover:bg-white/20 transition-colors w-48 text-center cursor-pointer">
                  {item}
                </div>
              ))}
            </div>
          </div>

          {/* Description Section */}
          <div className="py-4">
            <h3 className="text-lg font-bold text-slate-800 mb-3">Description</h3>
            <p className="text-sm text-slate-500 leading-relaxed max-w-2xl">
              Explore practical approaches to evaluating user experience across digital products. This module covers essential UX assessment techniques, usability heuristics, user testing methods, and analytics interpretation. You'll learn how to identify friction points, measure usability quality, and transform findings into actionable improvements. <span className="text-blue-600 font-semibold cursor-pointer">Read more</span>
            </p>
          </div>

          {/* Tabs Navigation */}
          <div className="flex gap-8 border-b border-slate-100 mb-4 pb-0.5">
            {['Details', 'Content', 'Report issue', 'Discussion'].map((tab, i) => (
              <button 
                key={tab} 
                className={cn(
                  "pb-3 text-sm font-medium transition-all relative border-b-2",
                  i === 0 ? "border-blue-600 text-slate-900" : "border-transparent text-slate-400 hover:text-slate-600"
                )}
              >
                {tab}
              </button>
            ))}
          </div>

          {/* Details Content Card Overlay style from image */}
          <div className="bg-white rounded-[2rem] border border-slate-100 p-8 shadow-premium relative">
             <div className="flex justify-between items-start mb-10">
                <h4 className="text-lg font-bold text-slate-800">Details</h4>
             </div>
             
             <div className="grid grid-cols-2 gap-y-6 gap-x-12 mb-10">
                <div className="flex items-center gap-3 text-sm text-slate-500">
                  <FileText className="w-4 h-4 text-slate-400" />
                  <span>3 sections & 5 chapters</span>
                </div>
                <div className="flex items-center gap-3 text-sm text-slate-500">
                  <GraduationCap className="w-4 h-4 text-slate-400" />
                  <span>Includes certificate of completion</span>
                </div>
                <div className="flex items-center gap-3 text-sm text-slate-500">
                  <Calendar className="w-4 h-4 text-slate-400" />
                  <span>2 weeks estimation</span>
                </div>
                <div className="flex items-center gap-3 text-sm text-slate-500">
                  <FileText className="w-4 h-4 text-slate-400" />
                  <span>Video + pdf files</span>
                </div>
                <div className="flex items-center gap-3 text-sm text-slate-500">
                  <Calendar className="w-4 h-4 text-slate-400" />
                  <span>No due date</span>
                </div>
                <div className="flex items-center gap-3 text-sm text-slate-500">
                  <CheckSquare className="w-4 h-4 text-slate-400" />
                  <span>Home work tasks</span>
                </div>
             </div>

             <div className="pt-8 border-t border-slate-50">
               <div className="flex items-center justify-between mb-6">
                 <h4 className="text-sm font-bold text-slate-900 flex items-center gap-2">
                   Team members
                   <button className="p-1 bg-blue-100 text-blue-600 rounded-full hover:bg-blue-200 transition-colors">
                     <Plus className="w-3 h-3" />
                   </button>
                 </h4>
               </div>
               <div className="grid grid-cols-2 gap-6">
                  {team.map((member) => (
                    <div key={member.name} className="flex items-center gap-3 group cursor-pointer">
                      <div className="w-10 h-10 rounded-full bg-slate-100 p-0.5 border-2 border-transparent group-hover:border-blue-400 transition-all">
                         <img src={member.avatar} alt={member.name} className="w-full h-full rounded-full" />
                      </div>
                      <div>
                        <p className="text-sm font-bold text-slate-800 flex items-center gap-2">
                          {member.name}
                          <span className="text-[9px] px-2 py-0.5 bg-green-50 text-green-600 rounded-full font-medium uppercase tracking-tighter border border-green-100">
                            {member.tag}
                          </span>
                        </p>
                        <p className="text-[10px] text-slate-400 font-medium">{member.role}</p>
                      </div>
                    </div>
                  ))}
               </div>
             </div>
          </div>
        </div>

        {/* Right Column (4 cols) */}
        <div className="col-span-4 flex flex-col gap-8">
          
          {/* Statistics Chart Overview */}
          <div className="bg-white rounded-[2rem] border border-slate-100 p-8 shadow-premium h-fit">
            <div className="flex items-center justify-between mb-8">
              <h4 className="text-base font-bold text-slate-900 flex items-center gap-2 uppercase tracking-tighter">
                Statistics Overview
                <Info className="w-3 h-3 text-slate-300" />
              </h4>
              <button className="flex items-center gap-2 text-xs font-medium text-slate-400">
                2025
                <ChevronRight className="w-3 h-3 rotate-90" />
              </button>
            </div>

            {/* Custom Bar Chart Visual */}
            <div className="h-48 flex items-end justify-between gap-2 mb-8 px-2 relative">
               {/* Grid lines mockup */}
               <div className="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-[0.03]">
                  {[1,2,3,4].map(line => <div key={line} className="w-full h-px bg-slate-900" />)}
               </div>
               
               {/* Tooltip mockup from image */}
               <div className="absolute top-0 right-24 bg-[#1e293b] text-white p-3 rounded-xl text-[10px] font-medium z-10 flex flex-col gap-1.5 shadow-xl animate-fade-in border border-white/5 whitespace-nowrap">
                  <p className="flex justify-between gap-6 opacity-60">Participance <span className="text-white opacity-100">19</span></p>
                  <p className="flex justify-between gap-6 opacity-60">Avg. Time <span className="text-white opacity-100">3.25h</span></p>
                  <p className="flex justify-between gap-6 opacity-60">Avg. Score <span className="text-white opacity-100">4.8</span></p>
               </div>

               {[
                 { h: 'h-4', active: false },
                 { h: 'h-8', active: false },
                 { h: 'h-12', active: false },
                 { h: 'h-16', active: false },
                 { h: 'h-24', active: false },
                 { h: 'h-10', active: false },
                 { h: 'h-32', active: true },
                 { h: 'h-20', active: false },
                 { h: 'h-16', active: false },
                 { h: 'h-12', active: false },
                 { h: 'h-8', active: false },
                 { h: 'h-4', active: false },
               ].map((bar, i) => (
                 <div 
                   key={i} 
                   className={cn(
                     "w-full rounded-full transition-all duration-300 transform origin-bottom hover:scale-y-110 cursor-pointer",
                     bar.h,
                     bar.active ? "bg-green-400 shadow-lg shadow-green-400/20" : i % 2 === 0 ? "bg-green-100/50" : "bg-green-100"
                   )}
                 />
               ))}
               
               {/* Tooltip selection marker */}
               <div className="absolute bottom-[32px] right-24 w-4 h-4 border-2 border-slate-900 rounded-full flex items-center justify-center p-0.5 bg-white shadow-lg translate-x-3 translate-y-2">
                 <div className="w-1.5 h-1.5 bg-slate-900 rounded-full" />
               </div>
            </div>

            <div className="grid grid-cols-3 gap-4 pt-4 border-t border-slate-50">
               {stats.map(stat => (
                 <div key={stat.label}>
                    <p className="text-xs font-bold text-slate-900 mb-0.5 truncate tracking-tighter">{stat.value}</p>
                    <p className="text-[10px] font-medium text-slate-400 uppercase tracking-tighter">{stat.label}</p>
                 </div>
               ))}
            </div>
          </div>

          {/* Assignment Modal Card Style (Glass) */}
          <div className="relative pt-12 pb-8">
            {/* The white card base */}
            <div className="bg-white rounded-[2rem] border border-slate-100 p-8 shadow-premium flex flex-col gap-8">
               <div className="flex items-center justify-between">
                  <h4 className="text-sm font-bold text-slate-800 flex items-center gap-2">
                    People on the course
                  </h4>
               </div>
               <div className="flex flex-col gap-6">
                {participants.map((p) => (
                  <div key={p.name} className="flex items-center gap-3">
                    <img src={p.avatar} alt={p.name} className="w-10 h-10 rounded-full p-0.5 border border-slate-100" />
                    <div className="flex-1 min-w-0">
                      <p className="text-xs font-bold text-slate-800 truncate">{p.name}</p>
                      <p className="text-[10px] text-slate-400 font-medium truncate uppercase tracking-tighter">{p.role}</p>
                    </div>
                    {/* Multi-dot progress mock */}
                    <div className="flex gap-[2px]">
                       {[1,2,3,4,5,6,7,8,9,10].map(dot => (
                         <div key={dot} className={cn("w-1.5 h-1 rounded-full", dot <= p.progress / 10 ? "bg-amber-400" : "bg-amber-100")} />
                       ))}
                       <span className="text-[10px] font-bold text-slate-800 ml-2">{p.progress}%</span>
                    </div>
                  </div>
                ))}
               </div>
               
               <div className="flex items-center justify-between pt-4 border-t border-slate-50 text-[11px] font-medium text-slate-400 uppercase tracking-wider">
                  <button className="flex items-center gap-2 hover:text-slate-900 transition-colors uppercase tracking-widest"><Copy className="w-3.5 h-3.5" /> Copy link</button>
                  <button className="flex items-center gap-2 hover:text-slate-900 transition-colors uppercase tracking-widest"><Users className="w-3.5 h-3.5" /> All participants</button>
                  <button className="flex items-center gap-1.5 hover:text-slate-900 transition-colors uppercase tracking-widest"><Settings className="w-3.5 h-3.5" /> Settings</button>
               </div>
            </div>

            {/* The Floating 'Assign new participant' Glass element */}
            <div className="absolute -top-4 -right-12 left-4 glass shadow-2xl rounded-[2rem] p-8 animate-fade-in border border-white/50">
               <div className="flex items-center justify-between mb-4">
                  <h4 className="text-xs font-bold text-slate-900 flex items-center gap-2 uppercase tracking-tight">
                    Assign new participant
                    <Info className="w-3 h-3 text-slate-400" />
                  </h4>
               </div>
               <div className="flex gap-2">
                 <div className="flex-1 h-10 border border-slate-200 rounded-2xl bg-white/50 flex items-center px-3 gap-2">
                    <div className="flex items-center gap-1.5 px-2 py-0.5 bg-slate-100 rounded-full text-xs font-medium text-slate-700">
                       <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Adam" className="w-4 h-4 rounded-full" />
                       Adam Brown
                       <button className="hover:text-slate-900">×</button>
                    </div>
                 </div>
                 <button className="h-10 px-5 bg-blue-600 text-white rounded-2xl text-xs font-bold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/20 uppercase tracking-widest">
                   Invite
                 </button>
               </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  );
}
