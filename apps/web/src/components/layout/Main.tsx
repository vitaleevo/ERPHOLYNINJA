import React from 'react';

interface MainProps {
  children: React.ReactNode;
  className?: string;
}

export const Main: React.FC<MainProps> = ({ children, className = '' }) => {
  return (
    <main className={`pt-16 min-h-screen bg-[var(--background)] ${className}`}>
      <div className="p-6">
        {children}
      </div>
    </main>
  );
};
