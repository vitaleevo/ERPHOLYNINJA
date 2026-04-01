import React from 'react';

interface HeaderProps {
  children: React.ReactNode;
  className?: string;
  fixed?: boolean;
}

export const Header: React.FC<HeaderProps> = ({ children, className = '', fixed = false }) => {
  return (
    <header className={`bg-white border-b border-[var(--border)] ${fixed ? 'fixed top-0 left-0 right-0 z-50' : ''} ${className}`}>
      <div className="px-6 py-4">
        {children}
      </div>
    </header>
  );
};
