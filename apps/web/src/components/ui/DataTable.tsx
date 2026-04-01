import React, { useMemo, useState } from 'react';

type Column<T> = {
  key: keyof T | string;
  header: string;
  sortable?: boolean;
  render?: (value: any, item: T) => React.ReactNode;
};

interface DataTableProps<T> {
  data: T[];
  columns: Column<T>[];
  loading?: boolean;
  search?: boolean;
  searchPlaceholder?: string;
  emptyMessage?: string;
  pagination?: boolean;
  pageSize?: number;
}

export function DataTable<T>({
  data,
  columns,
  loading = false,
  search = false,
  searchPlaceholder = 'Buscar...',
  emptyMessage = 'Nenhum registro encontrado',
  pagination = false,
  pageSize = 10,
}: DataTableProps<T>) {
  const [query, setQuery] = useState('');
  const [sortKey, setSortKey] = useState<string | null>(null);
  const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');
  const [page, setPage] = useState(1);

  const filtered = useMemo(() => {
    if (!search || !query.trim()) return data;
    const q = query.toLowerCase();
    return data.filter((row) =>
      columns.some((col) => {
        const value = (row as any)[col.key as any];
        if (value === null || value === undefined) return false;
        return String(value).toLowerCase().includes(q);
      })
    );
  }, [data, columns, query, search]);

  const sorted = useMemo(() => {
    if (!sortKey) return filtered;
    return [...filtered].sort((a, b) => {
      const av = (a as any)[sortKey as any];
      const bv = (b as any)[sortKey as any];
      if (av === bv) return 0;
      if (av === undefined || av === null) return 1;
      if (bv === undefined || bv === null) return -1;
      if (av > bv) return sortDirection === 'asc' ? 1 : -1;
      return sortDirection === 'asc' ? -1 : 1;
    });
  }, [filtered, sortKey, sortDirection]);

  const totalPages = pagination ? Math.max(1, Math.ceil(sorted.length / pageSize)) : 1;
  const paged = pagination
    ? sorted.slice((page - 1) * pageSize, page * pageSize)
    : sorted;

  const handleSort = (key: string, sortable?: boolean) => {
    if (!sortable) return;
    if (sortKey === key) {
      setSortDirection((d) => (d === 'asc' ? 'desc' : 'asc'));
    } else {
      setSortKey(key);
      setSortDirection('asc');
    }
  };

  return (
    <div className="w-full">
      {search && (
        <div className="mb-4">
          <input
            value={query}
            onChange={(e) => {
              setQuery(e.target.value);
              setPage(1);
            }}
            placeholder={searchPlaceholder}
            className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
      )}

      <div className="overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table className="min-w-full divide-y divide-gray-200 text-sm">
          <thead className="bg-gray-50">
            <tr>
              {columns.map((col) => (
                <th
                  key={String(col.key)}
                  onClick={() => handleSort(String(col.key), col.sortable)}
                  className={`px-4 py-3 text-left font-semibold text-gray-700 ${
                    col.sortable ? 'cursor-pointer select-none' : ''
                  }`}
                >
                  <div className="flex items-center gap-2">
                    {col.header}
                    {col.sortable && sortKey === col.key && (
                      <span className="text-xs text-gray-400">
                        {sortDirection === 'asc' ? '▲' : '▼'}
                      </span>
                    )}
                  </div>
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {loading ? (
              <tr>
                <td colSpan={columns.length} className="px-4 py-10 text-center text-gray-500">
                  Carregando...
                </td>
              </tr>
            ) : paged.length === 0 ? (
              <tr>
                <td colSpan={columns.length} className="px-4 py-10 text-center text-gray-500">
                  {emptyMessage}
                </td>
              </tr>
            ) : (
              paged.map((row, rowIndex) => (
                <tr key={rowIndex} className="hover:bg-gray-50">
                  {columns.map((col) => {
                    const value = (row as any)[col.key as any];
                    return (
                      <td key={String(col.key)} className="px-4 py-3 text-gray-700">
                        {col.render ? col.render(value, row) : value ?? '-'}
                      </td>
                    );
                  })}
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {pagination && totalPages > 1 && (
        <div className="mt-4 flex items-center justify-between text-sm text-gray-600">
          <span>
            Pagina {page} de {totalPages}
          </span>
          <div className="flex gap-2">
            <button
              onClick={() => setPage((p) => Math.max(1, p - 1))}
              disabled={page === 1}
              className="rounded-md border border-gray-200 px-3 py-1 disabled:opacity-50"
            >
              Anterior
            </button>
            <button
              onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
              disabled={page === totalPages}
              className="rounded-md border border-gray-200 px-3 py-1 disabled:opacity-50"
            >
              Proxima
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
