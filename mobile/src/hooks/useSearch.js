import { useState, useCallback, useRef } from 'react';
import { searchPosts } from '../services/api';

/**
 * Custom hook for semantic search with debouncing.
 */
export const useSearch = () => {
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [query, setQuery] = useState('');
  const [hasMore, setHasMore] = useState(false);

  const pageRef = useRef(1);
  const timerRef = useRef(null);

  const search = useCallback(async (searchQuery, page = 1) => {
    if (!searchQuery || searchQuery.length < 2) {
      setResults([]);
      setHasMore(false);
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const response = await searchPosts(searchQuery, page);

      if (page === 1) {
        setResults(response.data);
      } else {
        setResults((prev) => [...prev, ...response.data]);
      }

      setHasMore(response.meta.current_page < response.meta.last_page);
      pageRef.current = response.meta.current_page;
    } catch (err) {
      setError(err.message || 'Search failed');
    } finally {
      setLoading(false);
    }
  }, []);

  const debouncedSearch = useCallback(
    (text) => {
      setQuery(text);
      if (timerRef.current) clearTimeout(timerRef.current);

      timerRef.current = setTimeout(() => {
        pageRef.current = 1;
        search(text, 1);
      }, 400);
    },
    [search],
  );

  const loadMore = useCallback(() => {
    if (!hasMore || loading) return;
    search(query, pageRef.current + 1);
  }, [hasMore, loading, query, search]);

  const clearSearch = useCallback(() => {
    setQuery('');
    setResults([]);
    setError(null);
    setHasMore(false);
    if (timerRef.current) clearTimeout(timerRef.current);
  }, []);

  return {
    results,
    loading,
    error,
    query,
    hasMore,
    search: debouncedSearch,
    loadMore,
    clearSearch,
  };
};
