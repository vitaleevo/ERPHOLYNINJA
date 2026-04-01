import { useState, useCallback } from 'react';
import api from './api';

interface UseApiState<T> {
  data: T | null;
  loading: boolean;
  error: string | null;
}

export function useApi<T = any>() {
  const [state, setState] = useState<UseApiState<T>>({
    data: null,
    loading: false,
    error: null,
  });

  const execute = useCallback(async (
    method: 'get' | 'post' | 'put' | 'delete',
    endpoint: string,
    data?: any
  ) => {
    setState(prev => ({ ...prev, loading: true, error: null }));

    try {
      let response;
      
      switch (method) {
        case 'get':
          response = await api.get(endpoint);
          break;
        case 'post':
          response = await api.post(endpoint, data);
          break;
        case 'put':
          response = await api.put(endpoint, data);
          break;
        case 'delete':
          response = await api.delete(endpoint);
          break;
      }

      setState({
        data: response.data,
        loading: false,
        error: null,
      });

      return response.data;
    } catch (error: any) {
      const errorMessage = error.response?.data?.message || 'Erro ao processar requisição';
      
      setState(prev => ({
        ...prev,
        loading: false,
        error: errorMessage,
      }));

      throw new Error(errorMessage);
    }
  }, []);

  const get = useCallback((endpoint: string) => 
    execute('get', endpoint), [execute]);

  const post = useCallback((endpoint: string, data?: any) => 
    execute('post', endpoint, data), [execute]);

  const put = useCallback((endpoint: string, data?: any) => 
    execute('put', endpoint, data), [execute]);

  const deleteRequest = useCallback((endpoint: string) => 
    execute('delete', endpoint), [execute]);

  return {
    ...state,
    get,
    post,
    put,
    delete: deleteRequest,
    clearData: () => setState(prev => ({ ...prev, data: null })),
    clearError: () => setState(prev => ({ ...prev, error: null })),
  };
}
