import { useState, useEffect, useCallback } from 'react';
import { getStore } from '../api/storefront';

export function useStore(slug) {
  const [store, setStore] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchStore = useCallback(async () => {
    if (!slug) return;
    setLoading(true);
    setError(null);
    try {
      const data = await getStore(slug);
      setStore(data.store || data);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [slug]);

  useEffect(() => { fetchStore(); }, [fetchStore]);

  return { store, loading, error, refetch: fetchStore };
}
