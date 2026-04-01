import React from 'react';
import { clsx } from 'clsx';

interface HeaderProps {
  children?: React.ReactNode;
  className?: string;
  fixed?: boolean;
}

export const Header: React.FC<HeaderProps> = ({ children, className, fixed }) => {
  const baseStyles = 'bg-white border-b border-gray-200 shadow-sm';
  const fixedStyles = fixed ? 'fixed top-0 left-0 right-0 z-50' : '';
  
  const classes = clsx(
    baseStyles,
    fixedStyles,
    'px-6 py-4',
    className
  );

  return (
    <header className={classes}>
      {children}
    </header>
  );
};

interface FooterProps {
  children?: React.ReactNode;
  className?: string;
}

export const Footer: React.FC<FooterProps> = ({ children, className }) => {
  const classes = clsx(
    'bg-gray-50 border-t border-gray-200 px-6 py-4 mt-auto',
    className
  );

  return (
    <footer className={classes}>
      {children}
    </footer>
  );
};

interface SidebarProps {
  children?: React.ReactNode;
  className?: string;
  collapsed?: boolean;
}

export const Sidebar: React.FC<SidebarProps> = ({ children, className, collapsed }) => {
  const baseStyles = 'bg-white border-r border-gray-200 h-full transition-all duration-300';
  const collapsedStyles = collapsed ? 'w-20' : 'w-64';
  
  const classes = clsx(
    baseStyles,
    collapsedStyles,
    'flex flex-col',
    className
  );

  return (
    <aside className={classes}>
      {children}
    </aside>
  );
};

interface MainLayoutProps {
  children?: React.ReactNode;
  className?: string;
}

export const Main: React.FC<MainLayoutProps> = ({ children, className }) => {
  const classes = clsx(
    'flex-1 overflow-auto bg-gray-50 p-6',
    className
  );

  return (
    <main className={classes}>
      {children}
    </main>
  );
};
